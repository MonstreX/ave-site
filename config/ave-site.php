<?php

return [
    // Database
    'table_prefix' => 'site_',

    // Pages
    'route_home_page' => 'home',
    'default_model_table' => 'pages',
    'default_slug_field' => 'slug',

    // Templates
    'template' => 'site',
    'template_master' => 'layouts.master',
    'template_layout' => 'layouts.main',
    'template_page' => 'pages.page',
    'template_filters' => null, // Class for custom Liquid filters

    // SEO
    'seo_title_template' => '%page_title% | %site_title%',

    // Block Groups (Regions)
    'block_groups' => [
        'header' => 'Header',
        'footer' => 'Footer',
        'sidebar' => 'Sidebar',
        'content' => 'Content',
    ],

    // Navigation (for Ave Admin)
    'navigation' => [
        'group' => 'Site CMS',
        'sort' => 100,
        'icon' => 'globe-alt',
    ],

    // Image Processing
    'image_quality' => 85,
    'image_cache_dir' => 'cached',

    // Forms
    'form_route_name' => 'ave-site.form.submit',
    'recaptcha_enabled' => false,
    'recaptcha_site_key' => env('RECAPTCHA_SITE_KEY'),
    'recaptcha_secret_key' => env('RECAPTCHA_SECRET_KEY'),
];
