<?php

namespace Monstrex\AveSite\Traits;

use Illuminate\Database\Eloquent\Model;
use Monstrex\AveSite\Facades\{AvePage, AveData, AveSite};

trait PageTrait
{
    /**
     * Init Page Instance
     */
    public function create($alias, string $modelSlug = null, bool $fail = true)
    {
        $page = AveData::findFirst($alias, $modelSlug, $fail);

        if (!$page) {
            return null;
        }

        return AvePage::create($page, AveSite::getSettings());
    }

    /**
     * Find the first record
     */
    public function findFirst($alias, string $modelSlug = null, bool $fail = true)
    {
        return AveData::findFirst($alias, $modelSlug, $fail);
    }

    /**
     * Find multiple records
     */
    public function findByField(string $modelSlug, string $field, $value, string $order = 'order', string $direction = 'ASC')
    {
        return AveData::findByField($modelSlug, $field, $value, $order, $direction);
    }

    /**
     * Render the Page
     */
    public function view(string $templateLayout = null, array $data = null)
    {
        return AvePage::view($templateLayout, $data);
    }

    /**
     * Set Master Template
     */
    public function setMasterTemplate(string $template)
    {
        return AvePage::setMasterTemplate($template);
    }

    /**
     * Set Layout Template
     */
    public function setLayoutTemplate(string $template)
    {
        return AvePage::setLayoutTemplate($template);
    }

    /**
     * Set Page Template
     */
    public function setPageTemplate(string $template)
    {
        return AvePage::setPageTemplate($template);
    }

    /**
     * Set Page Instance
     */
    public function setPage(Model $content)
    {
        return AvePage::setPage($content);
    }

    /**
     * Get Page Instance
     */
    public function getPage()
    {
        return AvePage::getPage();
    }

    /**
     * Set Content
     */
    public function setContent(Model $content)
    {
        return AvePage::setContent($content);
    }

    /**
     * Get Content
     */
    public function getContent()
    {
        return AvePage::getContent();
    }

    /**
     * Add breadcrumb element
     */
    public function addBreadcrumbs(string $label, $url = '#')
    {
        return AvePage::addBreadcrumbs($label, $url);
    }

    /**
     * Build breadcrumbs
     */
    public function buildBreadcrumbs()
    {
        return AvePage::buildBreadcrumbs();
    }

    /**
     * Get parents chain
     */
    public function getParents()
    {
        return AvePage::getParents();
    }

    /**
     * Set Banner Image
     */
    public function setBanner(Model $page, array $parents, string $default_banner)
    {
        return AvePage::setBanner($page, $parents, $default_banner);
    }

    /**
     * Set Children
     */
    public function setChildren(string $parent_field)
    {
        return AvePage::setChildren($parent_field);
    }
}
