<?php

namespace Monstrex\AveSite\Services;

use Liquid\Template as LiquidTemplate;
use Illuminate\Support\Facades\Storage;

class LiquidTemplateService
{
    protected LiquidTemplate $template;

    public function __construct()
    {
        $this->template = new LiquidTemplate;
        $this->registerFilters();
        $this->registerCustomFilters();
    }

    /**
     * Рендер Liquid шаблона
     */
    public function render(string $content, array $vars = []): string
    {
        try {
            $this->template->parse($content);
            return $this->template->render($vars);
        } catch (\Exception $e) {
            return '<!-- Liquid Error: ' . $e->getMessage() . ' -->';
        }
    }

    /**
     * Регистрация встроенных фильтров
     */
    protected function registerFilters(): void
    {
        // === URL & Routes ===
        $this->template->registerFilter('url', fn($path) => url($path));

        $this->template->registerFilter('route', function($route, $param = null) {
            try {
                return $param ? route($route, $param) : route($route);
            } catch (\Exception $e) {
                return '#';
            }
        });

        $this->template->registerFilter('asset', fn($path) => asset($path));
        $this->template->registerFilter('storage', fn($path) => Storage::url($path));

        // === Content ===
        $this->template->registerFilter('block', fn($key) => render_block($key));

        $this->template->registerFilter('chunk', fn($key) => chunk($key));

        $this->template->registerFilter('site_setting', function($group, $field = null) {
            if ($field) {
                return site_setting("{$group}.{$field}");
            }
            return site_setting($group);
        });

        // === Images ===
        $this->template->registerFilter('crop', function($image, $width = null, $height = null, $format = null, $quality = null) {
            return app(ImageProcessingService::class)->getOrCreate($image, $width, $height, $format, $quality ?? 85);
        });

        $this->template->registerFilter('webp', function($image, $quality = null) {
            return app(ImageProcessingService::class)->getOrCreate($image, null, null, 'webp', $quality ?? 85);
        });

        // === Translations ===
        $this->template->registerFilter('lang', fn($key) => __($key));
        $this->template->registerFilter('translate', fn($key) => __($key)); // Alias

        // === CSRF ===
        $this->template->registerFilter('csrf_token', fn() => csrf_token());
        $this->template->registerFilter('csrf_field', fn() => csrf_field());
    }

    /**
     * Регистрация кастомных фильтров из конфига
     */
    protected function registerCustomFilters(): void
    {
        $customClass = config('ave-site.template_filters');

        if ($customClass && class_exists($customClass)) {
            $instance = app($customClass);

            if (method_exists($instance, 'handle')) {
                $instance->handle($this->template);
            }
        }
    }
}
