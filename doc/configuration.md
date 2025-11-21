# Configuration Reference

Complete reference for all configuration options in `config/ave-site.php`.

## Configuration File Location

After installation, the configuration file is located at:

```
config/ave-site.php
```

To publish or update the configuration:

```bash
php artisan vendor:publish --tag=ave-site-config --force
```

## Configuration Options

### Routing

```php
'route_home_page' => 'home',
```

The route name for the home page. Used for breadcrumb generation and navigation.

---

### Default Model Settings

```php
'default_model_table' => 'ave_site_pages',
'default_slug_field' => 'slug',
```

| Option | Description | Default |
|--------|-------------|---------|
| `default_model_table` | Default table for data queries when model not specified | `ave_site_pages` |
| `default_slug_field` | Field used for slug lookups | `slug` |

---

### Error Handling

```php
'use_legacy_error_handler' => false,
'not_found_page' => '404',
'error_pages' => [
    403 => 'error-403',
    404 => 'error-404',
    500 => 'error-500',
    503 => 'error-503',
],
```

| Option | Description | Default |
|--------|-------------|---------|
| `use_legacy_error_handler` | If `true`, uses `abort(404)`. If `false`, throws `VoyagerSiteException` | `false` |
| `not_found_page` | Slug of the 404 error page | `404` |
| `error_pages` | Mapping of HTTP error codes to page slugs | Array |

**Error Page Behavior:**

When `use_legacy_error_handler` is `false`, the package throws a custom exception that can be caught by your exception handler to render custom error pages from the database.

---

### Status Field Configuration

```php
'status' => [
    'enabled' => true,
    'field' => 'status',
    'active_value' => [1],
],
```

| Option | Description | Default |
|--------|-------------|---------|
| `enabled` | Enable/disable status field checking | `true` |
| `field` | Name of the status field in models | `status` |
| `active_value` | Array of values considered "active" | `[1]` |

When enabled, queries automatically filter records by status. Only records with status in `active_value` array are returned.

**Disable status checking:**
```php
'status' => [
    'enabled' => false,
],
```

---

### Namespaces

```php
'model_namespace' => 'Monstrex\\AveSite\\Models',
'resource_namespace' => 'Monstrex\\AveSite\\Resources',
```

| Option | Description | Default |
|--------|-------------|---------|
| `model_namespace` | Base namespace for package models | `Monstrex\AveSite\Models` |
| `resource_namespace` | Base namespace for Ave admin resources | `Monstrex\AveSite\Resources` |

---

### Model Class Mapping

```php
'models' => [
    'page' => 'Monstrex\\AveSite\\Models\\Page',
    'block' => 'Monstrex\\AveSite\\Models\\Block',
    'block_region' => 'Monstrex\\AveSite\\Models\\BlockRegion',
    'form' => 'Monstrex\\AveSite\\Models\\Form',
    'setting' => 'Monstrex\\AveSite\\Models\\Setting',
    'localization' => 'Monstrex\\AveSite\\Models\\Localization',
],
```

Maps model aliases to their fully qualified class names. Override to use custom model classes:

```php
'models' => [
    'page' => 'App\\Models\\CustomPage',
    // ...
],
```

---

### Table to Model Mapping

```php
'table_model_map' => [
    'ave_site_pages' => 'page',
    'ave_site_blocks' => 'block',
    'ave_site_block_regions' => 'block_region',
    'ave_site_forms' => 'form',
    'ave_site_settings' => 'setting',
    'ave_site_localizations' => 'localization',
],
```

Maps database table names to model aliases (which are then resolved via `models` config). Used by `DataService` for automatic model resolution.

---

### Resource Class Mapping

```php
'resources' => [
    'page' => 'Monstrex\\AveSite\\Resources\\Page\\Resource',
    'block' => 'Monstrex\\AveSite\\Resources\\Block\\Resource',
    'block_region' => 'Monstrex\\AveSite\\Resources\\BlockRegion\\Resource',
    'form' => 'Monstrex\\AveSite\\Resources\\Form\\Resource',
    'setting' => 'Monstrex\\AveSite\\Resources\\Setting\\Resource',
    'localization' => 'Monstrex\\AveSite\\Resources\\Localization\\Resource',
],
```

Maps model aliases to Ave Admin Panel resource classes. Override to use custom admin resources.

---

### Template Configuration

```php
'template' => 'template',
'template_master' => 'layouts.master',
'template_layout' => 'layouts.main',
'template_page' => 'pages.page',
'template_filters' => null,
```

| Option | Description | Default |
|--------|-------------|---------|
| `template` | Base template namespace/path | `template` |
| `template_master` | Master Blade layout template | `layouts.master` |
| `template_layout` | Main content layout template | `layouts.main` |
| `template_page` | Default page template | `pages.page` |
| `template_filters` | Custom Liquid filters class (FQCN) | `null` |

**Template Hierarchy:**

```
template_master (outermost)
└── template_layout
    └── template_page (innermost, page content)
```

**Custom Liquid Filters:**

Create a class that implements custom filters:

```php
// app/Templates/CustomFilters.php
namespace App\Templates;

use Liquid\Template;

class CustomFilters
{
    public function handle(Template $template, string $content): void
    {
        $template->registerFilter('uppercase', function ($value) {
            return strtoupper($value);
        });
    }
}
```

Configure in `ave-site.php`:

```php
'template_filters' => App\Templates\CustomFilters::class,
```

---

## Environment Variables

The package supports environment variable overrides for sensitive configuration:

```env
# Mail settings (override via Site Settings admin)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=user@example.com
MAIL_PASSWORD=secret
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

# reCAPTCHA (configure in Site Settings)
RECAPTCHA_SECRET_KEY=your-secret-key
```

**Note:** Mail settings configured in Site Settings admin panel will override Laravel's default mail configuration at runtime.

---

## Full Configuration Example

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Routing Configuration
    |--------------------------------------------------------------------------
    */
    'route_home_page' => 'home',

    /*
    |--------------------------------------------------------------------------
    | Default Model Settings
    |--------------------------------------------------------------------------
    */
    'default_model_table' => 'ave_site_pages',
    'default_slug_field' => 'slug',

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    */
    'use_legacy_error_handler' => false,
    'not_found_page' => '404',
    'error_pages' => [
        403 => 'error-403',
        404 => 'error-404',
        500 => 'error-500',
        503 => 'error-503',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Field Configuration
    |--------------------------------------------------------------------------
    */
    'status' => [
        'enabled' => true,
        'field' => 'status',
        'active_value' => [1],
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    */
    'model_namespace' => 'Monstrex\\AveSite\\Models',
    'resource_namespace' => 'Monstrex\\AveSite\\Resources',

    /*
    |--------------------------------------------------------------------------
    | Model Class Mapping
    |--------------------------------------------------------------------------
    */
    'models' => [
        'page' => 'Monstrex\\AveSite\\Models\\Page',
        'block' => 'Monstrex\\AveSite\\Models\\Block',
        'block_region' => 'Monstrex\\AveSite\\Models\\BlockRegion',
        'form' => 'Monstrex\\AveSite\\Models\\Form',
        'setting' => 'Monstrex\\AveSite\\Models\\Setting',
        'localization' => 'Monstrex\\AveSite\\Models\\Localization',
    ],

    /*
    |--------------------------------------------------------------------------
    | Table to Model Mapping
    |--------------------------------------------------------------------------
    */
    'table_model_map' => [
        'ave_site_pages' => 'page',
        'ave_site_blocks' => 'block',
        'ave_site_block_regions' => 'block_region',
        'ave_site_forms' => 'form',
        'ave_site_settings' => 'setting',
        'ave_site_localizations' => 'localization',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Class Mapping
    |--------------------------------------------------------------------------
    */
    'resources' => [
        'page' => 'Monstrex\\AveSite\\Resources\\Page\\Resource',
        'block' => 'Monstrex\\AveSite\\Resources\\Block\\Resource',
        'block_region' => 'Monstrex\\AveSite\\Resources\\BlockRegion\\Resource',
        'form' => 'Monstrex\\AveSite\\Resources\\Form\\Resource',
        'setting' => 'Monstrex\\AveSite\\Resources\\Setting\\Resource',
        'localization' => 'Monstrex\\AveSite\\Resources\\Localization\\Resource',
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Configuration
    |--------------------------------------------------------------------------
    */
    'template' => 'template',
    'template_master' => 'layouts.master',
    'template_layout' => 'layouts.main',
    'template_page' => 'pages.page',
    'template_filters' => null,
];
```

## See Also

- [Installation](installation.md) - Initial setup guide
- [Templating](templating.md) - Template system documentation
- [Settings](settings.md) - Runtime settings management
