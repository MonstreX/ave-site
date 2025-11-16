<?php

namespace Monstrex\AveSite\Contracts;

interface DataContract
{
    public function findFirst($alias, string $modelSlug = null, bool $fail = true);
    public function where(string $field, string $value, string $modelSlug = null, bool $fail = true);
    public function findByField(string $modelSlug, string $field, $value, string $order = 'order', string $direction = 'ASC');
    public function getDataSources(array $datasources): array;
    public function getMenu(string $modelSlug = null, array $parent = null);
    public function getImageOrCreate(string $image_url, int $width = null, int $height = null, string $format = null, int $quality = null): string;
}
