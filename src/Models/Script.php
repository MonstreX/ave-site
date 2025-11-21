<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;

class Script extends Model
{
    protected $table = 'ave_site_scripts';

    protected $fillable = [
        'title',
        'key',
        'status',
        'position',
        'content',
        'details',
        'order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'details' => 'array',
        'order' => 'integer',
    ];

    protected $attributes = [
        'status' => true,
        'order' => 0,
        'position' => 'body_end',
    ];

    /**
     * Scope to get only active scripts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope to filter by position.
     */
    public function scopeByPosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope to order by order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }
}
