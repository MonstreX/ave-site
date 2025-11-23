<?php

namespace Monstrex\AveSite\Services;

use Monstrex\AveSite\Models\Setting;

class SettingsService
{
    /**
     * Get a specific setting value (format: "group.field")
     */
    public function get(string $key, $default = null)
    {
        if (!str_contains($key, '.')) {
            return $default;
        }

        [$group, $field] = explode('.', $key, 2);

        $setting = Setting::byGroup($group)->first();

        if (!$setting) {
            return $default;
        }

        $fields = $setting->getFieldsArray();

        return $fields[$field] ?? $default;
    }

    /**
     * Get an entire settings group as an associative array
     */
    public function getGroup(string $group): array
    {
        $setting = Setting::byGroup($group)->first();

        if (!$setting) {
            return [];
        }

        return $setting->getFieldsArray();
    }
}
