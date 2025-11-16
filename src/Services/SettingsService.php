<?php

namespace Monstrex\AveSite\Services;

use Monstrex\AveSite\Models\Setting;

class SettingsService
{
    /**
     * Получить настройку (формат: "group.field")
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
     * Получить группу настроек
     */
    public function getGroup(string $group): array
    {
        $setting = Setting::byGroup($group)->first();

        if (!$setting) {
            return [];
        }

        return $setting->getFieldsArray();
    }

    /**
     * Применение runtime-конфигурации
     * (переопределение Laravel config из БД)
     */
    public function applyRuntimeConfig(): void
    {
        $this->applyGeneralConfig();
        $this->applyMailConfig();
    }

    protected function applyGeneralConfig(): void
    {
        $general = $this->getGroup('general');

        if (isset($general['site_title'])) {
            config(['app.name' => $general['site_title']]);
        }

        if (isset($general['debug_mode'])) {
            config(['app.debug' => (bool)$general['debug_mode']]);
        }
    }

    protected function applyMailConfig(): void
    {
        $mail = $this->getGroup('mail');

        if (empty($mail)) {
            return;
        }

        config([
            'mail.from.address' => $mail['from_address'] ?? config('mail.from.address'),
            'mail.from.name' => $mail['from_name'] ?? config('mail.from.name'),
            'mail.mailers.smtp.host' => $mail['smtp_host'] ?? config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => (int)($mail['smtp_port'] ?? config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.encryption' => $mail['smtp_encryption'] ?? config('mail.mailers.smtp.encryption'),
            'mail.mailers.smtp.username' => $mail['smtp_username'] ?? config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $mail['smtp_password'] ?? config('mail.mailers.smtp.password'),
        ]);
    }
}
