<?php

namespace Monstrex\AveSite\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SiteContract
{
    public function setting($key, $default = null);
    public function getSettings(): array;
    public function storeMediaFile(Model $class, $field): string;
    public function currentPath(): string;
}
