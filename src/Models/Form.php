<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $table = 'ave_site_forms';

    protected $fillable = [
        'status',
        'order',
        'title',
        'key',
        'content',
        'details',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
        'details' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
