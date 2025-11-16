<?php

if (!function_exists('site_setting')) {
    /**
     * Получить настройку сайта
     */
    function site_setting(string $key, $default = null)
    {
        return app(\Monstrex\AveSite\Services\SettingsService::class)->get($key, $default);
    }
}

if (!function_exists('site_settings_group')) {
    /**
     * Получить группу настроек
     */
    function site_settings_group(string $group): array
    {
        return app(\Monstrex\AveSite\Services\SettingsService::class)->getGroup($group);
    }
}
