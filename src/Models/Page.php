<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;
use Monstrex\Ave\Media\Traits\HasMedia;
use Monstrex\AveSite\Models\Concerns\HasSeoMeta;

class Page extends Model
{
    use HasMedia;
    use HasSeoMeta;

    protected $table = 'ave_site_pages';

    protected $guarded = [];
    
    protected $casts = [
        'status' => 'boolean',
        'menu' => 'boolean',
        'seo' => 'array',
    ];

    protected $attributes = [
        'status' => 1,
        'menu' => 1,
    ];

    public function scopePublished($query)
    {
        return $query->where('status', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
