<?php

namespace Monstrex\AveSite\Services;

use Monstrex\AveSite\Contracts\BlockContract;
use Monstrex\AveSite\Models\Block;
use Monstrex\AveSite\Models\BlockRegion;
use Illuminate\Support\Str;

/**
 * BlockService - Renders blocks and block regions with Liquid templates
 */
class BlockService implements BlockContract
{
    protected const EXCEPT = 0;
    protected const ONLY = 1;

    protected LiquidTemplateService $liquidService;
    protected DataService $dataService;

    public function __construct(
        LiquidTemplateService $liquidService,
        DataService $dataService
    ) {
        $this->liquidService = $liquidService;
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

        // Parse options JSON
        $options = json_decode($block->options, true) ?? [];

        // Get datasources if defined
        $datasources = $options['datasources'] ?? [];
        $data = $this->dataService->getDataSources($datasources);

        // Merge with block data
        $vars = array_merge($data, [
            'block' => [
                'id' => $block->id,
                'key' => $block->key,
                'title' => $block->title,
                'content' => $block->content,
                'options' => $options,
            ],
        ]);

        // Render content with Liquid
        return $this->liquidService->render($block->content, $vars);
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
        // For future implementation with forms
        // Currently just render as regular block
        return $this->render($key);
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
        // For future implementation
        return null;
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
}
