<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;
use Monstrex\Ave\Media\Facades\Media as MediaFacade;
use Monstrex\Ave\Media\Traits\HasMedia;
use Monstrex\Ave\Models\Media as AveMedia;

class Setting extends Model
{
    use HasMedia;

    protected $table = 'ave_site_settings';

    protected $fillable = ['key', 'group', 'title', 'order', 'fields'];

    // Scopes
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get decoded field values as a key=>value array
     */
    public function getFieldsArray(): array
    {
        $decoded = json_decode($this->fields, true);

        if (!isset($decoded['fields'])) {
            return [];
        }

        $result = [];

        foreach ($decoded['fields'] as $fieldKey => $fieldConfig) {
            if (isset($fieldConfig['type']) && in_array($fieldConfig['type'], ['section', 'route'])) {
                continue;
            }

            $value = $fieldConfig['value'] ?? null;

            if (isset($fieldConfig['type'])) {
                $value = match ($fieldConfig['type']) {
                    'checkbox' => (bool) $value,
                    'number' => is_numeric($value) ? (int) $value : null,
                    'media', 'image' => $this->resolveMediaValue($value),
                    default => $value,
                };
            }

            $result[$fieldKey] = $value;
        }

        return $result;
    }

    public function getMediaItem(string $collection): ?AveMedia
    {
        if ($this->relationLoaded('media')) {
            return $this->getRelation('media')
                ->where('collection_name', $collection)
                ->sortBy('order')
                ->first();
        }

        return $this->getMedia($collection)->first();
    }

    public function clearMediaCollection(string $collection): void
    {
        MediaFacade::model($this)->collection($collection)->delete();
    }

    protected function resolveMediaValue($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            $media = AveMedia::find((int) $value);

            return $media ? $media->url() : null;
        }

        return is_string($value) ? $value : null;
    }
}
