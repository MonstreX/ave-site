<?php

namespace Monstrex\AveSite\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Monstrex\Ave\Media\ImageProcessor;

/**
 * ImageProcessingService - Wrapper around Ave's ImageProcessor with caching
 *
 * Provides on-the-fly image resizing and format conversion with file-based caching
 */
class ImageProcessingService
{
    /**
     * Получить или создать обработанное изображение
     *
     * @param string $imagePath Относительный путь (storage/images/photo.jpg)
     * @param int|string|null $width Ширина (или "800x600")
     * @param int|null $height Высота
     * @param string|null $format Формат (webp, jpg, png)
     * @param int $quality Качество (0-100)
     * @return string URL обработанного изображения
     */
    public function getOrCreate(
        string $imagePath,
        $width = null,
        $height = null,
        ?string $format = null,
        int $quality = 85
    ): string {
        // Parse "800x600" format
        if (is_string($width) && str_contains($width, 'x')) {
            [$width, $height] = explode('x', $width);
            $width = (int)$width;
            $height = (int)$height;
        }

        // Удалить host если есть
        $imagePath = $this->removeHost($imagePath);

        // Путь к кешу
        $cachePath = $this->getCachePath($imagePath, $width, $height, $format, $quality);

        // Вернуть кеш если существует
        if (Storage::disk('public')->exists($cachePath)) {
            return Storage::url($cachePath);
        }

        // Проверить существование оригинала
        if (!Storage::disk('public')->exists($imagePath)) {
            return $imagePath; // Вернуть оригинальный путь
        }

        // Обработка с использованием Ave ImageProcessor
        $fullPath = Storage::disk('public')->path($imagePath);
        $processor = new ImageProcessor();
        $processor->read($fullPath);

        // Resize/Crop
        if ($width && $height) {
            $processor->cover((int)$width, (int)$height); // Crop to exact size
        } elseif ($width || $height) {
            $processor->scale((int)$width, (int)$height); // Maintain aspect ratio
        }

        // Encode with format and quality
        $encoded = $processor->encode($format, $quality);

        // Save to cache
        Storage::disk('public')->makeDirectory(dirname($cachePath));
        Storage::disk('public')->put($cachePath, $encoded);

        return Storage::url($cachePath);
    }

    /**
     * Удалить host из URL
     */
    protected function removeHost(string $url): string
    {
        // Удалить scheme + host
        $url = preg_replace('#^https?://[^/]+#', '', $url);

        // Удалить /storage/ prefix
        $url = Str::replaceFirst('/storage/', '', $url);

        return ltrim($url, '/');
    }

    /**
     * Генерация пути к кешу
     */
    protected function getCachePath(string $originalPath, $width, $height, $format, $quality): string
    {
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? '';
        $extension = $format ?? $pathInfo['extension'] ?? 'jpg';

        $cacheDir = config('ave-site.image_cache_dir', 'cached');

        $dimensions = '';
        if ($width) $dimensions .= "w{$width}";
        if ($height) $dimensions .= "h{$height}";
        if ($quality != 85) $dimensions .= "q{$quality}";

        $cachedFilename = $filename . ($dimensions ? "_{$dimensions}" : '') . '.' . $extension;

        return "{$directory}/{$cacheDir}/{$cachedFilename}";
    }
}
