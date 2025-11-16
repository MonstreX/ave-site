<?php

namespace Monstrex\AveSite\Services;

use Monstrex\AveSite\Models\Page;
use Illuminate\Support\Facades\View;

/**
 * PageService - Loads and renders pages
 */
class PageService
{
    protected DataService $dataService;
    protected LiquidTemplateService $liquidService;
    protected ?Page $page = null;
    protected array $data = [];

    public function __construct(
        DataService $dataService,
        LiquidTemplateService $liquidService
    ) {
        $this->dataService = $dataService;
        $this->liquidService = $liquidService;
    }

    /**
     * Create/load page by slug or alias
     *
     * @param string|Page $alias Page slug, Page model, or alias
     * @param string|null $modelSlug Not used currently (for compatibility)
     * @return self
     */
    public function create($alias, ?string $modelSlug = null): self
    {
        // If already a Page model
        if ($alias instanceof Page) {
            $this->page = $alias;
            return $this;
        }

        // Find page by slug
        $this->page = Page::published()->where('slug', $alias)->first();

        if (!$this->page) {
            abort(404, "Page not found: {$alias}");
        }

        return $this;
    }

    /**
     * Render page view
     *
     * @param string|null $customView Custom Blade view path
     * @param array $additionalData Additional data to pass to view
     * @return string Rendered HTML
     */
    public function view(?string $customView = null, ?array $additionalData = []): string
    {
        if (!$this->page) {
            abort(500, 'Page not loaded. Call create() first.');
        }

        // Parse options
        $options = json_decode($this->page->options, true) ?? [];

        // Load datasources
        $datasources = $options['datasources'] ?? [];
        $data = $this->dataService->getDataSources($datasources);

        // Render content with Liquid if it contains liquid syntax
        $content = $this->page->content;
        if ($this->hasLiquidSyntax($content)) {
            $content = $this->liquidService->render($content, $data);
        }

        // Prepare view data
        $viewData = array_merge($data, $additionalData, [
            'page' => $this->page,
            'content' => $content,
        ]);

        // Determine view
        $view = $customView ?? $options['view'] ?? 'ave-site::pages.default';

        // Check if view exists
        if (!View::exists($view)) {
            // Fallback to default
            $view = 'ave-site::pages.default';
        }

        return view($view, $viewData)->render();
    }

    /**
     * Get loaded page
     *
     * @return Page|null
     */
    public function getPage(): ?Page
    {
        return $this->page;
    }

    /**
     * Check if content has Liquid template syntax
     *
     * @param string $content
     * @return bool
     */
    protected function hasLiquidSyntax(string $content): bool
    {
        return str_contains($content, '{{') || str_contains($content, '{%');
    }
}
