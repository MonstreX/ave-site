<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $table = 'ave_site_redirects';

    protected $fillable = [
        'from_url',
        'to_url',
        'status_code',
        'is_active',
        'hits',
        'last_hit_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hits' => 'integer',
        'status_code' => 'integer',
        'last_hit_at' => 'datetime',
    ];

    protected $attributes = [
        'hits' => 0,
        'status_code' => 301,
        'is_active' => true,
    ];

    /**
     * Scope to get only active redirects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Find redirect by URL path.
     */
    public static function findByPath(string $path): ?self
    {
        // Normalize path
        $path = '/' . ltrim($path, '/');

        return static::active()
            ->where('from_url', $path)
            ->first();
    }

    /**
     * Increment hit counter.
     */
    public function recordHit(): void
    {
        $this->increment('hits');
        $this->update(['last_hit_at' => now()]);
    }
}
