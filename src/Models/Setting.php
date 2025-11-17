<?php

namespace Monstrex\AveSite\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
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
     * Получить все поля как ассоциативный массив key=>value
     */
    public function getFieldsArray(): array
    {
        $decoded = json_decode($this->fields, true);

        if (!isset($decoded['fields'])) {
            return [];
        }

        $result = [];

        foreach ($decoded['fields'] as $fieldKey => $fieldConfig) {
            // Пропускаем секции и маршруты (они не содержат данные для сохранения)
            if (isset($fieldConfig['type']) && in_array($fieldConfig['type'], ['section', 'route'])) {
                continue;
            }

            // Типизация значений
            $value = $fieldConfig['value'] ?? null;

            if (isset($fieldConfig['type'])) {
                $value = match($fieldConfig['type']) {
                    'checkbox' => (bool)$value,
                    'number' => is_numeric($value) ? (int)$value : null,
                    default => $value,
                };
            }

            $result[$fieldKey] = $value;
        }

        return $result;
    }
}
