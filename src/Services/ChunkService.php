<?php

namespace Monstrex\AveSite\Services;

use Monstrex\AveSite\Models\Chunk;

class ChunkService
{
    /**
     * Получить значение чанка
     */
    public function get(string $key, $default = null): ?string
    {
        $chunk = Chunk::byKey($key)->first();
        return $chunk ? $chunk->value : $default;
    }
}
