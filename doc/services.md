# Services Reference

Complete documentation for all service classes in the Ave Site package.

## Overview

The package follows a service-oriented architecture with 7 core services:

| Service | Purpose | Facade |
|---------|---------|--------|
| [PageService](#pageservice) | Page lifecycle management | `AvePage` |
| [BlockService](#blockservice) | Block and form rendering | `AveBlock` |
| [DataService](#dataservice) | Data fetching and images | `AveData` |
| [SiteService](#siteservice) | Site settings management | `AveSite` |
| [SettingsService](#settingsservice) | Alternative settings access | - |
| [LocalizationService](#localizationservice) | Translation management | - |
| [ModelResolver](#modelresolver) | Model-to-array conversion | - |

---

## PageService

**Namespace:** `Monstrex\AveSite\Services\PageService`
**Contract:** `Monstrex\AveSite\Contracts\PageContract`
**Facade:** `AvePage`

Manages the complete page lifecycle including loading, template configuration, SEO setup, and rendering.

### Internal State

The service maintains page state across method calls:

```php
protected array $settings = [];
protected $content;           // Page model instance
protected string $title = '';
protected array $parents = [];
protected array $dataSources = [];
protected array $children = [];
protected string $banner = '';
protected array $breadcrumbs = [];
protected string $seoTitle = '';
protected string $metaDescription = '';
protected string $metaKeywords = '';
protected string $template = '';
protected string $templateMaster = '';
protected string $templateLayout = '';
protected string $templatePage = '';
protected int $responseCode = 200;
```

### Methods

#### Getters

```php
public function getTitle(): string
public function getPage()                    // Returns page model
public function getContent()                 // Alias for getPage()
public function getDataSources(): array
public function getSeoTitle(): string
public function getSeoDescription(): string
public function getSeoKeywords(): string
public function getBreadcrumbs(): array
public function getParents(): array
public function getChildren(): array
public function getResponseCode(): int
```

#### Setters

```php
public function setTitle(string $title): self
public function setPage($content): self
public function setContent($model): self     // Alias for setPage()
public function setDataSources(array $data): self
public function setSeoTitle(string $title): self
public function setSeoDescription(string $description): self
public function setSeoKeywords(string $keywords): self
public function setResponseCode(int $code): self
```

#### Template Configuration

```php
public function setTemplates($content, array $settings): self
public function setMasterTemplate(string $template): self
public function setLayoutTemplate(string $template): self
public function setPageTemplate(string $template): self
```

**Template Resolution Logic:**

Templates are resolved from multiple sources with fallback:
1. Page model `details.template` field
2. Settings from `SiteService::getSettings()`
3. Configuration defaults

#### SEO Configuration

```php
public function setSeo($content, array $settings): self
```

**SEO Resolution Hierarchy:**
1. Model's `seo.seo_title` field
2. Model's `title` field
3. Settings `seo_title`
4. Settings `site_title`

#### Breadcrumbs

```php
public function startBreadcrumbs(): self
public function addBreadcrumbs(string $label, ?string $url = null): self
public function buildBreadcrumbs(): self
```

**Example:**
```php
$pageService->startBreadcrumbs()
    ->addBreadcrumbs('Category', '/category')
    ->addBreadcrumbs('Current Page');
```

#### Parent/Child Management

```php
public function setParents($page, string $parent_field = 'parent_id'): self
public function setChildren(string $parent_field = 'parent_id'): self
```

Builds parent chain by traversing `parent_id` relationships upward.

#### Banner Image

```php
public function setBanner($page, array $parents, ?string $default_banner): self
```

**Banner Resolution:**
1. Current page's `image` field
2. First parent with `image` field
3. Default banner from settings

#### Main Initialization

```php
public function create($content, array $settings = []): self
```

Performs complete page setup:
1. Sets page content
2. Configures templates
3. Sets up SEO
4. Builds parent chain
5. Resolves banner
6. Loads DataSources

#### View Rendering

```php
public function view(string $template_layout = null, array $data = []): Response
```

Returns Laravel Response with all page data:
- `page` - Page model
- `title` - Page title
- `banner` - Banner image URL
- `breadcrumbs` - Breadcrumb array
- `parents` - Parent pages
- `children` - Child pages
- `data` - DataSources data
- Plus any custom `$data` passed

### Usage Example

```php
use Monstrex\AveSite\Facades\AvePage;

// In controller
public function show($slug)
{
    $page = Page::where('slug', $slug)->firstOrFail();
    $settings = AveSite::getSettings();

    return AvePage::create($page, $settings)
        ->startBreadcrumbs()
        ->buildBreadcrumbs()
        ->view();
}
```

---

## BlockService

**Namespace:** `Monstrex\AveSite\Services\BlockService`
**Contract:** `Monstrex\AveSite\Contracts\BlockContract`
**Facade:** `AveBlock`

Handles rendering of blocks, regions, and forms using Liquid templates.

### Constructor Dependencies

```php
public function __construct(
    DataService $dataService,
    ModelResolver $modelResolver
)
```

### Methods

#### Region Rendering

```php
public function renderRegion(string $region_name, ?string $path = null): string
```

Renders all active blocks in a region with visibility filtering.

**Parameters:**
- `$region_name` - Region key (e.g., 'header', 'footer')
- `$path` - Optional current path override (defaults to request path)

**Visibility Logic:**
- Gets current URL path
- For each block in region:
  - If `rules=0` (EXCEPT): Show if URL NOT in `urls` list
  - If `rules=1` (ONLY): Show if URL IS in `urls` list
- `<front>` in URLs matches homepage

#### Single Block Rendering

```php
public function render(string|int $key): string
```

Renders a block by key, ID, or title (tried in that order).

```php
public function renderBlock($block): string
```

Internal method that processes Liquid template with:
- Block data (`this`, `block` variables)
- DataSources (`data` variable)
- Media/FieldSet resolution via ModelResolver

#### Form Rendering

```php
public function renderForm(string $key, ?string $subject = null, ?string $suffix = null): string
```

Renders a form block with additional Blade variables:
- `_form_key` - Form identifier
- `_subject` - Email subject
- `_suffix` - Email subject suffix
- `errors` - Validation errors from session

#### Layout Rendering

```php
public function renderLayout($layout, $page): string
```

Renders layout from JSON configuration.

#### Block Lookups

```php
public function getByID(int $id): ?Block
public function getByKey(string $key): ?Block
public function getByTitle(string $title): ?Block
public function getFormByKey(string $key): ?Block
```

#### Field Access

```php
public function getBlockField($block, string $field)
```

Extracts specific field value from block's resolved data.

### Usage Examples

```php
use Monstrex\AveSite\Facades\AveBlock;

// Render entire region
$header = AveBlock::renderRegion('header');

// Render single block
$footer = AveBlock::render('footer-block');

// Render form
$contactForm = AveBlock::renderForm('contact-form', 'Contact Request');

// Get block data
$block = AveBlock::getByKey('testimonials');
$testimonials = AveBlock::getBlockField($block, 'items');
```

---

## DataService

**Namespace:** `Monstrex\AveSite\Services\DataService`
**Contract:** `Monstrex\AveSite\Contracts\DataContract`
**Facade:** `AveData`

Provides data fetching, model resolution, and image processing capabilities.

### Constructor Dependencies

```php
public function __construct(ModelResolver $modelResolver)
```

### Methods

#### Record Finding

```php
public function findFirst($alias, ?string $modelSlug = null, bool $fail = true)
```

Find record by slug or ID.

**Parameters:**
- `$alias` - Slug string or numeric ID
- `$modelSlug` - Table name (defaults to `ave_site_pages`)
- `$fail` - Throw 404 if not found

```php
public function where(string $field, string $value, ?string $modelSlug = null, bool $fail = true)
```

Find first record by field value.

```php
public function findByField(string $modelSlug, string $field, $value, string $order = 'order', string $direction = 'ASC')
```

Find multiple records with status filtering and ordering.

#### DataSources

```php
public function getDataSources(object $datasources): array
```

Load multiple DataSources from configuration object.

**DataSource Configuration:**

```json
{
    "articles": {
        "model": "Article",
        "where": {"status": 1, "featured": true},
        "order": {"field": "created_at", "direction": "DESC"},
        "with": ["author", "category"],
        "limit": 10,
        "random": false
    },
    "testimonials": {
        "model": "Testimonial",
        "limit": 5,
        "random": true
    }
}
```

**Returns:**
```php
[
    'articles' => [...],      // Array of Article records
    'testimonials' => [...]   // Array of Testimonial records
]
```

#### Menu Generation

```php
public function getMenu(?string $modelSlug = null, ?array $parent = null): ?array
```

Get hierarchical menu from tree-structured model.

**Parameters:**
- `$modelSlug` - Table name (must have `parent_id` column)
- `$parent` - Filter by parent: `['field' => 'slug', 'value' => 'about']`

**Returns tree structure:**
```php
[
    [
        'id' => 1,
        'title' => 'About',
        'slug' => 'about',
        'children' => [
            ['id' => 2, 'title' => 'Team', 'slug' => 'about/team', 'children' => []],
            ['id' => 3, 'title' => 'Contact', 'slug' => 'about/contact', 'children' => []],
        ]
    ],
    // ...
]
```

#### Image Processing

```php
public function getImageOrCreate(
    string $image_url,
    ?int $width = null,
    ?int $height = null,
    ?string $format = null,
    ?int $quality = null
): string
```

Resize, crop, or convert images with caching.

**Parameters:**
- `$image_url` - Source image path (relative or absolute)
- `$width` - Target width in pixels
- `$height` - Target height in pixels
- `$format` - Output format: 'webp', 'png', 'jpg'
- `$quality` - Image quality (1-100, default 80)

**Behavior:**
- If only width OR height: Maintains aspect ratio
- If both width AND height: Crops to fit (fit mode)
- Creates thumbnails in `thumbnails/` subdirectory
- Returns cached image if already generated

### Usage Examples

```php
use Monstrex\AveSite\Facades\AveData;

// Find page by slug
$page = AveData::findFirst('about-us');

// Find by ID
$page = AveData::findFirst(123);

// Find with custom model
$article = AveData::findFirst('my-article', 'articles');

// Get DataSources
$datasources = (object)[
    'recent_posts' => (object)[
        'model' => 'Post',
        'limit' => 5,
        'order' => (object)['field' => 'created_at', 'direction' => 'DESC']
    ]
];
$data = AveData::getDataSources($datasources);

// Process image
$thumb = AveData::getImageOrCreate('/storage/photos/hero.jpg', 800, 600, 'webp', 85);
// Returns: /storage/photos/thumbnails/hero-800x600.webp
```

---

## SiteService

**Namespace:** `Monstrex\AveSite\Services\SiteService`
**Contract:** `Monstrex\AveSite\Contracts\SiteContract`
**Facade:** `AveSite`

Manages site-wide settings and configuration.

### Methods

#### Settings Access

```php
public function setting(string $key, $default = null)
```

Get setting value by "group.field" notation.

**Examples:**
```php
$email = AveSite::setting('mail.from_address');
$title = AveSite::setting('general.site_title', 'Default Title');
```

#### Get All Settings

```php
public function getSettings(): array
```

Returns array with template and SEO settings:

```php
[
    'template' => 'template',
    'template_master' => 'layouts.master',
    'template_layout' => 'layouts.main',
    'template_page' => 'pages.page',
    'site_title' => 'My Website',
    'site_description' => 'Website description',
    'seo_title_template' => '%s | My Website',
    'seo_title' => 'Default SEO Title',
    'meta_description' => 'Default meta description',
    'meta_keywords' => 'default, keywords',
]
```

#### Media Storage

```php
public function storeMediaFile($model, string $field): void
```

Add uploaded media file to model's media collection.

#### Current Path

```php
public function currentPath(): string
```

Get current request path (e.g., `/about/team`).

### Usage Examples

```php
use Monstrex\AveSite\Facades\AveSite;

// Get single setting
$smtpHost = AveSite::setting('mail.smtp_host', 'localhost');

// Get all settings for page rendering
$settings = AveSite::getSettings();

// Check current path
$path = AveSite::currentPath();
```

---

## SettingsService

**Namespace:** `Monstrex\AveSite\Services\SettingsService`

Alternative, simpler interface for settings access.

### Methods

```php
public function get(string $key, $default = null)
```

Get setting by "group.field" key.

```php
public function getGroup(string $group): array
```

Get all fields in a group as key=>value array.

### Usage Examples

```php
$settingsService = app(SettingsService::class);

// Get single value
$title = $settingsService->get('general.site_title');

// Get entire group
$mailSettings = $settingsService->getGroup('mail');
// Returns: ['smtp_host' => '...', 'smtp_port' => '...', ...]
```

---

## LocalizationService

**Namespace:** `Monstrex\AveSite\Services\LocalizationService`

Manages database-driven translations.

### Methods

```php
public function loadLocalizations(): void
```

Load all translations from database into Laravel's translator.

```php
public function get(string $key, ?string $locale = null): ?string
```

Get translation for key.

```php
public function set(string $key, string $value, ?string $locale = null): void
```

Store/update translation.

```php
public function getByLocale(string $locale): array
```

Get all translations for locale as key=>value array.

```php
public function has(string $key, ?string $locale = null): bool
```

Check if translation exists.

```php
public function delete(string $key): void
```

Delete translation by key.

```php
public function getAllKeys(): array
```

Get list of all translation keys.

### Usage Examples

```php
$locService = app(LocalizationService::class);

// Load into Laravel (done automatically by service provider)
$locService->loadLocalizations();

// Get translation
$welcome = $locService->get('welcome.message', 'en');

// Set translation
$locService->set('welcome.message', 'Welcome!', 'en');
$locService->set('welcome.message', 'Добро пожаловать!', 'ru');

// Use with Laravel's __() helper
echo __('welcome.message'); // Uses loaded translations
```

---

## ModelResolver

**Namespace:** `Monstrex\AveSite\Services\ModelResolver`

Converts Eloquent models to arrays suitable for Liquid templates, with automatic resolution of Media, FieldSet, and JSON fields.

### Methods

```php
public function resolveModel($model, ?string $resourceClass = null): array
```

Convert model to array with resolved relationships.

**Parameters:**
- `$model` - Eloquent model instance
- `$resourceClass` - Optional Ave Resource class for field metadata

### Field Type Detection

The resolver automatically detects field types from Resource form configuration:

| Field Type | Resolution |
|------------|------------|
| `media` | Calls `getMedia()`, returns array of media objects |
| `fieldset` | Recursively resolves nested arrays with media |
| `code_editor` (JSON) | JSON decodes the value |
| Others | Pass-through |

### Media Array Structure

```php
[
    'url' => '/storage/images/photo.jpg',
    'fullUrl' => 'https://example.com/storage/images/photo.jpg',
    'path' => '/var/www/storage/app/public/images/photo.jpg',
    'fileName' => 'photo.jpg',
    'size' => 102400,
    'mime' => 'image/jpeg',
    'order' => 1,
    'props' => (object)['alt' => 'Photo description', 'title' => 'Photo']
]
```

### Resource Discovery Order

1. ResourceRegistry (Ave admin panel registry)
2. `App\Ave\Resources\{ModelName}\Resource`
3. `Monstrex\AveSite\Resources\{ModelName}\Resource`
4. `Monstrex\Ave\Resources\{ModelName}\Resource`

### Usage Examples

```php
$resolver = app(ModelResolver::class);

// Resolve block with all media and fieldsets
$block = Block::find(1);
$data = $resolver->resolveModel($block);

// Access in Liquid template
// {{ block.images[0].url }}
// {{ block.elements[0].title }}
```

---

## Service Provider Registration

All services are registered as singletons in `AveSiteServiceProvider`:

```php
$this->app->singleton('ave-site', SiteService::class);
$this->app->singleton('ave-page', PageService::class);
$this->app->singleton('ave-block', BlockService::class);
$this->app->singleton('ave-data', DataService::class);
$this->app->singleton(ModelResolver::class);
$this->app->singleton(SettingsService::class);
$this->app->singleton(LocalizationService::class);
```

## See Also

- [Facades](facades.md) - Using service facades
- [Helpers](helpers.md) - Global helper functions
- [Models](models.md) - Model documentation
