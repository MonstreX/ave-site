<?php

if (!function_exists('chunk')) {
    /**
     * Получить значение чанка
     */
    function chunk(string $key, $default = null): ?string
    {
        return app(\Monstrex\AveSite\Services\ChunkService::class)->get($key, $default);
    }
}
