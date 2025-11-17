<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlockRegion extends Model
{
    protected $table = 'ave_site_block_regions';

    protected $fillable = ['key', 'name'];

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'region_id');
    }
}
