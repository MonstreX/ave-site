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

        // MENU (for future implementation)
        $this->template->registerFilter('menu', function ($name, $template = null) {
            // TODO: Implement menu rendering
            return '';
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

        // TRANSLATE using lang files
        $this->template->registerFilter('lang', function ($arg) {
            return __($arg);
        });

        $this->template->parse($content);
    }

    public function render($vars)
    {
        return $this->template->render($vars);
    }
}
