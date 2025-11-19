<?php

namespace Monstrex\AveSite\Services;

use Monstrex\AveSite\Contracts\BlockContract;
use Monstrex\AveSite\Models\Block;
use Monstrex\AveSite\Models\BlockRegion;
use Monstrex\AveSite\Models\Form;
use Monstrex\AveSite\Templates\Template;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Webwizo\Shortcodes\Facades\Shortcode;

/**
 * BlockService - Renders blocks and block regions with Liquid templates
 */
class BlockService implements BlockContract
{
    protected const EXCEPT = 0;
    protected const ONLY = 1;

    protected DataService $dataService;

    public function __construct(DataService $dataService)
    {
        $this->dataService = $dataService;
    }

    /**
     * Render all blocks in a region
     *
     * @param string $region_name Region key
     * @param string|null $path Current URL path for filtering blocks
     * @return string Rendered HTML of all blocks
     */
    public function renderRegion($region_name, $path = null)
    {
        $region = BlockRegion::where('key', $region_name)->first();

        if (!$region) {
            return '';
        }

        $blocks = Block::where(['region_id' => $region->id, 'status' => 1])
            ->orderBy('order', 'asc')
            ->get();

        $current_path = $path ? $path : request()->path();

        $html = '';
        foreach ($blocks as $block) {
            $blockShow = false;

            $urls = explode(PHP_EOL, str_replace('<front>', '/', $block->urls));
            foreach ($urls as $key => $url) {
                if (empty($url)) {
                    unset($urls[$key]);
                }
            }

            // Set visibility ON EVERY PAGE
            if ($block->rules === self::EXCEPT && empty($urls)) {
                $blockShow = true;
            } elseif ($block->rules == self::EXCEPT) {
                // Set visibility ON EVERY PAGE EXCEPT SELECTED
                $blockShow = true;
                foreach ($urls as $url) {
                    if (Str::is($url, $current_path)) {
                        $blockShow = false;
                        break;
                    }
                }
            } elseif ($block->rules == self::ONLY && !empty($urls)) {
                // Set visibility ONLY ON SPECIFIC PAGES
                $blockShow = false;
                foreach ($urls as $url) {
                    if (Str::is($url, $current_path)) {
                        $blockShow = true;
                        break;
                    }
                }
            }

            if ($blockShow) {
                $html .= $this->renderBlock($block);
            }
        }

        return $html;
    }

    /**
     * Render a single block by key or ID
     *
     * @param string|int $key Block key or ID
     * @return string Rendered HTML
     */
    public function render($key)
    {
        if (is_numeric($key)) {
            $block = $this->getByID($key);
        } else {
            $block = $this->getByKey($key);

            if (!$block) {
                $block = $this->getByTitle($key);
            }
        }

        return $this->renderBlock($block);
    }

    /**
     * Render a single block
     *
     * @param Block|null $block
     * @return string
     */
    public function renderBlock($block)
    {
        if (!$block) {
            return '';
        }

        // Get details (already cast to array)
        $details = $block->details ?? [];

        // Get datasources if defined
        $datasources = $details['datasources'] ?? [];
        $data = $this->dataService->getDataSources($datasources);

        // Process media images - convert Media objects to arrays for Liquid
        $images = $block->getMedia('block_images')->map(fn($m) => $this->mediaToArray($m))->toArray();

        // Process elements - auto-detect and load Media fields
        $elements = collect($block->elements ?? [])->map(function ($element) use ($block) {
            foreach ($element as $key => $value) {
                // Check if value is a media collection name (starts with 'elements.')
                if (is_string($value) && str_starts_with($value, 'elements.')) {
                    // Replace collection name with actual media array
                    $element[$key] = $block->getMedia($value)
                        ->map(fn($m) => $this->mediaToArray($m))
                        ->toArray();
                }
            }
            return $element;
        })->toArray();

        // Merge with block data
        $vars = array_merge($data, [
            'block' => [
                'id' => $block->id,
                'key' => $block->key,
                'title' => $block->title,
                'content' => $block->content,
                'images' => $images,
                'elements' => $elements,
                'details' => $details,
            ],
        ]);

        // Render content with Liquid template engine (via Template wrapper)
        $template = new Template(Shortcode::compile($block->content));
        return $template->render($vars);
    }

    /**
     * Render form block
     *
     * @param string $key Form key
     * @param string|null $subject Form subject
     * @param string|null $suffix Form suffix
     * @return string
     */
    public function renderForm($key, $subject = null, $suffix = null)
    {
        $form = $this->getFormByKey($key);

        if (!$form) {
            return '';
        }

        $vars = [];
        $vars['old'] = session()->getOldInput();

        $errors = Session::get('errors', new MessageBag());
        $vars['errors_messages'] = $errors->all();
        $vars['errors'] = $errors->toArray();

        $vars['form_alias'] = $key;
        $vars['form_suffix'] = $suffix;
        $vars['form_subject'] = $subject;
        $vars['csrf_token'] = csrf_token();

        $template = new Template(Shortcode::compile($form->content));
        return $template->render($vars);
    }

    /**
     * Render layout from JSON config
     *
     * @param string $layout JSON layout configuration
     * @param mixed $page Page model
     * @return string
     */
    public function renderLayout($layout, $page)
    {
        $layoutFields = json_decode($layout);

        if (!$layoutFields) {
            return '';
        }

        $html = '';
        foreach ($layoutFields as $field) {
            if ($field->type === 'Block') {
                $html .= $this->render($field->key);
            } elseif ($field->type === 'Form') {
                $html .= $this->renderForm($field->key);
            } elseif ($field->type === 'Field') {
                $html .= $page->{$field->key};
            }
        }

        return $html;
    }

    /**
     * Get block by ID
     *
     * @param int $id
     * @return Block|null
     */
    public function getByID($id)
    {
        return Block::where(['id' => $id, 'status' => 1])->first();
    }

    /**
     * Get block by key
     *
     * @param string $key
     * @return Block|null
     */
    public function getByKey($key)
    {
        return Block::where(['key' => $key, 'status' => 1])->first();
    }

    /**
     * Get block by title
     *
     * @param string $title
     * @return Block|null
     */
    public function getByTitle($title)
    {
        return Block::where(['title' => trim($title), 'status' => 1])->first();
    }

    /**
     * Get form by key (for future implementation)
     *
     * @param string $key
     * @return mixed
     */
    public function getFormByKey($key)
    {
        return Form::where(['key' => $key, 'status' => 1])->first();
    }

    /**
     * Get specific field value from block
     *
     * @param string $block Block key or title
     * @param string $field Field name
     * @return mixed
     */
    public function getBlockField($block, $field)
    {
        $blockModel = $this->getByKey($block);

        if (!$blockModel) {
            $blockModel = $this->getByTitle($block);
        }

        return $blockModel ? $blockModel->{$field} : null;
    }

    /**
     * Convert Media object to array for Liquid templates
     * Returns all media properties + props object with ALL dynamic properties
     *
     * @param \Monstrex\Ave\Models\Media $media
     * @return array
     */
    private function mediaToArray($media): array
    {
        return [
            'url' => $media->url(),
            'fullUrl' => $media->fullUrl(),
            'path' => $media->path(),
            'fileName' => $media->fileName(),
            'size' => $media->size(),
            'mime' => $media->mime(),
            'order' => $media->order(),
            'props' => $media->props(), // stdClass with ALL props (alt, title, any custom)
        ];
    }
}
