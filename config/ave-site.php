<?php

$modelNamespace = 'Monstrex\\AveSite\\Models';
$resourceNamespace = 'Monstrex\\AveSite\\Resources';

return [

    /*
     * Route for Home Page
     */
    'route_home_page' => 'home',

    /*
     * Default model table name to find records
     */
    'default_model_table' => 'ave_site_pages',

    /*
     * Default slug field name
     */
    'default_slug_field' => 'slug',

    /*
     * If false will use ave-site 404 error handler
     */
    'use_legacy_error_handler' => false,

    /*
     * Optional slug of the page that will be rendered for 404 responses.
     */
    'not_found_page' => '404',

    /*
     * Mapping for error pages (slug or ID). Falls back to not_found_page.
     */
    'error_pages' => [
        403 => 'error-403',
        404 => '404',
    ],

    /*
     * Status control for published records.
     */
    'status' => [
        'enabled' => true,
        'field' => 'status',
        'active_value' => [1],
    ],

    /*
     * Namespace for models. Override when publishing models into application namespace.
     */
    'model_namespace' => $modelNamespace,

    /*
     * Namespace for Ave resources. Override when resources are published into the host app.
     */
    'resource_namespace' => $resourceNamespace,

    /*
     * Registered model classes keyed by logical name.
     */
    'models' => [
        'page' => $modelNamespace.'\\Page',
        'block' => $modelNamespace.'\\Block',
        'block_region' => $modelNamespace.'\\BlockRegion',
        'form' => $modelNamespace.'\\Form',
        'localization' => $modelNamespace.'\\Localization',
        'setting' => $modelNamespace.'\\Setting',
    ],

    /*
     * Maps database tables to logical model keys.
     */
    'table_model_map' => [
        'ave_site_pages' => 'page',
        'ave_site_blocks' => 'block',
        'ave_site_block_regions' => 'block_region',
        'ave_site_forms' => 'form',
        'ave_site_localizations' => 'localization',
        'ave_site_settings' => 'setting',
    ],

    /*
     * Resource classes keyed by logical name.
     */
    'resources' => [
        'page' => $resourceNamespace.'\\Page\\Resource',
        'block' => $resourceNamespace.'\\Block\\Resource',
        'block_region' => $resourceNamespace.'\\BlockRegion\\Resource',
        'form' => $resourceNamespace.'\\Form\\Resource',
        'localization' => $resourceNamespace.'\\Localization\\Resource',
        'setting' => $resourceNamespace.'\\Setting\\Resource',
    ],

    /*
     *  Name of the Template
     */
    'template' => 'template',

    /*
     *  Root Extendable Template
     */
    'template_master' => 'layouts.master',

    /*
     *  Main Layout Template
     */
    'template_layout' => 'layouts.main',

    /*
     *  Page Template
     */
    'template_page'   => 'pages.page',

    /*
     *  Template custom filters class
     */
    'template_filters' => null,
];
