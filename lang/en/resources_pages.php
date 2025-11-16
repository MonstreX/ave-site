<?php

return [
    'label' => 'Pages',
    'singular' => 'Page',
    'no_parent' => '(No parent)',
    'columns' => [
        'title' => 'Title',
        'slug' => 'URL',
        'status' => 'Published',
        'created_at' => 'Created',
    ],
    'fields' => [
        'title' => 'Title',
        'slug' => 'URL (slug)',
        'parent' => 'Parent page',
        'status' => 'Published',
        'content' => 'Content',
        'options' => 'Options (JSON)',
        'seo_title' => 'SEO Title',
        'seo_keywords' => 'SEO Keywords',
        'seo_description' => 'SEO Description',
    ],
];
