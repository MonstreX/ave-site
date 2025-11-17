<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    protected $table = 'ave_site_blocks';

    protected $fillable = [
        'title', 'key', 'region_id', 'order', 'status',
        'urls', 'rules', 'content', 'media', 'options',
    ];

    protected $casts = [
        'media' => 'array',
        'status' => 'boolean',
        'rules' => 'integer',
    ];

    // Relationships
    public function region(): BelongsTo
    {
        return $this->belongsTo(BlockRegion::class, 'region_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInRegion($query, $regionKey)
    {
        return $query->whereHas('region', fn($q) => $q->where('key', $regionKey));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Helpers
    public function isForm(): bool
    {
        $options = json_decode($this->options, true) ?? [];
        return isset($options['validator']);
    }

    // Options Accessor
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
