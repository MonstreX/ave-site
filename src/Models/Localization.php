<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Dynamic localization model for managing translations in database.
 * Supports multiple locales with caching for performance.
 */
class Localization extends Model
{
    protected $table = 'ave_site_localizations';

    protected $guarded = [];

    protected $perPage = 100;

    /**
     * Cache duration in minutes (24 hours)
     */
    private const CACHE_TTL = 24 * 60;

    /**
     * Load all localizations for the current locale into Laravel's translator.
     * This method is called from the ServiceProvider's boot() phase.
     */
    public function loadLocalizations(): void
    {
        $locale = app()->getLocale();
        $cacheKey = "ave_site_localizations.{$locale}";

        try {
            // Try to get from cache first
            $locLines = Cache::remember(
                $cacheKey,
                self::CACHE_TTL,
                fn() => $this->getLocalizedLines($locale)
            );

            if (!empty($locLines)) {
                app('translator')->addLines($locLines, $locale);
            }
        } catch (\Exception $e) {
            // Silently fail - table may not exist yet
            // Don't break the application
        }
    }

    /**
     * Get all localized lines for a specific locale.
     * Returns array of [key => translated_value]
     */
    private function getLocalizedLines(string $locale): array
    {
        return $this
            ->whereNotNull($locale)
            ->where($locale, '!=', '')
            ->pluck($locale, 'key')
            ->toArray();
    }

    /**
     * Clear localization cache when any localization is saved/updated
     */
    protected static function booted(): void
    {
        static::saved(function (self $model) {
            Cache::forget("ave_site_localizations.*");
        });

        static::deleted(function (self $model) {
            Cache::forget("ave_site_localizations.*");
        });
    }

    /**
     * Scope to filter by key
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Scope to filter by locale
     */
    public function scopeByLocale($query, string $locale)
    {
        return $query->whereNotNull($locale)
            ->where($locale, '!=', '');
    }
}
