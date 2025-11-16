<?php

namespace Monstrex\AveSite\Templates;

use Monstrex\AveSite\Facades\AveBlock;

class CustomShortcodes
{
    /**
     * Block shortcode: [block name="top-line"]
     */
    public function block($shortcode, $content, $compiler, $name, $viewData)
    {
        return AveBlock::render($shortcode->name);
    }

    /**
     * Form shortcode: [form name="callback-form" subject="Callback form submission" suffix="callback-frm"]
     */
    public function form($shortcode, $content, $compiler, $name, $viewData)
    {
        return AveBlock::renderForm($shortcode->name, $shortcode->subject ?? null, $shortcode->suffix ?? null);
    }

    /**
     * Div shortcode: [div class="wrapper"] CONTENT [/div]
     */
    public function div($shortcode, $content, $compiler, $name, $viewData)
    {
        $class = $shortcode->get('class', 'default');
        return '<div class="' . $class . '">' . $content . '</div>';
    }

    /**
     * Image shortcode
     * [image field="images" index="3" width="50%" height="50%" lightbox="true" picture="true" format="webp" crop="400,400" class="responsive" lightbox_class="image-gallery"]
     * [image url="/storage/images/file.jpg" width="50%" height="50%" lightbox="true" picture="true" format="webp" crop="400,400" class="responsive" lightbox_class="image-gallery"]
     */
    public function image($shortcode, $content, $compiler, $name, $viewData)
    {
        // Get image URL
        if (isset($shortcode->url)) {
            $image_origin = $image_src = $shortcode->url;
        } else {
            // Try to get from viewData
            if (!isset($viewData['page'])) {
                return '';
            }

            $field = $shortcode->field ?? 'images';
            $index = isset($shortcode->index) ? (int)$shortcode->index - 1 : 0;

            // For future implementation with media library
            // Currently just return empty
            return '';
        }

        $class = $shortcode->class ?? '';
        $width = $shortcode->width ?? '';
        $height = $shortcode->height ?? '';
        $format = $shortcode->format ?? null;

        // Crop if required
        if (isset($shortcode->crop)) {
            $size = explode(',', $shortcode->crop);
            if (count($size) === 2 && is_numeric($size[0]) && is_numeric($size[1])) {
                $image_src = get_image_or_create($image_origin, $size[0], $size[1], $format);
            }
        }

        $html = '<img src="' . $image_src . '"';

        if ($class) {
            $html .= ' class="' . $class . '"';
        }

        if ($width) {
            $html .= ' width="' . $width . '"';
        }

        if ($height) {
            $html .= ' height="' . $height . '"';
        }

        $html .= ' alt="">';

        // Wrap with lightbox if needed
        if (isset($shortcode->lightbox) && $shortcode->lightbox) {
            $lightbox_class = $shortcode->lightbox_class ?? 'lightbox';
            $html = '<a href="' . $image_origin . '" class="' . $lightbox_class . '">' . $html . '</a>';
        }

        return $html;
    }
}
