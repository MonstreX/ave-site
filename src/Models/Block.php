<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Monstrex\Ave\Media\Traits\HasMedia;
use Monstrex\Ave\Core\Fields\Fieldset\HasFieldSet;

class Block extends Model
{
    use HasMedia;
    use HasFieldSet;
    
    protected $table = 'ave_site_blocks';

    protected $fillable = [
        'title', 'key', 'region_id', 'order', 'status',
        'urls', 'rules', 'content', 'images', 'elements', 'details',
    ];

    protected $casts = [
        'elements' => 'array',
        'details' => 'array',
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
        $details = is_array($this->details) ? $this->details : [];
        return isset($details['validator']);
    }
}
