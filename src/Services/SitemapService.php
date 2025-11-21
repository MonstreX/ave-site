<?php

namespace Monstrex\AveSite\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class SitemapService
{
    /**
     * Generate sitemap XML
     */
    public function generate(): string
    {
        $cacheKey = 'ave-site-sitemap';
        $cacheTtl = config('ave-site.sitemap.cache_ttl', 3600);

        if ($cacheTtl > 0 && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $urls = $this->collectUrls();
        $xml = $this->buildXml($urls);

        if ($cacheTtl > 0) {
            Cache::put($cacheKey, $xml, $cacheTtl);
        }

        return $xml;
    }

    /**
     * Clear sitemap cache
     */
    public function clearCache(): void
    {
        Cache::forget('ave-site-sitemap');
    }

    /**
     * Collect all URLs for sitemap
     */
    protected function collectUrls(): array
    {
        $urls = [];

        // 1. Add static URLs from config
        $urls = array_merge($urls, $this->getStaticUrls());

        // 2. Add URLs from configured routes with models
        $urls = array_merge($urls, $this->getRouteModelUrls());

        // 3. Add simple GET routes (without parameters)
        $urls = array_merge($urls, $this->getSimpleRouteUrls());

        return $urls;
    }

    /**
     * Get static URLs from config
     */
    protected function getStaticUrls(): array
    {
        $urls = [];
        $static = config('ave-site.sitemap.static', []);
        $baseUrl = rtrim(config('app.url'), '/');

        foreach ($static as $path => $options) {
            $urls[] = [
                'loc' => $baseUrl . $path,
                'lastmod' => $options['lastmod'] ?? null,
                'changefreq' => $options['changefreq'] ?? config('ave-site.sitemap.defaults.changefreq', 'monthly'),
                'priority' => $options['priority'] ?? config('ave-site.sitemap.defaults.priority', 0.5),
            ];
        }

        return $urls;
    }

    /**
     * Get URLs from routes with model bindings
     */
    protected function getRouteModelUrls(): array
    {
        $urls = [];
        $routes = config('ave-site.sitemap.routes', []);
        $baseUrl = rtrim(config('app.url'), '/');

        foreach ($routes as $routeName => $config) {
            if (!Route::has($routeName)) {
                continue;
            }

            $modelClass = $config['model'] ?? null;
            if (!$modelClass || !class_exists($modelClass)) {
                continue;
            }

            $query = $modelClass::query();

            // Apply scope if specified
            if (!empty($config['scope'])) {
                $scope = $config['scope'];
                $query->$scope();
            }

            // Eager load relations
            if (!empty($config['with'])) {
                $query->with($config['with']);
            }

            $records = $query->get();
            $field = $config['field'] ?? 'slug';
            $lastmodField = $config['lastmod'] ?? 'updated_at';

            foreach ($records as $record) {
                // Build route parameters
                $params = [];

                if (!empty($config['params'])) {
                    // Multiple parameters (e.g., category/product)
                    foreach ($config['params'] as $param => $fieldPath) {
                        $params[$param] = data_get($record, $fieldPath);
                    }
                } else {
                    // Single parameter
                    $param = $config['param'] ?? 'slug';
                    $params[$param] = $record->{$field};
                }

                try {
                    $url = route($routeName, $params);
                } catch (\Exception $e) {
                    continue;
                }

                $lastmod = null;
                if ($lastmodField && isset($record->{$lastmodField})) {
                    $date = $record->{$lastmodField};
                    $lastmod = $date instanceof \DateTimeInterface
                        ? $date->format('Y-m-d')
                        : (is_string($date) ? substr($date, 0, 10) : null);
                }

                $urls[] = [
                    'loc' => $url,
                    'lastmod' => $lastmod,
                    'changefreq' => $config['changefreq'] ?? config('ave-site.sitemap.defaults.changefreq', 'monthly'),
                    'priority' => $config['priority'] ?? config('ave-site.sitemap.defaults.priority', 0.5),
                ];
            }
        }

        return $urls;
    }

    /**
     * Get URLs from simple routes (without parameters)
     */
    protected function getSimpleRouteUrls(): array
    {
        if (!config('ave-site.sitemap.include_simple_routes', false)) {
            return [];
        }

        $urls = [];
        $exclude = config('ave-site.sitemap.exclude', []);
        $excludeNames = config('ave-site.sitemap.exclude_names', []);
        $configuredRoutes = array_keys(config('ave-site.sitemap.routes', []));
        $baseUrl = rtrim(config('app.url'), '/');

        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            // Only GET routes
            if (!in_array('GET', $route->methods())) {
                continue;
            }

            $uri = $route->uri();
            $name = $route->getName();

            // Skip routes with parameters
            if (preg_match('/\{[^}]+\}/', $uri)) {
                continue;
            }

            // Skip if already configured in routes
            if ($name && in_array($name, $configuredRoutes)) {
                continue;
            }

            // Skip excluded patterns
            if ($this->isExcluded($uri, $exclude)) {
                continue;
            }

            // Skip excluded names
            if ($name && $this->isExcludedName($name, $excludeNames)) {
                continue;
            }

            $urls[] = [
                'loc' => $baseUrl . '/' . ltrim($uri, '/'),
                'lastmod' => null,
                'changefreq' => config('ave-site.sitemap.defaults.changefreq', 'monthly'),
                'priority' => config('ave-site.sitemap.defaults.priority', 0.5),
            ];
        }

        return $urls;
    }

    /**
     * Check if URI matches any exclude pattern
     */
    protected function isExcluded(string $uri, array $patterns): bool
    {
        $uri = '/' . ltrim($uri, '/');

        foreach ($patterns as $pattern) {
            $pattern = '/' . ltrim($pattern, '/');

            // Wildcard support
            if (str_contains($pattern, '*')) {
                $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';
                if (preg_match($regex, $uri)) {
                    return true;
                }
            } elseif ($uri === $pattern) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if route name matches any exclude pattern
     */
    protected function isExcludedName(string $name, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';
                if (preg_match($regex, $name)) {
                    return true;
                }
            } elseif ($name === $pattern) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build XML from URLs array
     */
    protected function buildXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";

            if (!empty($url['lastmod'])) {
                $xml .= "        <lastmod>{$url['lastmod']}</lastmod>\n";
            }

            if (!empty($url['changefreq'])) {
                $xml .= "        <changefreq>{$url['changefreq']}</changefreq>\n";
            }

            if (isset($url['priority'])) {
                $xml .= "        <priority>{$url['priority']}</priority>\n";
            }

            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
