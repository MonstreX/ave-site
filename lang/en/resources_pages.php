<?php

return [
    'label' => 'Pages',
    'singular' => 'Page',
    'no_parent' => '(No parent)',
    'columns' => [
        'id' => 'ID',
        'title' => 'Title',
        'slug' => 'URL',
        'status' => 'Published',
    ],
    'tabs' => [
        'main' => 'General',
        'media' => 'Media & Images',
        'seo' => 'SEO',
        'options' => 'Options',
        'additional' => 'Additional',
    ],
    'fields' => [
        'title' => 'Title',
        'slug' => 'URL (slug)',
        'parent' => 'Parent page',
        'status' => 'Published',
        'menu' => 'Show in menu',
        'content' => 'Content',
        'image' => 'Main Image',
        'images' => 'Gallery Images',
        'details' => 'Options (JSON)',
        'seo_title' => 'SEO Title',
        'seo_keywords' => 'SEO Keywords',
        'seo_description' => 'SEO Description',
        'order' => 'Order',
        'published_at' => 'Publication date',
    ],
];
