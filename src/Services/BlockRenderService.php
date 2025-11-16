<?php

namespace Monstrex\AveSite\Services;

use Monstrex\AveSite\Models\Block;
use Monstrex\AveSite\Models\BlockRegion;

/**
 * BlockRenderService - Renders blocks and block regions with Liquid templates
 */
class BlockRenderService
{
    protected LiquidTemplateService $liquidService;
    protected DataSourceService $dataSourceService;

    public function __construct(
        LiquidTemplateService $liquidService,
        DataSourceService $dataSourceService
    ) {
        $this->liquidService = $liquidService;
        $this->dataSourceService = $dataSourceService;
    }

    /**
     * Render a single block by key
     *
     * @param string|int $key Block key or ID
     * @return string Rendered HTML
     */
    public function render($key): string
    {
        $block = is_numeric($key)
            ? Block::active()->find($key)
            : Block::active()->where('key', $key)->first();

        if (!$block) {
            return '';
        }

        return $this->renderBlock($block);
    }

    /**
     * Render all blocks in a region
     *
     * @param string $regionKey Region key
     * @param string|null $path Current URL path for filtering blocks
     * @return string Rendered HTML of all blocks
     */
    public function renderRegion(string $regionKey, ?string $path = null): string
    {
        $region = BlockRegion::where('key', $regionKey)->first();

        if (!$region) {
            return '';
        }

        $blocks = Block::active()
            ->inRegion($regionKey)
            ->ordered()
            ->get();

        if ($blocks->isEmpty()) {
            return '';
        }

        // Filter blocks by URL rules if path provided
        if ($path !== null) {
            $blocks = $blocks->filter(function ($block) use ($path) {
                return $this->matchesUrlRules($block, $path);
            });
        }

        $output = '';
        foreach ($blocks as $block) {
            $output .= $this->renderBlock($block);
        }

        return $output;
    }

    /**
     * Render a single block
     *
     * @param Block $block
     * @return string
     */
    protected function renderBlock(Block $block): string
    {
        // Parse options JSON
        $options = json_decode($block->options, true) ?? [];

        // Get datasources if defined
        $datasources = $options['datasources'] ?? [];
        $data = $this->dataSourceService->getDataSources($datasources);

        // Merge with block data
        $vars = array_merge($data, [
            'block' => [
                'id' => $block->id,
                'key' => $block->key,
                'title' => $block->title,
                'media' => $block->media,
                'options' => $options,
            ],
        ]);

        // Render content with Liquid
        return $this->liquidService->render($block->content, $vars);
    }

    /**
     * Check if block matches URL rules
     *
     * @param Block $block
     * @param string $path Current URL path
     * @return bool
     */
    protected function matchesUrlRules(Block $block, string $path): bool
    {
        // If no rules defined, show on all pages
        if (!$block->rules) {
            return true;
        }

        $urls = $block->urls ? explode("\n", trim($block->urls)) : [];

        if (empty($urls)) {
            return true;
        }

        // Normalize path
        $path = '/' . trim($path, '/');

        foreach ($urls as $pattern) {
            $pattern = trim($pattern);

            if (empty($pattern)) {
                continue;
            }

            // Exact match
            if ($pattern === $path) {
                return $this->shouldShow($block->rules);
            }

            // Wildcard match
            if (str_contains($pattern, '*')) {
                $regex = '#^' . str_replace(['/', '*'], ['\/', '.*'], $pattern) . '$#';
                if (preg_match($regex, $path)) {
                    return $this->shouldShow($block->rules);
                }
            }
        }

        return !$this->shouldShow($block->rules);
    }

    /**
     * Determine if block should be shown based on rules
     *
     * @param int $rules 1 = show on listed URLs, 0 = hide on listed URLs
     * @return bool
     */
    protected function shouldShow(int $rules): bool
    {
        return $rules === 1;
    }
}
