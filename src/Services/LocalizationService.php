<?php

namespace Monstrex\AveSite\Services;

use Monstrex\AveSite\Models\Localization;

/**
 * Service for managing dynamic localizations.
 * Provides methods for loading, saving, and managing translation strings in the database.
 */
class LocalizationService
{
    /**
     * Load localizations from database for the current locale.
     * Called automatically from ServiceProvider's boot() phase.
     */
    public function loadLocalizations(): void
    {
        $localization = app(Localization::class);
        $localization->loadLocalizations();
    }

    /**
     * Get a localized string by key.
     * Falls back to the key if translation not found.
     */
    public function get(string $key, string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $localization = Localization::byKey($key)->first();

        if ($localization && isset($localization->$locale)) {
            $value = $localization->$locale;
            return !empty($value) ? $value : $key;
        }

        return $key;
    }

    /**
     * Store a localization string.
     */
    public function set(string $key, string $value, string $locale = null): Localization
    {
        $locale = $locale ?? app()->getLocale();

        $localization = Localization::byKey($key)->first()
            ?? new Localization(['key' => $key]);

        $localization->$locale = $value;
        $localization->save();

        return $localization;
    }

    /**
     * Get all localizations for a specific locale.
     */
    public function getByLocale(string $locale): array
    {
        return Localization::byLocale($locale)
            ->pluck($locale, 'key')
            ->toArray();
    }

    /**
     * Check if a localization key exists.
     */
    public function has(string $key, string $locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();
        $localization = Localization::byKey($key)->first();

        return $localization && isset($localization->$locale) && !empty($localization->$locale);
    }

    /**
     * Delete a localization by key.
     */
    public function delete(string $key): bool
    {
        return Localization::byKey($key)->delete() > 0;
    }

    /**
     * Get all localization keys.
     */
    public function getAllKeys(): array
    {
        return Localization::pluck('key')->toArray();
    }
}
