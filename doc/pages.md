# Pages System

Complete guide to hierarchical page management in Ave Site.

## Overview

The Pages system provides:

- Hierarchical page structure (parent-child relationships)
- SEO metadata management
- Custom templates per page
- DataSources for dynamic content
- Banner image inheritance
- Automatic breadcrumb generation

## Page Model

### Database Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `parent_id` | bigint | Parent page ID (null for root) |
| `status` | boolean | Published status |
| `menu` | boolean | Show in navigation |
| `title` | string | Page title |
| `slug` | string | URL slug (unique) |
| `content` | text | Page content (Liquid) |
| `image` | string | Featured/banner image |
| `images` | json | Additional images |
| `order` | int | Sort order |
| `seo` | json | SEO metadata |
| `details` | json | Page configuration |

---

## Creating Pages

### Via Admin Panel

1. Go to **Content > Pages**
2. Click **Create**
3. Fill in page details:
   - **Title** - Page title
   - **Slug** - URL path (auto-generated from title)
   - **Parent** - Parent page for hierarchy
   - **Status** - Published or Draft
   - **Menu** - Show in navigation menus
   - **Content** - Page content (supports Liquid)
   - **SEO** - Title, description, keywords
   - **Details** - DataSources, templates, banner

---

## Page Hierarchy

### Structure Example

```
Home (/)
├── About (/about)
│   ├── Team (/about/team)
│   └── History (/about/history)
├── Services (/services)
│   ├── Web Design (/services/web-design)
│   └── Development (/services/development)
└── Contact (/contact)
```

### Querying Hierarchy

```php
use Monstrex\AveSite\Models\Page;

// Get root pages
$rootPages = Page::roots()->published()->orderBy('order')->get();

// Get children
$page = Page::where('slug', 'about')->first();
$children = $page->children()->published()->orderBy('order')->get();

// Get parent
$parent = $page->parent;

// Get all ancestors
$ancestors = [];
$current = $page;
while ($current->parent) {
    $ancestors[] = $current->parent;
    $current = $current->parent;
}
```

### Building Menu from Pages

```php
// Get hierarchical menu
$menu = AveData::getMenu('ave_site_pages');

// Structure:
// [
//     ['id' => 1, 'title' => 'About', 'slug' => 'about', 'children' => [
//         ['id' => 2, 'title' => 'Team', 'slug' => 'about/team', 'children' => []],
//         ...
//     ]],
//     ...
// ]
```

---

## SEO Configuration

### SEO Fields

```json
{
    "seo_title": "Custom Page Title for Search Engines",
    "meta_description": "Page description for search results (150-160 chars)",
    "meta_keywords": "keyword1, keyword2, keyword3"
}
```

### SEO Resolution Hierarchy

When rendering, SEO is resolved in order:

1. Page's `seo.seo_title` field
2. Page's `title` field
3. Settings `seo.seo_title`
4. Settings `general.site_title`

### Using SEO Data

```php
// In controller
$seo = $page->seoMeta();
// ['seo_title' => '...', 'meta_description' => '...', 'meta_keywords' => '...']

// Using PageService
$settings = AveSite::getSettings();
AvePage::create($page, $settings); // SEO auto-configured

// Get resolved SEO
$title = AvePage::getSeoTitle();
$description = AvePage::getSeoDescription();
$keywords = AvePage::getSeoKeywords();
```

### In Blade Template

```blade
<head>
    <title>{{ AvePage::getSeoTitle() }}</title>
    <meta name="description" content="{{ AvePage::getSeoDescription() }}">
    <meta name="keywords" content="{{ AvePage::getSeoKeywords() }}">
</head>
```

---

## Page Templates

### Template Hierarchy

```
template_master (base layout)
└── template_layout (content layout)
    └── template_page (page template)
```

### Configuring Templates

**Per-page templates (details JSON):**
```json
{
    "template": "pages.custom",
    "template_layout": "layouts.wide",
    "template_master": "layouts.admin"
}
```

**Default templates (config/ave-site.php):**
```php
'template_master' => 'layouts.master',
'template_layout' => 'layouts.main',
'template_page' => 'pages.page',
```

### Example Template Structure

**layouts/master.blade.php:**
```blade
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ AvePage::getSeoTitle() }}</title>
    <meta name="description" content="{{ AvePage::getSeoDescription() }}">
    @stack('head')
</head>
<body>
    @renderRegion('header')

    @yield('layout')

    @renderRegion('footer')

    @stack('scripts')
</body>
</html>
```

**layouts/main.blade.php:**
```blade
@extends('layouts.master')

@section('layout')
<main class="container">
    @if($banner)
        <div class="page-banner" style="background-image: url('{{ $banner }}')">
            <h1>{{ $title }}</h1>
        </div>
    @endif

    @include('partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

    <div class="content">
        @yield('content')
    </div>

    @if(count($children))
        <nav class="children-nav">
            @foreach($children as $child)
                <a href="/{{ $child->slug }}">{{ $child->title }}</a>
            @endforeach
        </nav>
    @endif
</main>
@endsection
```

**pages/page.blade.php:**
```blade
@extends('layouts.main')

@section('content')
    {!! $page->content !!}

    @if(isset($data['articles']))
        <div class="articles">
            @foreach($data['articles'] as $article)
                <article>
                    <h2>{{ $article['title'] }}</h2>
                    <p>{{ $article['excerpt'] }}</p>
                </article>
            @endforeach
        </div>
    @endif
@endsection
```

---

## DataSources

DataSources allow pages to load related data automatically.

### Configuration (details JSON)

```json
{
    "datasources": {
        "articles": {
            "model": "Article",
            "where": {"status": 1, "featured": true},
            "order": {"field": "created_at", "direction": "DESC"},
            "limit": 10,
            "with": ["author", "category"]
        },
        "testimonials": {
            "model": "Testimonial",
            "limit": 5,
            "random": true
        },
        "categories": {
            "model": "Category",
            "order": {"field": "name", "direction": "ASC"}
        }
    }
}
```

### DataSource Options

| Option | Description |
|--------|-------------|
| `model` | Model class name |
| `where` | Filter conditions |
| `order.field` | Sort field |
| `order.direction` | ASC or DESC |
| `limit` | Maximum records |
| `with` | Eager load relations |
| `random` | Randomize results |

### Accessing DataSources

```blade
{{-- In Blade --}}
@foreach($data['articles'] as $article)
    <h2>{{ $article['title'] }}</h2>
@endforeach

{{-- Check if exists --}}
@if(isset($data['testimonials']))
    @foreach($data['testimonials'] as $item)
        ...
    @endforeach
@endif
```

```liquid
{# In Liquid content #}
{% for article in data.articles %}
    <h2>{{ article.title }}</h2>
{% endfor %}
```

---

## Banner Images

### Banner Resolution

Banners are resolved in order:
1. Current page's `image` field
2. Parent page's `image` field (traversing up)
3. Default banner from settings (`general.default_banner`)

### Configuration (details JSON)

```json
{
    "banner": "/storage/banners/custom-banner.jpg"
}
```

### Usage in Templates

```blade
@if($banner)
    <div class="hero" style="background-image: url('{{ get_image_or_create($banner, 1920, 600, 'webp') }}')">
        <h1>{{ $title }}</h1>
    </div>
@endif
```

---

## Breadcrumbs

### Automatic Generation

```php
// In controller
return AvePage::create($page, $settings)
    ->startBreadcrumbs()    // Adds Home link
    ->buildBreadcrumbs()    // Adds parent chain + current
    ->view();
```

### Manual Breadcrumbs

```php
return AvePage::create($page, $settings)
    ->startBreadcrumbs()
    ->addBreadcrumbs('Products', '/products')
    ->addBreadcrumbs('Category', '/products/category')
    ->addBreadcrumbs($page->title)
    ->view();
```

### Rendering Breadcrumbs

**partials/breadcrumbs.blade.php:**
```blade
@if(count($breadcrumbs) > 1)
<nav aria-label="Breadcrumb">
    <ol class="breadcrumbs">
        @foreach($breadcrumbs as $crumb)
            @if($loop->last)
                <li class="active" aria-current="page">{{ $crumb['label'] }}</li>
            @else
                <li><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
```

---

## Page Controller Example

### Basic Implementation

```php
namespace App\Http\Controllers;

use Monstrex\AveSite\Facades\AveSite;
use Monstrex\AveSite\Facades\AvePage;
use Monstrex\AveSite\Facades\AveData;

class PageController extends Controller
{
    public function show($slug = null)
    {
        // Handle homepage
        if (!$slug || $slug === '/') {
            $slug = 'home';
        }

        // Find page
        $page = AveData::findFirst($slug);

        // Get settings
        $settings = AveSite::getSettings();

        // Render
        return AvePage::create($page, $settings)
            ->startBreadcrumbs()
            ->buildBreadcrumbs()
            ->view();
    }
}
```

### Advanced Implementation

```php
class PageController extends Controller
{
    public function show($slug = null)
    {
        $slug = $slug ?: 'home';
        $page = AveData::findFirst($slug);
        $settings = AveSite::getSettings();

        // Check for custom template
        $template = $page->details['template'] ?? null;

        // Build page
        $pageService = AvePage::create($page, $settings)
            ->startBreadcrumbs()
            ->buildBreadcrumbs();

        // Add extra data
        $extraData = [];

        // For blog page, add recent posts
        if ($slug === 'blog') {
            $extraData['recent_posts'] = Post::latest()->limit(5)->get();
        }

        return $pageService->view($template, $extraData);
    }

    public function category($category, $slug)
    {
        $page = AveData::findFirst($slug);
        $settings = AveSite::getSettings();

        return AvePage::create($page, $settings)
            ->startBreadcrumbs()
            ->addBreadcrumbs('Categories', '/categories')
            ->addBreadcrumbs($category, '/categories/' . $category)
            ->addBreadcrumbs($page->title)
            ->view();
    }
}
```

### Routes

```php
// routes/web.php
Route::get('/', [PageController::class, 'show']);
Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '.*');
```

---

## Error Pages

### Configuration

```php
// config/ave-site.php
'error_pages' => [
    403 => 'error-403',
    404 => 'error-404',
    500 => 'error-500',
    503 => 'error-503',
],
```

### Creating Error Pages

Create pages with slugs matching the config (e.g., `error-404`).

### Exception Handler

```php
// app/Exceptions/Handler.php
use Monstrex\AveSite\Exceptions\VoyagerSiteException;

public function render($request, Throwable $exception)
{
    if ($exception instanceof VoyagerSiteException) {
        $slug = config('ave-site.error_pages.' . $exception->getCode(), 'error-404');
        $page = Page::where('slug', $slug)->first();

        if ($page) {
            return AvePage::create($page, AveSite::getSettings())
                ->setResponseCode($exception->getCode())
                ->view();
        }
    }

    return parent::render($request, $exception);
}
```

---

## Scopes

### Published Pages

```php
// Only published (status = true)
Page::published()->get();
```

### Root Pages

```php
// Only top-level (parent_id = null)
Page::roots()->get();
```

### Combined

```php
Page::published()
    ->roots()
    ->where('menu', true)
    ->orderBy('order')
    ->get();
```

---

## See Also

- [Models](models.md) - Page model reference
- [Services](services.md) - PageService documentation
- [Facades](facades.md) - AvePage facade
- [Templating](templating.md) - Liquid templates
