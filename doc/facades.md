# Facades Reference

Complete documentation for Laravel facades provided by the Ave Site package.

## Overview

The package provides 4 facades for convenient access to core services:

| Facade | Service | Purpose |
|--------|---------|---------|
| `AveSite` | SiteService | Site settings management |
| `AvePage` | PageService | Page lifecycle management |
| `AveBlock` | BlockService | Block and form rendering |
| `AveData` | DataService | Data fetching and images |

## Importing Facades

```php
use Monstrex\AveSite\Facades\AveSite;
use Monstrex\AveSite\Facades\AvePage;
use Monstrex\AveSite\Facades\AveBlock;
use Monstrex\AveSite\Facades\AveData;
```

---

## AveSite

**Service:** `Monstrex\AveSite\Services\SiteService`
**Accessor:** `'ave-site'`

Manages site-wide settings and configuration.

### Methods

#### setting()

Get a setting value by "group.field" key.

```php
AveSite::setting(string $key, $default = null): mixed
```

**Example:**
```php
$title = AveSite::setting('general.site_title', 'Default Title');
$smtpHost = AveSite::setting('mail.smtp_host');
```

#### getSettings()

Get all template and SEO settings.

```php
AveSite::getSettings(): array
```

**Returns:**
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

**Example:**
```php
$settings = AveSite::getSettings();
$masterTemplate = $settings['template_master'];
```

#### storeMediaFile()

Add media file to a model.

```php
AveSite::storeMediaFile($model, string $field): void
```

**Example:**
```php
AveSite::storeMediaFile($setting, 'logo');
```

#### currentPath()

Get current request path.

```php
AveSite::currentPath(): string
```

**Example:**
```php
$path = AveSite::currentPath();
// Returns: "/about/team"
```

---

## AvePage

**Service:** `Monstrex\AveSite\Services\PageService`
**Accessor:** `'ave-page'`

Manages page lifecycle, templates, SEO, and rendering.

### Initialization Methods

#### create()

Initialize page with full setup.

```php
AvePage::create($content, array $settings = []): PageService
```

**Example:**
```php
$page = Page::where('slug', $slug)->firstOrFail();
$settings = AveSite::getSettings();

AvePage::create($page, $settings);
```

#### setPage() / setContent()

Set page model.

```php
AvePage::setPage($content): PageService
AvePage::setContent($model): PageService
```

### Template Methods

```php
AvePage::setTemplates($content, array $settings): PageService
AvePage::setMasterTemplate(string $template): PageService
AvePage::setLayoutTemplate(string $template): PageService
AvePage::setPageTemplate(string $template): PageService
```

**Example:**
```php
AvePage::setMasterTemplate('layouts.admin')
       ->setLayoutTemplate('layouts.dashboard')
       ->setPageTemplate('pages.settings');
```

### SEO Methods

```php
AvePage::setSeo($content, array $settings): PageService
AvePage::setSeoTitle(string $title): PageService
AvePage::setSeoDescription(string $description): PageService
AvePage::setSeoKeywords(string $keywords): PageService
```

**Example:**
```php
AvePage::setSeoTitle('Custom Page Title')
       ->setSeoDescription('Page description for search engines');
```

### Breadcrumb Methods

```php
AvePage::startBreadcrumbs(): PageService
AvePage::addBreadcrumbs(string $label, ?string $url = null): PageService
AvePage::buildBreadcrumbs(): PageService
```

**Example:**
```php
AvePage::startBreadcrumbs()
       ->addBreadcrumbs('Blog', '/blog')
       ->addBreadcrumbs('Category', '/blog/category')
       ->addBreadcrumbs('Article Title');
```

### Hierarchy Methods

```php
AvePage::setParents($page, string $parent_field = 'parent_id'): PageService
AvePage::setChildren(string $parent_field = 'parent_id'): PageService
```

### Banner Method

```php
AvePage::setBanner($page, array $parents, ?string $default_banner): PageService
```

### Getter Methods

```php
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
```

### Rendering

#### view()

Render page to Laravel Response.

```php
AvePage::view(?string $template_layout = null, array $data = []): Response
```

**Example:**
```php
return AvePage::create($page, $settings)
    ->startBreadcrumbs()
    ->buildBreadcrumbs()
    ->view();
```

**View receives these variables:**
- `$page` - Page model
- `$title` - Page title
- `$banner` - Banner image URL
- `$breadcrumbs` - Breadcrumb array
- `$parents` - Parent pages
- `$children` - Child pages
- `$data` - DataSources data

### Complete Usage Example

```php
use Monstrex\AveSite\Facades\AveSite;
use Monstrex\AveSite\Facades\AvePage;
use Monstrex\AveSite\Models\Page;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        $settings = AveSite::getSettings();

        return AvePage::create($page, $settings)
            ->startBreadcrumbs()
            ->buildBreadcrumbs()
            ->view();
    }

    public function customPage($slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        return AvePage::setPage($page)
            ->setMasterTemplate('layouts.custom')
            ->setLayoutTemplate('layouts.wide')
            ->setSeoTitle('Custom Title - ' . $page->title)
            ->startBreadcrumbs()
            ->addBreadcrumbs('Custom Section', '/custom')
            ->addBreadcrumbs($page->title)
            ->view('layouts.custom', ['extra' => 'data']);
    }
}
```

---

## AveBlock

**Service:** `Monstrex\AveSite\Services\BlockService`
**Accessor:** `'ave-block'`

Renders blocks, regions, and forms using Liquid templates.

### Rendering Methods

#### renderRegion()

Render all active blocks in a region.

```php
AveBlock::renderRegion(string $region_name, ?string $path = null): string
```

**Example:**
```php
$header = AveBlock::renderRegion('header');
$footer = AveBlock::renderRegion('footer');

// With custom path for visibility rules
$sidebar = AveBlock::renderRegion('sidebar', '/blog');
```

#### render()

Render single block by key, ID, or title.

```php
AveBlock::render(string|int $key): string
```

**Example:**
```php
$hero = AveBlock::render('hero-section');
$widget = AveBlock::render(123); // By ID
```

#### renderForm()

Render form block with email configuration.

```php
AveBlock::renderForm(string $key, ?string $subject = null, ?string $suffix = null): string
```

**Example:**
```php
$contactForm = AveBlock::renderForm('contact-form', 'Contact Request', 'Website');
```

#### renderLayout()

Render layout from JSON configuration.

```php
AveBlock::renderLayout($layout, $page): string
```

### Lookup Methods

```php
AveBlock::getByID(int $id): ?Block
AveBlock::getByKey(string $key): ?Block
AveBlock::getByTitle(string $title): ?Block
AveBlock::getFormByKey(string $key): ?Block
```

**Example:**
```php
$block = AveBlock::getByKey('testimonials');
if ($block) {
    $content = $block->content;
}
```

#### getBlockField()

Extract field from block data.

```php
AveBlock::getBlockField($block, string $field): mixed
```

**Example:**
```php
$block = AveBlock::getByKey('hero-section');
$title = AveBlock::getBlockField($block, 'title');
$images = AveBlock::getBlockField($block, 'images');
```

---

## AveData

**Service:** `Monstrex\AveSite\Services\DataService`
**Accessor:** `'ave-data'`

Data fetching, model resolution, and image processing.

### Finding Records

#### findFirst()

Find record by slug or ID.

```php
AveData::findFirst($alias, ?string $modelSlug = null, bool $fail = true): mixed
```

**Example:**
```php
// Find page by slug
$page = AveData::findFirst('about-us');

// Find by ID
$page = AveData::findFirst(123);

// From custom table
$article = AveData::findFirst('my-article', 'articles');

// Without 404 on not found
$page = AveData::findFirst('maybe-exists', null, false);
```

#### where()

Find by field value.

```php
AveData::where(string $field, string $value, ?string $modelSlug = null, bool $fail = true): mixed
```

**Example:**
```php
$page = AveData::where('slug', 'about-us');
$user = AveData::where('email', 'user@example.com', 'users');
```

#### findByField()

Find multiple records.

```php
AveData::findByField(
    string $modelSlug,
    string $field,
    $value,
    string $order = 'order',
    string $direction = 'ASC'
): Collection
```

**Example:**
```php
$articles = AveData::findByField('articles', 'category_id', 5, 'created_at', 'DESC');
```

### DataSources

#### getDataSources()

Load multiple DataSources from configuration.

```php
AveData::getDataSources(object $datasources): array
```

**Example:**
```php
$config = (object)[
    'articles' => (object)[
        'model' => 'Article',
        'where' => (object)['featured' => true],
        'order' => (object)['field' => 'created_at', 'direction' => 'DESC'],
        'limit' => 10
    ],
    'testimonials' => (object)[
        'model' => 'Testimonial',
        'limit' => 5,
        'random' => true
    ]
];

$data = AveData::getDataSources($config);
// $data['articles'] - Latest 10 featured articles
// $data['testimonials'] - 5 random testimonials
```

### Menu Generation

#### getMenu()

Get hierarchical menu from tree model.

```php
AveData::getMenu(?string $modelSlug = null, ?array $parent = null): ?array
```

**Example:**
```php
// Get full page tree
$menu = AveData::getMenu();

// From custom table
$categoryMenu = AveData::getMenu('categories');

// Get submenu from specific parent
$submenu = AveData::getMenu('ave_site_pages', ['field' => 'slug', 'value' => 'products']);
```

### Image Processing

#### getImageOrCreate()

Resize, crop, or convert images.

```php
AveData::getImageOrCreate(
    string $image_url,
    ?int $width = null,
    ?int $height = null,
    ?string $format = null,
    ?int $quality = null
): string
```

**Example:**
```php
// Resize to width
$thumb = AveData::getImageOrCreate('/storage/photo.jpg', 800);

// Resize to exact dimensions (crop)
$thumb = AveData::getImageOrCreate('/storage/photo.jpg', 400, 300);

// Convert to WebP
$webp = AveData::getImageOrCreate('/storage/photo.jpg', 800, 600, 'webp', 85);
```

---

## Facade Aliases

The package registers these aliases automatically:

```php
'AveSite' => Monstrex\AveSite\Facades\AveSite::class,
'AvePage' => Monstrex\AveSite\Facades\AvePage::class,
'AveBlock' => Monstrex\AveSite\Facades\AveBlock::class,
'AveData' => Monstrex\AveSite\Facades\AveData::class,
```

You can use them without importing:

```php
$title = \AveSite::setting('general.site_title');
$html = \AveBlock::render('footer');
```

---

## Dependency Injection Alternative

Instead of facades, you can inject services directly:

```php
use Monstrex\AveSite\Services\SiteService;
use Monstrex\AveSite\Services\PageService;
use Monstrex\AveSite\Services\BlockService;
use Monstrex\AveSite\Services\DataService;

class PageController extends Controller
{
    public function __construct(
        protected SiteService $siteService,
        protected PageService $pageService,
        protected BlockService $blockService,
        protected DataService $dataService
    ) {}

    public function show($slug)
    {
        $page = $this->dataService->findFirst($slug);
        $settings = $this->siteService->getSettings();

        return $this->pageService
            ->create($page, $settings)
            ->view();
    }
}
```

---

## See Also

- [Services](services.md) - Detailed service documentation
- [Helpers](helpers.md) - Global helper functions
- [Pages](pages.md) - Page management guide
