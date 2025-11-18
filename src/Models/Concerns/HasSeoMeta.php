<?php

namespace Monstrex\AveSite\Models\Concerns;

trait HasSeoMeta
{
    public function seoMeta(): array
    {
        return $this->normalizeSeoMeta($this->getAttribute('seo'));
    }

    public function getSeoMetaAttribute(): array
    {
        return $this->seoMeta();
    }

    protected function normalizeSeoMeta(mixed $value): array
    {
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (!is_array($value)) {
            return $this->emptySeoMeta();
        }

        if (isset($value[0]) && is_array($value[0])) {
            return $this->ensureSeoKeys($value[0]);
        }

        return $this->ensureSeoKeys($value);
    }

    protected function ensureSeoKeys(array $value): array
    {
        return [
            'seo_title' => $value['seo_title'] ?? '',
            'meta_description' => $value['meta_description'] ?? '',
            'meta_keywords' => $value['meta_keywords'] ?? '',
        ];
    }

    protected function emptySeoMeta(): array
    {
        return [
            'seo_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
        ];
    }
}
