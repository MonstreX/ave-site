# API Reference

Complete API reference for all public interfaces in Ave Site package.

## Table of Contents

- [Facades](#facades)
- [Helper Functions](#helper-functions)
- [Models](#models)
- [Services](#services)
- [Blade Directives](#blade-directives)
- [Liquid Filters](#liquid-filters)
- [Shortcodes](#shortcodes)
- [Routes](#routes)

---

## Facades

### AveSite

```php
use Monstrex\AveSite\Facades\AveSite;

AveSite::setting(string $key, $default = null): mixed
AveSite::getSettings(): array
AveSite::storeMediaFile($model, string $field): void
AveSite::currentPath(): string
```

### AvePage

```php
use Monstrex\AveSite\Facades\AvePage;

// Initialization
AvePage::create($content, array $settings = []): PageService
AvePage::setPage($content): PageService
AvePage::setContent($model): PageService

// Templates
AvePage::setTemplates($content, array $settings): PageService
AvePage::setMasterTemplate(string $template): PageService
AvePage::setLayoutTemplate(string $template): PageService
AvePage::setPageTemplate(string $template): PageService

// SEO
AvePage::setSeo($content, array $settings): PageService
AvePage::setSeoTitle(string $title): PageService
AvePage::setSeoDescription(string $description): PageService
AvePage::setSeoKeywords(string $keywords): PageService

// Breadcrumbs
AvePage::startBreadcrumbs(): PageService
AvePage::addBreadcrumbs(string $label, ?string $url = null): PageService
AvePage::buildBreadcrumbs(): PageService

// Hierarchy
AvePage::setParents($page, string $parent_field = 'parent_id'): PageService
AvePage::setChildren(string $parent_field = 'parent_id'): PageService
AvePage::setBanner($page, array $parents, ?string $default_banner): PageService

// Response
AvePage::setResponseCode(int $code): PageService
AvePage::setDataSources(array $data): PageService

// Getters
AvePage::getTitle(): string
AvePage::getPage(): mixed
AvePage::getContent(): mixed
AvePage::getDataSources(): array
AvePage::getSeoTitle(): string
AvePage::getSeoDescription(): string
AvePage::getSeoKeywords(): string
AvePage::getBreadcrumbs(): array
AvePage::getParents(): array
AvePage::getChildren(): array
AvePage::getResponseCode(): int

// Rendering
AvePage::view(?string $template_layout = null, array $data = []): Response
```

### AveBlock

```php
use Monstrex\AveSite\Facades\AveBlock;

// Rendering
AveBlock::renderRegion(string $region_name, ?string $path = null): string
AveBlock::render(string|int $key): string
AveBlock::renderBlock($block): string
AveBlock::renderForm(string $key, ?string $subject = null, ?string $suffix = null): string
AveBlock::renderLayout($layout, $page): string

// Lookups
AveBlock::getByID(int $id): ?Block
AveBlock::getByKey(string $key): ?Block
AveBlock::getByTitle(string $title): ?Block
AveBlock::getFormByKey(string $key): ?Block
AveBlock::getBlockField($block, string $field): mixed
```

### AveData

```php
use Monstrex\AveSite\Facades\AveData;

// Finding
AveData::findFirst($alias, ?string $modelSlug = null, bool $fail = true): mixed
AveData::where(string $field, string $value, ?string $modelSlug = null, bool $fail = true): mixed
AveData::findByField(string $modelSlug, string $field, $value, string $order = 'order', string $direction = 'ASC'): Collection

// DataSources
AveData::getDataSources(object $datasources): array

// Menu
AveData::getMenu(?string $modelSlug = null, ?array $parent = null): ?array

// Images
AveData::getImageOrCreate(string $image_url, ?int $width = null, ?int $height = null, ?string $format = null, ?int $quality = null): string
```

---

## Helper Functions

### Settings

```php
site_setting(string $key, $default = null): mixed
site_settings_group(string $key): array
```

### Content Rendering

```php
render_block(string $key): string
render_region(string $key, ?string $path = null): string
render_form(string $key, ?string $subject = null, ?string $suffix = null): string
render_layout($layout, $page): string
get_block_field($block, string $field): mixed
```

### Image Processing

```php
get_image_or_create(string $image_path, ?int $width = null, ?int $height = null, ?string $format = null, ?int $quality = null): string
get_image_webp(string $image_path): string
get_image_or_create_webp(string $image_path, ?int $width = null, ?int $height = null, ?int $quality = null): string
```

### Data Utilities

```php
flat_to_tree(array $array, $parent_id = null): array
get_first_not_empty(array $values): mixed
seo_meta(mixed $model): array
translit_cyrillic(string $string): string
```

### File Handling

```php
get_file($file_path): string
store_post_files(Request $request, string $slug, string $field, string $public = 'public'): string
generate_filename($file, string $path): string
```

---

## Models

### Page

```php
use Monstrex\AveSite\Models\Page;

// Properties
$page->id;
$page->parent_id;
$page->status;
$page->menu;
$page->title;
$page->slug;
$page->content;
$page->image;
$page->images;
$page->order;
$page->seo;          // array
$page->details;      // array

// Relationships
$page->parent;       // BelongsTo Page
$page->children;     // HasMany Page

// Scopes
Page::published()
Page::roots()

// Methods (HasSeoMeta)
$page->seoMeta(): array
$page->seo_meta;     // Attribute accessor
```

### Block

```php
use Monstrex\AveSite\Models\Block;

// Properties
$block->id;
$block->title;
$block->key;
$block->region_id;
$block->order;
$block->status;
$block->urls;
$block->rules;       // 0=EXCEPT, 1=ONLY
$block->content;
$block->images;      // array
$block->elements;    // array
$block->details;     // array

// Relationships
$block->region;      // BelongsTo BlockRegion

// Scopes
Block::active()
Block::inRegion(string $regionKey)
Block::ordered()

// Methods
$block->isForm(): bool
```

### BlockRegion

```php
use Monstrex\AveSite\Models\BlockRegion;

// Properties
$region->id;
$region->key;
$region->name;

// Relationships
$region->blocks;     // HasMany Block
```

### Form

```php
use Monstrex\AveSite\Models\Form;

// Properties
$form->id;
$form->status;
$form->order;
$form->title;
$form->key;
$form->content;
$form->details;      // array (validator, messages, to_address)

// Scopes
Form::active()
```

### Setting

```php
use Monstrex\AveSite\Models\Setting;

// Properties
$setting->id;
$setting->key;
$setting->group;
$setting->title;
$setting->order;
$setting->fields;    // array

// Scopes
Setting::byKey(string $key)
Setting::byGroup(string $group)

// Methods
$setting->getFieldsArray(): array
$setting->getMediaItem(string $collection): ?Media
$setting->clearMediaCollection(string $collection): void
$setting->resolveMediaValue($value): ?string
```

### Localization

```php
use Monstrex\AveSite\Models\Localization;

// Properties
$localization->id;
$localization->key;
$localization->en;
$localization->ru;
// ... additional locale columns

// Static Methods
Localization::loadLocalizations(): void
Localization::getLocalizedLines(string $locale): array

// Scopes
Localization::byKey(string $key)
Localization::byLocale(string $locale)
```

---

## Services

### SiteService

```php
use Monstrex\AveSite\Services\SiteService;

$service = app(SiteService::class);

$service->setting(string $key, $default = null): mixed
$service->getSettings(): array
$service->storeMediaFile($model, string $field): void
$service->currentPath(): string
```

### PageService

```php
use Monstrex\AveSite\Services\PageService;

$service = app(PageService::class);

// All AvePage facade methods are available
$service->create($content, array $settings = []): self
$service->view(?string $template_layout = null, array $data = []): Response
// ... etc
```

### BlockService

```php
use Monstrex\AveSite\Services\BlockService;

$service = app(BlockService::class);

// All AveBlock facade methods are available
$service->renderRegion(string $region_name, ?string $path = null): string
$service->render(string|int $key): string
// ... etc
```

### DataService

```php
use Monstrex\AveSite\Services\DataService;

$service = app(DataService::class);

// All AveData facade methods are available
$service->findFirst($alias, ?string $modelSlug = null, bool $fail = true): mixed
$service->getDataSources(object $datasources): array
// ... etc
```

### SettingsService

```php
use Monstrex\AveSite\Services\SettingsService;

$service = app(SettingsService::class);

$service->get(string $key, $default = null): mixed
$service->getGroup(string $group): array
```

### LocalizationService

```php
use Monstrex\AveSite\Services\LocalizationService;

$service = app(LocalizationService::class);

$service->loadLocalizations(): void
$service->get(string $key, ?string $locale = null): ?string
$service->set(string $key, string $value, ?string $locale = null): void
$service->getByLocale(string $locale): array
$service->has(string $key, ?string $locale = null): bool
$service->delete(string $key): void
$service->getAllKeys(): array
```

### ModelResolver

```php
use Monstrex\AveSite\Services\ModelResolver;

$resolver = app(ModelResolver::class);

$resolver->resolveModel($model, ?string $resourceClass = null): array
```

---

## Blade Directives

```blade
@renderBlock('block-key')

@renderRegion('region-key')

@renderForm('form-key')
@renderForm('form-key', 'Subject')
@renderForm('form-key', 'Subject', 'Suffix')
```

---

## Liquid Filters

| Filter | Usage | Description |
|--------|-------|-------------|
| `site_setting` | `{{ 'key' \| site_setting }}` | Get site setting |
| `menu` | `{{ 'name' \| menu }}` | Render menu |
| `block` | `{{ 'key' \| block }}` | Render block |
| `form` | `{{ 'key' \| form }}` | Render form |
| `url` | `{{ '/path' \| url }}` | Generate URL |
| `route` | `{{ 'name' \| route }}` | Generate route URL |
| `webp` | `{{ image \| webp }}` | Convert to WebP |
| `crop` | `{{ image \| crop: w, h }}` | Resize/crop image |
| `lang` | `{{ 'key' \| lang }}` | Translate |
| `dump` | `{{ var \| dump }}` | Debug output |

---

## Shortcodes

```html
[block name="key"]

[form name="key"]
[form name="key" subject="Subject" suffix="Suffix"]

[div class="classname"]content[/div]

[image url="/path/to/image.jpg"]
[image url="/path/to/image.jpg" crop="400,300" format="webp"]
[image url="/path/to/image.jpg" width="800" height="600" class="responsive"]
[image url="/path/to/image.jpg" lightbox="true" lightbox_class="gallery"]
```

---

## Routes

### Admin Routes

| Method | URL | Controller | Action |
|--------|-----|------------|--------|
| GET | `/admin/site-settings/{key}/edit` | SettingsController | edit |
| POST | `/admin/site-settings/{key}` | SettingsController | update |
| POST | `/admin/site-settings/test-mail` | SettingsController | testMail |

### Frontend Routes

| Method | URL | Controller | Action |
|--------|-----|------------|--------|
| POST | `/api/send-form` | FormController | send |

---

## Configuration Keys

```php
// config/ave-site.php

'route_home_page'           // string: Home page route name
'default_model_table'       // string: Default table for queries
'default_slug_field'        // string: Slug field name
'use_legacy_error_handler'  // bool: Use abort() vs exception
'not_found_page'            // string: 404 page slug
'error_pages'               // array: Error code to slug mapping
'status.enabled'            // bool: Check status field
'status.field'              // string: Status field name
'status.active_value'       // array: Active status values
'model_namespace'           // string: Model namespace
'resource_namespace'        // string: Resource namespace
'models'                    // array: Model class mapping
'table_model_map'           // array: Table to model mapping
'resources'                 // array: Resource class mapping
'template'                  // string: Template namespace
'template_master'           // string: Master layout
'template_layout'           // string: Content layout
'template_page'             // string: Page template
'template_filters'          // string|null: Custom filters class
```

---

## Events

The package does not dispatch custom events. Use Laravel model events for hooks:

```php
// In AppServiceProvider
Page::saved(function ($page) {
    // Handle page saved
});

Block::created(function ($block) {
    // Handle block created
});

Setting::updated(function ($setting) {
    // Handle setting updated
    Cache::forget('site_settings');
});
```

---

## Exceptions

```php
use Monstrex\AveSite\Exceptions\VoyagerSiteException;

// Thrown when page not found (if use_legacy_error_handler = false)
throw new VoyagerSiteException(__('Page not found'), 404);
```

---

## Artisan Commands

```bash
# Install package
php artisan ave-site:install

# Force reinstall
php artisan ave-site:install --force

# Publish config
php artisan vendor:publish --tag=ave-site-config

# Publish views
php artisan vendor:publish --tag=ave-site-views
```

---

## DataSource Configuration

```json
{
    "model": "ModelName",           // Required: Model class name
    "where": {                      // Optional: Filter conditions
        "field": "value",
        "status": 1
    },
    "order": {                      // Optional: Sorting
        "field": "created_at",
        "direction": "DESC"
    },
    "limit": 10,                    // Optional: Max records
    "with": ["relation1"],          // Optional: Eager load
    "random": true                  // Optional: Randomize
}
```

---

## Media Array Structure

When block data is resolved, media collections have this structure:

```php
[
    'url' => '/storage/path/to/file.jpg',
    'fullUrl' => 'https://example.com/storage/path/to/file.jpg',
    'path' => '/var/www/storage/app/public/path/to/file.jpg',
    'fileName' => 'file.jpg',
    'size' => 102400,
    'mime' => 'image/jpeg',
    'order' => 1,
    'props' => (object)[
        'alt' => 'Alternative text',
        'title' => 'Image title',
        // ... custom properties
    ]
]
```

---

## SEO Array Structure

```php
[
    'seo_title' => 'Page Title for Search Engines',
    'meta_description' => 'Description for search results',
    'meta_keywords' => 'keyword1, keyword2, keyword3'
]
```

---

## Breadcrumb Array Structure

```php
[
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Category', 'url' => '/category'],
    ['label' => 'Current Page', 'url' => null]
]
```
