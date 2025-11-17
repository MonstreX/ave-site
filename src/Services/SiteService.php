<?php

namespace Monstrex\AveSite\Services;

use Monstrex\AveSite\Contracts\SiteContract;
use Illuminate\Database\Eloquent\Model;
use Monstrex\AveSite\Models\Setting;

class SiteService implements SiteContract
{
    protected array $setting_cache = [];

    /**
     * Get setting value by key
     *
     * @param string $key Format: "group.field"
     * @param mixed $default
     * @return mixed
     */
    public function setting($key, $default = null)
    {
        if (empty($this->setting_cache)) {
            $this->buildSettingsCache();
        }

        $keys = explode('.', $key);

        if (!isset($keys[0]) || !isset($keys[1])) {
            return $default;
        }

        return $this->setting_cache[$keys[0]][$keys[1]] ?? $default;
    }

    /**
     * Build settings cache from database
     */
    protected function buildSettingsCache(): void
    {
        $settings = Setting::all();

        foreach ($settings as $setting) {
            $decoded = json_decode($setting->fields, true);

            if (!is_array($decoded) || !isset($decoded['fields'])) {
                continue;
            }

            // Extract values from nested structure: fields.fields.field_name.value
            foreach ($decoded['fields'] as $field_name => $field_config) {
                // Skip sections and routes
                if (isset($field_config['type']) && in_array($field_config['type'], ['section', 'route'])) {
                    continue;
                }

                $this->setting_cache[$setting->key][$field_name] = $field_config['value'] ?? null;
            }
        }
    }

    /**
     * Get all site settings for SEO and templates
     *
     * @return array
     */
    public function getSettings(): array
    {
        return [
            'template'        => config('ave-site.template'),
            'template_master' => config('ave-site.template_master'),
            'template_layout' => config('ave-site.template_layout'),
            'template_page'   => config('ave-site.template_page'),
            'site_title'      => $this->setting('general.site_title', config('app.name')),
            'site_description' => $this->setting('general.site_description', ''),
            'seo_title_template' => $this->setting('seo.seo_title_template', '%seo_title% | %site_title%'),
            'seo_title'       => $this->setting('seo.seo_title', ''),
            'meta_description' => $this->setting('seo.meta_description', ''),
            'meta_keywords'   => $this->setting('seo.meta_keywords', ''),
        ];
    }

    /**
     * Store media file for model
     *
     * @param Model $class
     * @param string $field
     * @return string
     */
    public function storeMediaFile(Model $class, $field): string
    {
        $result = $class->addMediaFromRequest($field)->toMediaCollection($field);
        return $result;
    }

    /**
     * Get current request path
     *
     * @return string
     */
    public function currentPath(): string
    {
        return request()->path();
    }
}
