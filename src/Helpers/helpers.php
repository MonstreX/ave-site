<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
 * Translit cyrillic to latin
 */
if (!function_exists('translit_cyrillic')) {
    function translit_cyrillic($string)
    {
        $charlist = [
            "А" => "A", "Б" => "B", "В" => "V", "Г" => "G",
            "Д" => "D", "Е" => "E", "Ж" => "J", "З" => "Z", "И" => "I",
            "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N",
            "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
            "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH",
            "Ш" => "SH", "Щ" => "SCH", "Ъ" => "", "Ы" => "YI", "Ь" => "",
            "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j",
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
            "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya", " " => "_"
        ];
        return strtr($string, $charlist);
    }
}

/*
 * Get file path from JSON or string
 */
if (!function_exists('get_file')) {
    function get_file($file_path)
    {
        $ops = json_decode($file_path);
        if ($ops) {
            return Storage::url(str_replace('\\', '/', $ops[0]->download_link));
        } else {
            return Storage::url(str_replace('\\', '/', $file_path));
        }
    }
}

/*
 * Store uploaded files
 */
if (!function_exists('store_post_files')) {
    function store_post_files(Request $request, $slug, $field, $public = 'public')
    {
        if (!$request->has($field)) {
            return json_encode([]);
        }

        $files = Arr::wrap($request->file($field));
        $filesPath = [];

        foreach ($files as $file) {
            $path = $slug . DIRECTORY_SEPARATOR . date('FY') . DIRECTORY_SEPARATOR;
            $filename = generate_filename($file, $path);

            $file->storeAs(
                $path,
                $filename . '.' . $file->getClientOriginalExtension(),
                config('filesystems.default', $public)
            );

            array_push($filesPath, [
                'download_link' => $path . $filename . '.' . $file->getClientOriginalExtension(),
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        return json_encode($filesPath);
    }
}

/*
 * Generate unique filename
 */
if (!function_exists('generate_filename')) {
    function generate_filename($file, $path)
    {
        $filename = basename(translit_cyrillic($file->getClientOriginalName()), '.' . $file->getClientOriginalExtension());
        $filename_counter = 1;

        // Make sure the filename does not exist, if it does make sure to add a number to the end 1, 2, 3, etc...
        while (Storage::disk(config('filesystems.default'))->exists($path . $filename . '.' . $file->getClientOriginalExtension())) {
            $filename = basename(translit_cyrillic($file->getClientOriginalName()), '.' . $file->getClientOriginalExtension()) . (string)($filename_counter++);
        }

        return $filename;
    }
}

/*
 * Get site setting value
 */
if (!function_exists('site_setting')) {
    function site_setting($key, $default = null)
    {
        return app(\Monstrex\AveSite\Services\SiteService::class)->setting($key, $default);
    }
}

/*
 * Get site settings group
 */
if (!function_exists('site_settings_group')) {
    function site_settings_group($key)
    {
        if (\Schema::hasTable('ave_site_settings')) {
            $settings = \Monstrex\AveSite\Models\Setting::byGroup($key)->first();
            if ($settings) {
                return $settings->getFieldsArray();
            }
        }
        return [];
    }
}

/*
 * Get or create WebP image
 */
if (!function_exists('get_image_or_create_webp')) {
    function get_image_or_create_webp($image_path, $width = null, $height = null, $quality = null)
    {
        return app(\Monstrex\AveSite\Services\DataService::class)
            ->getImageOrCreate($image_path, $width, $height, 'webp', $quality);
    }
}

/*
 * Get WebP version of image
 */
if (!function_exists('get_image_webp')) {
    function get_image_webp($image_path)
    {
        return app(\Monstrex\AveSite\Services\DataService::class)
            ->getImageOrCreate($image_path, null, null, 'webp');
    }
}

/*
 * Get or create image with specified dimensions
 */
if (!function_exists('get_image_or_create')) {
    function get_image_or_create($image_path, $width = null, $height = null, $format = null, $quality = null)
    {
        return app(\Monstrex\AveSite\Services\DataService::class)
            ->getImageOrCreate($image_path, $width, $height, $format, $quality);
    }
}

/*
 * Generate responsive <img> tag with automatic srcset
 *
 * Usage:
 *   responsive_image($src, 800, 600)
 *   responsive_image($src, 800, 600, ['alt' => 'My image', 'class' => 'hero'])
 *   responsive_image($src, 800, 600, ['sizes' => '(max-width: 768px) 100vw, 50vw'])
 *
 * Options:
 *   - format: Image format (default: 'webp')
 *   - quality: Image quality 1-100 (default: 80)
 *   - alt: Alt text
 *   - class: CSS class
 *   - lazy: Enable lazy loading (default: true)
 *   - sizes: Custom sizes attribute
 *   - srcset: Custom srcset widths array [400, 800, 1200]
 */
if (!function_exists('responsive_image')) {
    function responsive_image(string $src, int $width, ?int $height = null, array $options = [])
    {
        return app(\Monstrex\AveSite\Services\ResponsiveImageService::class)
            ->image($src, $width, $height, $options);
    }
}

/*
 * Generate responsive <picture> tag with WebP and fallback formats
 *
 * Usage:
 *   responsive_picture($src, 800, 600)
 *   responsive_picture($src, 800, 600, ['alt' => 'My image'])
 *   responsive_picture($src, 800, 600, [
 *       'breakpoints' => [
 *           ['media' => '(max-width: 768px)', 'width' => 400, 'height' => 400],
 *       ]
 *   ])
 *
 * Options:
 *   - quality: Image quality 1-100 (default: 80)
 *   - alt: Alt text
 *   - class: CSS class
 *   - lazy: Enable lazy loading (default: true)
 *   - sizes: Custom sizes attribute
 *   - srcset: Custom srcset widths array
 *   - formats: Array of formats for sources (default: ['webp'])
 *   - fallback: Fallback format for img tag (default: auto-detect)
 *   - breakpoints: Array of art direction breakpoints
 *       Each: ['media' => '(max-width: 768px)', 'width' => 400, 'height' => 400]
 */
if (!function_exists('responsive_picture')) {
    function responsive_picture(string $src, int $width, ?int $height = null, array $options = [])
    {
        return app(\Monstrex\AveSite\Services\ResponsiveImageService::class)
            ->picture($src, $width, $height, $options);
    }
}

/*
 * Returns first NOT empty element in given array
 */
if (!function_exists('get_first_not_empty')) {
    function get_first_not_empty(array $values)
    {
        foreach ($values as $value) {
            if (!empty($value)) {
                return $value;
            }
        }
        return null;
    }
}

/*
 * Render breadcrumbs with Schema.org microdata
 *
 * Usage:
 *   render_breadcrumbs($breadcrumbs)
 *   render_breadcrumbs($breadcrumbs, ['separator' => '→', 'class' => 'my-breadcrumbs'])
 *
 * Options:
 *   - home_title: Title for home link (default: 'Home')
 *   - separator: Separator between items (default: '/')
 *   - class: CSS class for nav element (default: 'breadcrumbs')
 *   - item_class: CSS class for each item (default: 'breadcrumb-item')
 *   - active_class: CSS class for active/last item (default: 'active')
 *   - schema: Include Schema.org microdata (default: true)
 *
 * @param array $breadcrumbs Array of ['label' => '', 'url' => '']
 * @param array $options Rendering options
 * @return \Illuminate\Support\HtmlString
 */
if (!function_exists('render_breadcrumbs')) {
    function render_breadcrumbs(array $breadcrumbs, array $options = [])
    {
        if (empty($breadcrumbs)) {
            return new \Illuminate\Support\HtmlString('');
        }

        $separator = $options['separator'] ?? '/';
        $class = $options['class'] ?? 'breadcrumbs';
        $itemClass = $options['item_class'] ?? 'breadcrumb-item';
        $activeClass = $options['active_class'] ?? 'active';
        $schema = $options['schema'] ?? true;

        $schemaAttr = $schema ? ' itemscope itemtype="https://schema.org/BreadcrumbList"' : '';
        $html = "<nav class=\"{$class}\" aria-label=\"Breadcrumb\">\n";
        $html .= "    <ol{$schemaAttr}>\n";

        $total = count($breadcrumbs);
        foreach ($breadcrumbs as $index => $item) {
            $position = $index + 1;
            $isLast = ($position === $total);
            $label = htmlspecialchars($item['label'] ?? '', ENT_QUOTES, 'UTF-8');
            $url = $item['url'] ?? '#';

            $itemClasses = $itemClass . ($isLast ? ' ' . $activeClass : '');

            if ($schema) {
                $html .= "        <li class=\"{$itemClasses}\" itemprop=\"itemListElement\" itemscope itemtype=\"https://schema.org/ListItem\">\n";
                if ($isLast) {
                    $html .= "            <span itemprop=\"name\">{$label}</span>\n";
                } else {
                    $html .= "            <a itemprop=\"item\" href=\"{$url}\"><span itemprop=\"name\">{$label}</span></a>\n";
                }
                $html .= "            <meta itemprop=\"position\" content=\"{$position}\">\n";
                $html .= "        </li>\n";
            } else {
                $html .= "        <li class=\"{$itemClasses}\">\n";
                if ($isLast) {
                    $html .= "            <span>{$label}</span>\n";
                } else {
                    $html .= "            <a href=\"{$url}\">{$label}</a>\n";
                }
                $html .= "        </li>\n";
            }

            // Add separator (except after last item)
            if (!$isLast && $separator) {
                $html .= "        <li class=\"breadcrumb-separator\" aria-hidden=\"true\">{$separator}</li>\n";
            }
        }

        $html .= "    </ol>\n";
        $html .= "</nav>";

        return new \Illuminate\Support\HtmlString($html);
    }
}

/*
 * Render block by key
 */
if (!function_exists('render_block')) {
    function render_block($key)
    {
        return app(\Monstrex\AveSite\Services\BlockService::class)->render($key);
    }
}

/*
 * Render region
 */
if (!function_exists('render_region')) {
    function render_region($key, $path = null)
    {
        return app(\Monstrex\AveSite\Services\BlockService::class)->renderRegion($key, $path);
    }
}

/*
 * Render form
 */
if (!function_exists('render_form')) {
    function render_form($key, $subject = null, $suffix = null)
    {
        return app(\Monstrex\AveSite\Services\BlockService::class)->renderForm($key, $subject, $suffix);
    }
}

/*
 * Render layout
 */
if (!function_exists('render_layout')) {
    function render_layout($layout, $page)
    {
        return app(\Monstrex\AveSite\Services\BlockService::class)->renderLayout($layout, $page);
    }
}

/*
 * Get block field value
 */
if (!function_exists('get_block_field')) {
    function get_block_field($block, $field)
    {
        return app(\Monstrex\AveSite\Services\BlockService::class)->getBlockField($block, $field);
    }
}

/*
 * Convert flat array to tree structure
 */
if (!function_exists('flat_to_tree')) {
    function flat_to_tree(array $array, $parent_id = null): array
    {
        $tree = [];

        foreach ($array as $item) {
            if ($item['parent_id'] == $parent_id) {
                $children = flat_to_tree($array, $item['id']);

                if ($children) {
                    $item['children'] = $children;
                }

                $tree[] = $item;
            }
        }

        return $tree;
    }
}

if (!function_exists('seo_meta')) {
    function seo_meta(mixed $model): array
    {
        if (is_object($model) && method_exists($model, 'seoMeta')) {
            return $model->seoMeta();
        }

        if (is_array($model)) {
            return [
                'seo_title' => $model['seo_title'] ?? '',
                'meta_description' => $model['meta_description'] ?? '',
                'meta_keywords' => $model['meta_keywords'] ?? '',
            ];
        }

        return [
            'seo_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
        ];
    }
}

