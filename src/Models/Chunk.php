<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;

class Chunk extends Model
{
    protected $table = 'site_chunks';

    protected $fillable = ['key', 'value'];

    // Scopes
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }
}
