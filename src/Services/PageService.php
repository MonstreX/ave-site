<?php

namespace Monstrex\AveSite\Services;

use Monstrex\AveSite\Contracts\PageContract;
use Monstrex\AveSite\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Schema;

/**
 * PageService - Loads and renders pages with full VoyagerPage functionality
 */
class PageService implements PageContract
{
    protected DataService $dataService;
    protected SiteService $siteService;

    // Settings
    protected array $settings = [];

    // MODEL Title if present
    protected ?string $title = null;

    // Loaded MODEL Record
    protected ?Model $content = null;

    // Parents chain
    protected array $parents = [];

    // Header page image
    protected ?string $banner = null;

    // Attached Models Records
    protected $dataSources = null;

    // Children of current page
    protected $children = null;

    // Templates
    protected ?string $template = null;
    protected ?string $templateMaster = null;
    protected ?string $templateLayout = null;
    protected ?string $templatePage = null;

    // Breadcrumbs
    protected array $breadcrumbs = [];

    // SEO Data
    protected ?string $seoTitleTemplate = null;
    protected ?string $seoTitle = null;
    protected ?string $metaDescription = null;
    protected ?string $metaKeywords = null;
    protected int $responseCode = 200;

    public function __construct(
        DataService $dataService,
        SiteService $siteService
    ) {
        $this->dataService = $dataService;
        $this->siteService = $siteService;
    }

    /*
     * Get Title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /*
     * Get Current Page
     */
    public function getPage()
    {
        return $this;
    }

    /*
     * Get Current Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /*
     * Get Data Sources
     */
    public function getDataSources()
    {
        return $this->dataSources;
    }

    /*
     * Get SEO Title
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /*
     * Get SEO Description
     */
    public function getSeoDescription()
    {
        return $this->metaDescription;
    }

    /*
     * Get SEO Keywords
     */
    public function getSeoKeywords()
    {
        return $this->metaKeywords;
    }

    /*
     * Set Title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /*
     * Set Current Page
     */
    public function setPage(Model $content)
    {
        return $this->create($content, $this->siteService->getSettings());
    }

    /*
     * Set Current Content
     */
    public function setContent(Model $content)
    {
        $this->content = $content;
    }

    /*
     * Set Data Sources
     */
    public function setDataSources($dataSources)
    {
        $this->dataSources = $dataSources;
    }

    /*
     * Set SEO Title
     */
    public function setSeoTitle(string $title)
    {
        $this->seoTitle = $title;
    }

    /*
     * Set SEO Description
     */
    public function setSeoDescription(string $description)
    {
        $this->metaDescription = $description;
    }

    /*
     * Set SEO Keywords
     */
    public function setSeoKeywords(string $keywords)
    {
        $this->metaKeywords = $keywords;
    }

    /*
     * Init Templates names
     */
    public function setTemplates(Model $content, array $settings)
    {
        $page_templates = json_decode($content->details ?? '[]', true) ?: [];

        $this->template = $page_templates['template'] ?? $settings['template'];
        $this->templateMaster = $page_templates['template_master'] ?? $settings['template_master'];
        $this->templateLayout = $page_templates['template_layout'] ?? $settings['template_layout'];
        $this->templatePage = $page_templates['template_page'] ?? $settings['template_page'];
    }

    /*
     * Set Master Template
     */
    public function setMasterTemplate(string $template)
    {
        $this->templateMaster = $template;
    }

    /*
     * Set Layout Template
     */
    public function setLayoutTemplate(string $template)
    {
        $this->templateLayout = $template;
    }

    /*
     * Set Page Template
     */
    public function setPageTemplate(string $template)
    {
        $this->templatePage = $template;
    }

    /*
     * Init SEO Data
     */
    public function setSeo(Model $content, array $settings)
    {
        $seoData = method_exists($content, 'seoMeta')
            ? $content->seoMeta()
            : $this->extractSeoData($content->seo ?? []);

        $seo_title = $seoData['seo_title'] ?? '';
        $meta_description = $seoData['meta_description'] ?? '';
        $meta_keywords = $seoData['meta_keywords'] ?? '';

        // TITLE
        $page_title = $content->title ?? '';
        $this->seoTitle = $this->getFirstNotEmpty([
            $seo_title,
            !empty($page_title) ? $page_title : '',
            $settings['seo_title'],
            $settings['site_title']
        ]);

        // Apply template if present
        if (!empty($settings['seo_title_template'])) {
            $title = str_replace('%site_title%', $settings['site_title'], $settings['seo_title_template']);
            $title = str_replace('%seo_title%', $this->seoTitle, $title);
            $this->seoTitle = $title;
        }

        // DESCRIPTION
        $this->metaDescription = $this->getFirstNotEmpty([
            $meta_description,
            $settings['meta_description'],
            $settings['site_description']
        ]);

        // KEYWORDS
        $this->metaKeywords = $this->getFirstNotEmpty([
            $meta_keywords,
            $settings['meta_keywords'],
        ]);

        $this->seoTitle = $this->seoTitle ?? '';
        $this->metaDescription = $this->metaDescription ?? '';
        $this->metaKeywords = $this->metaKeywords ?? '';
    }

    protected function extractSeoData(mixed $raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $raw = $decoded;
            }
        }

        if (!is_array($raw)) {
            return [];
        }

        if (isset($raw[0]) && is_array($raw[0])) {
            $raw = $raw[0];
        }

        return $raw;
    }

    /*
     * Clear and Add the First breadcrumb with Home Page Route
     */
    public function startBreadcrumbs()
    {
        $this->breadcrumbs = [];
        $this->addBreadcrumbs('Home', route(config('ave-site.route_home_page', 'home')));
    }

    /*
     * Add breadcrumb element
     */
    public function addBreadcrumbs($label, $url = '#')
    {
        $this->breadcrumbs[] = [
            'label' => $label,
            'url' => $url
        ];
    }

    /*
     * Returns Breadcrumbs array
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /*
     * Build breadcrumbs from parents
     */
    public function buildBreadcrumbs()
    {
        foreach ($this->parents as $key => $parent) {
            $route_name = 'page'; // Default route for pages
            $this->addBreadcrumbs($parent->title, route($route_name, $parent->slug));
        }
    }

    /*
     * Get parents chain (except current page)
     */
    public function setParents(Model $page = null, $parent_field = 'parent_id')
    {
        if (!$page) {
            $page = $this->content;
        }

        $parents = [];
        $current = $page;
        $table = $this->content?->getTable() ?? config('ave-site.default_model_table', 'ave_site_pages');

        while ($current && !empty($current->{$parent_field})) {
            $parent = $this->dataService->findFirst((int)$current->{$parent_field}, $table, false);

            if (!$parent) {
                break;
            }

            $parents[] = $parent;
            $current = $parent;
        }

        $this->parents = array_reverse($parents);

        return $this->parents;
    }

    /*
     * Get Parents Chain
     */
    public function getParents()
    {
        return $this->parents;
    }

    /*
     * Set Children for the given content. Parent field should consist parent ID
     */
    public function setChildren(string $parent_field)
    {
        if ($this->content && Schema::hasColumn($this->content->getTable(), $parent_field)) {
            $this->children = $this->dataService->findByField(
                $this->content->getTable(),
                $parent_field,
                (int)$this->content->id
            );
        }

        return $this->children;
    }

    /*
     * Get Children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /*
     * Create Page
     */
    public function create(Model $content, array $settings)
    {
        $this->responseCode = 200;

        if (!$content) {
            throw new NotFoundHttpException('Page not found');
        }

        // General settings
        $this->settings = $settings;

        // Model Content
        $this->content = $content;

        $this->title = $this->content->title ?? '';

        // Templates
        $this->setTemplates($content, $settings);

        // SEO
        $this->setSeo($content, $settings);

        // Breadcrumbs
        $this->startBreadcrumbs();

        // Set parents chain
        $this->setParents();

        // Set page header banner
        $this->setBanner($content, $this->parents, $this->siteService->setting('theme.theme_banner_image', ''));

        // Attach Data Sets if present
        $options = json_decode($this->content->details ?? '[]', true) ?: [];
        if ($options && isset($options['datasources'])) {
            $this->dataSources = $this->dataService->getDataSources($options['datasources']);
        }

        return $this;
    }

    /*
     * Returns rendered VIEW using PAGE Vars
     */
    public function view(string $template_layout = null, array $data = null)
    {
        $this->addBreadcrumbs($this->title);

        $viewPath = $template_layout ?? $this->settings['template'] . '.' . $this->templateLayout;

        return response()->view($viewPath, [
            'template' => $this->settings['template'],
            'template_master' => $this->settings['template'] . '.' . $this->settings['template_master'],
            'template_page' => $this->settings['template'] . '.' . $this->templatePage,
            'breadcrumbs' => $this->breadcrumbs,
            'banner' => $this->banner,
            'title' => $this->title,
            'page' => $this->content,
            'parents' => $this->parents,
            'children' => $this->children,
            'data_sources' => $this->dataSources,
            'seo' => [
                'title' => $this->getSeoTitle(),
                'description' => $this->getSeoDescription(),
                'keywords' => $this->getSeoKeywords(),
            ],
            'data' => $data,
        ], $this->responseCode);
    }

    public function setResponseCode(int $code): void
    {
        $this->responseCode = $code;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    /*
     * Set Banner Image (header page image)
     */
    public function setBanner(Model $page, array $parents, string $default_banner)
    {
        $banner = '';

        // Try to get banner from page options
        $options = json_decode($page->details ?? '[]', true) ?: [];
        if (isset($options['banner'])) {
            $banner = $options['banner'];
        }

        // If no banner, try parents
        if (empty($banner)) {
            foreach (array_reverse($parents) as $parent) {
                $parentOptions = json_decode($parent->details ?? '[]', true) ?: [];
                if (isset($parentOptions['banner']) && !empty($parentOptions['banner'])) {
                    $banner = $parentOptions['banner'];
                    break;
                }
            }

            // If we don't have any attached banner in our models we use global settings banner
            if (empty($banner)) {
                $banner = $default_banner;
            }
        }

        $this->banner = $banner;

        return $banner;
    }

    /**
     * Get first not empty value from array
     *
     * @param array $values
     * @return mixed
     */
    protected function getFirstNotEmpty(array $values)
    {
        foreach ($values as $value) {
            if (!empty($value)) {
                return $value;
            }
        }
        return null;
    }
}


