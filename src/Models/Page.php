<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};

class Page extends Model
{
    protected $table = 'ave_site_pages';

    protected $fillable = [
        'parent_id', 'order', 'title', 'slug', 'content', 'media',
        'status', 'seo_title', 'seo_description', 'seo_keywords', 'options',
    ];

    protected $attributes = [
        'parent_id' => -1,
    ];

    protected $casts = [
        'media' => 'array',
        'status' => 'boolean',
    ];

    // === Relationships ===

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')
            ->where('status', true)
            ->orderBy('order');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    // === Scopes ===

    public function scopePublished($query)
    {
        return $query->where('status', true);
    }

    public function scopeRoots($query)
    {
        return $query->where('parent_id', -1);
    }

    // === Helpers ===

    public function isRoot(): bool
    {
        return $this->parent_id === -1;
    }

    public function getSeoTitle(): string
    {
        return $this->seo_title ?? $this->title;
    }

    public function getSeoDescription(): string
    {
        return $this->seo_description ?? '';
    }

    public function getSeoKeywords(): string
    {
        return $this->seo_keywords ?? '';
    }

    // Options Accessor - форматированный JSON
    public function getOptionsAttribute($value)
    {
        if (empty($value)) {
            return "{\n    \n}";
        }

        $decoded = json_decode($value, true);
        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function setOptionsAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['options'] = null;
            return;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $this->attributes['options'] = json_encode($decoded);
        } else {
            $this->attributes['options'] = null;
        }
    }
}
