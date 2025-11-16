<?php

if (!function_exists('get_image')) {
    /**
     * Получить URL обработанного изображения
     */
    function get_image(string $path, $width = null, $height = null, ?string $format = null, int $quality = 85): string
    {
        return app(\Monstrex\AveSite\Services\ImageProcessingService::class)->getOrCreate($path, $width, $height, $format, $quality);
    }
}

if (!function_exists('crop_image')) {
    /**
     * Crop изображение
     */
    function crop_image(string $path, int $width, int $height, ?string $format = null): string
    {
        return get_image($path, $width, $height, $format);
    }
}

if (!function_exists('webp_image')) {
    /**
     * Конвертация в WebP
     */
    function webp_image(string $path, ?int $quality = null): string
    {
        return get_image($path, null, null, 'webp', $quality ?? 85);
    }
}
