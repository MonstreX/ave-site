<?php

namespace Monstrex\AveSite\Templates;

use Liquid\Template as LiquidTemplate;

class Template
{
    protected LiquidTemplate $template;

    public function __construct($content)
    {
        $this->template = new LiquidTemplate();

        // Load custom filters if configured
        $custom_filters = config('ave-site.template_filters');
        if ($custom_filters && class_exists($custom_filters)) {
            app($custom_filters)->handle($this->template, $content);
        }

        // SETTINGS
        $this->template->registerFilter('site_setting', function ($arg, $arg2 = null) {
            return site_setting($arg, $arg2);
        });

        // MENU (uses global menu() helper from ave.package)
        $this->template->registerFilter('menu', function ($name, $template = null) {
            return menu($name, $template);
        });

        // BLOCK
        $this->template->registerFilter('block', function ($arg) {
            return render_block($arg);
        });

        // FORM
        $this->template->registerFilter('form', function ($name, $subject = null, $suffix = null) {
            return render_form($name, $subject, $suffix);
        });

        // URL
        $this->template->registerFilter('url', function ($arg) {
            return url($arg);
        });

        // ROUTE
        $this->template->registerFilter('route', function ($route, $param = null) {
            if ($param) {
                return route($route, $param);
            } else {
                return route($route);
            }
        });

        // CONVERT TO WEBP IMAGE
        $this->template->registerFilter('webp', function ($image, $quality = null) {
            return get_image_or_create($image, null, null, 'webp', $quality);
        });

        // CROP IMAGE
        $this->template->registerFilter('crop', function ($image, $xsize = '', $ysize = '', $format = null, $quality = null) {
            return get_image_or_create($image, $xsize, $ysize, $format, $quality);
        });

        // RESPONSIVE IMAGE - generates <img> with srcset
        // Usage: {{ image.url | responsive_image: 800, 600 }}
        $this->template->registerFilter('responsive_image', function ($image, $width = 800, $height = null) {
            return responsive_image($image, (int) $width, $height ? (int) $height : null);
        });

        // RESPONSIVE PICTURE - generates <picture> with WebP + fallback
        // Usage: {{ image.url | responsive_picture: 800, 600 }}
        $this->template->registerFilter('responsive_picture', function ($image, $width = 800, $height = null) {
            return responsive_picture($image, (int) $width, $height ? (int) $height : null);
        });

        // BREADCRUMBS - renders breadcrumbs with Schema.org markup
        // Usage: {{ breadcrumbs | breadcrumbs }}
        // Usage: {{ breadcrumbs | breadcrumbs: '→' }}
        $this->template->registerFilter('breadcrumbs', function ($breadcrumbs, $separator = '/') {
            if (!is_array($breadcrumbs)) {
                return '';
            }
            return render_breadcrumbs($breadcrumbs, ['separator' => $separator]);
        });

        // TRANSLATE using lang files
        $this->template->registerFilter('lang', function ($arg) {
            return __($arg);
        });

        // DEBUG: Dump variable using Laravel dump() - for development only
        $this->template->registerFilter('dump', function ($arg) {
            if (config('app.debug')) {
                ob_start();
                dump($arg);
                return ob_get_clean();
            }
            return '';
        });

        $this->template->parse($content);
    }

    public function render($vars)
    {
        return $this->template->render($vars);
    }
}
