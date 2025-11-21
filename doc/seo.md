# SEO Features

Documentation for SEO-related features: breadcrumbs and sitemap generation.

---

## Breadcrumbs

Render breadcrumbs with Schema.org microdata for better search engine understanding.

### Helper Function

```php
render_breadcrumbs(array $breadcrumbs, array $options = []): HtmlString
```

**Parameters:**
- `$breadcrumbs` - Array of `['label' => '', 'url' => '']`
- `$options` - Rendering options

**Options:**
| Option | Default | Description |
|--------|---------|-------------|
| `separator` | `/` | Separator between items |
| `class` | `breadcrumbs` | CSS class for nav element |
| `item_class` | `breadcrumb-item` | CSS class for each item |
| `active_class` | `active` | CSS class for last item |
| `schema` | `true` | Include Schema.org microdata |

### Usage in Blade

```blade
{{-- Basic usage --}}
{!! render_breadcrumbs($breadcrumbs) !!}

{{-- With custom separator --}}
{!! render_breadcrumbs($breadcrumbs, ['separator' => '→']) !!}

{{-- With custom classes --}}
{!! render_breadcrumbs($breadcrumbs, [
    'class' => 'my-breadcrumbs',
    'item_class' => 'crumb',
    'active_class' => 'current',
]) !!}

{{-- Without Schema.org markup --}}
{!! render_breadcrumbs($breadcrumbs, ['schema' => false]) !!}

{{-- Using PageService breadcrumbs --}}
{!! render_breadcrumbs(AvePage::getBreadcrumbs()) !!}
```

### Usage in Liquid

```liquid
{# Basic usage #}
{{ breadcrumbs | breadcrumbs }}

{# With custom separator #}
{{ breadcrumbs | breadcrumbs: '→' }}

{# With arrow separator #}
{{ breadcrumbs | breadcrumbs: '>' }}
```

### Generated HTML

```html
<nav class="breadcrumbs" aria-label="Breadcrumb">
    <ol itemscope itemtype="https://schema.org/BreadcrumbList">
        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="/"><span itemprop="name">Home</span></a>
            <meta itemprop="position" content="1">
        </li>
        <li class="breadcrumb-separator" aria-hidden="true">/</li>
        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="/services"><span itemprop="name">Services</span></a>
            <meta itemprop="position" content="2">
        </li>
        <li class="breadcrumb-separator" aria-hidden="true">/</li>
        <li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <span itemprop="name">Web Development</span>
            <meta itemprop="position" content="3">
        </li>
    </ol>
</nav>
```

### Building Breadcrumbs

PageService automatically builds breadcrumbs from page hierarchy:

```php
// In controller
$page = AvePage::create($pageModel, $settings);
// Breadcrumbs are auto-built from parent pages

// In view
{!! render_breadcrumbs($breadcrumbs) !!}
```

Manually add breadcrumbs:

```php
AvePage::startBreadcrumbs(); // Clears and adds Home
AvePage::addBreadcrumbs('Products', '/products');
AvePage::addBreadcrumbs('Laptops', '/products/laptops');
AvePage::addBreadcrumbs('MacBook Pro'); // Last item, no URL needed
```

---

## Sitemap

Automatic sitemap.xml generation based on routes and models.

### Configuration

Add to `config/ave-site.php`:

```php
'sitemap' => [
    // Enable/disable sitemap route
    'enabled' => true,

    // Cache TTL in seconds (0 to disable)
    'cache_ttl' => 3600,

    // Include simple GET routes without parameters
    'include_simple_routes' => false,

    // Exclude URL patterns (supports wildcards *)
    'exclude' => [
        'admin/*',
        'api/*',
        'login',
        'register',
    ],

    // Exclude route names (supports wildcards *)
    'exclude_names' => [
        'debugbar.*',
        'ignition.*',
    ],

    // Routes with model bindings
    'routes' => [
        'page.show' => [
            'model' => \Monstrex\AveSite\Models\Page::class,
            'param' => 'slug',
            'field' => 'slug',
            'scope' => 'published',
            'lastmod' => 'updated_at',
            'changefreq' => 'weekly',
            'priority' => 0.8,
        ],
    ],

    // Static URLs
    'static' => [
        '/' => ['priority' => 1.0, 'changefreq' => 'daily'],
        '/contacts' => ['priority' => 0.8, 'changefreq' => 'monthly'],
    ],

    // Defaults
    'defaults' => [
        'changefreq' => 'monthly',
        'priority' => 0.5,
    ],
],
```

### Route Configuration

#### Single Parameter Routes

```php
// Route: /pages/{slug}
'page.show' => [
    'model' => \Monstrex\AveSite\Models\Page::class,
    'param' => 'slug',        // Route parameter name
    'field' => 'slug',        // Model field to use
    'scope' => 'published',   // Optional: model scope
    'with' => [],             // Optional: eager load
    'lastmod' => 'updated_at', // Field for lastmod
    'changefreq' => 'weekly',
    'priority' => 0.8,
],
```

#### Multiple Parameter Routes

```php
// Route: /catalog/{category}/{product}
'product.show' => [
    'model' => \App\Models\Product::class,
    'params' => [
        'category' => 'category.slug', // Dot notation for relations
        'product' => 'slug',
    ],
    'with' => ['category'],
    'scope' => 'active',
    'changefreq' => 'daily',
    'priority' => 0.7,
],
```

### Accessing Sitemap

The sitemap is automatically available at `/sitemap.xml`.

```
https://yoursite.com/sitemap.xml
```

### Generated Output

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://yoursite.com/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://yoursite.com/pages/about</loc>
        <lastmod>2025-11-20</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://yoursite.com/pages/services</loc>
        <lastmod>2025-11-15</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
</urlset>
```

### Clearing Cache

```php
use Monstrex\AveSite\Services\SitemapService;

app(SitemapService::class)->clearCache();
```

Or create an artisan command:

```php
// In your application
Artisan::command('sitemap:clear', function () {
    app(\Monstrex\AveSite\Services\SitemapService::class)->clearCache();
    $this->info('Sitemap cache cleared!');
});
```

### Adding to robots.txt

```
User-agent: *
Allow: /

Sitemap: https://yoursite.com/sitemap.xml
```

---

## Complete Example

### Controller

```php
class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->published()->firstOrFail();

        return AvePage::create($page, AveSite::getSettings())->view();
    }
}
```

### Layout Template

```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="keywords" content="{{ $seo['keywords'] }}">
</head>
<body>
    {{-- Breadcrumbs with Schema.org --}}
    {!! render_breadcrumbs($breadcrumbs, ['separator' => '›']) !!}

    <main>
        @yield('content')
    </main>
</body>
</html>
```

### Config

```php
// config/ave-site.php
'sitemap' => [
    'enabled' => true,
    'cache_ttl' => 3600,
    'routes' => [
        'page.show' => [
            'model' => \Monstrex\AveSite\Models\Page::class,
            'param' => 'slug',
            'field' => 'slug',
            'scope' => 'published',
            'changefreq' => 'weekly',
            'priority' => 0.8,
        ],
    ],
    'static' => [
        '/' => ['priority' => 1.0, 'changefreq' => 'daily'],
    ],
],
```

---

## See Also

- [Pages](pages.md) - Page management
- [Configuration](configuration.md) - Full config reference
- [Helpers](helpers.md) - Helper functions
