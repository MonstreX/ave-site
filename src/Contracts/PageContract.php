<?php

namespace Monstrex\AveSite\Contracts;

use Illuminate\Database\Eloquent\Model;

interface PageContract
{
    public function getTitle();
    public function getPage();
    public function getContent();
    public function getDataSources();
    public function getSeoTitle();
    public function getSeoDescription();
    public function getSeoKeywords();

    public function setTitle(string $title);
    public function setPage(Model $content);
    public function setContent(Model $content);
    public function setDataSources($dataSources);
    public function setSeoTitle(string $title);
    public function setSeoDescription(string $description);
    public function setSeoKeywords(string $keywords);

    public function setTemplates(Model $content, array $settings);
    public function setMasterTemplate(string $template);
    public function setLayoutTemplate(string $template);
    public function setPageTemplate(string $template);

    public function setSeo(Model $content, array $settings);

    public function startBreadcrumbs();
    public function addBreadcrumbs($label, $url = '#');
    public function getBreadcrumbs();
    public function buildBreadcrumbs();

    public function setParents(Model $page = null, $parent_field = 'parent_id');
    public function getParents();

    public function setChildren(string $parent_field);
    public function getChildren();

    public function create(Model $content, array $settings);
    public function view(string $template_layout = null, array $data = null);

    public function setBanner(Model $page, array $parents, string $default_banner);
}
