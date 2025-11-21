# SEO Features

Documentation for SEO-related features: breadcrumbs, sitemap generation, redirects, and scripts management.

---

## Breadcrumbs

Render breadcrumbs with Schema.org microdata for better search engine understanding.

### Helper Function

```php
render_breadcrumbs(array $breadcrumbs, array $options = []): HtmlString
```

**Parameters:**
- `$breadcrumbs` - Array of `['label' => '', 'url' => '']`
- `$options` - Rendering options

**Options:**
| Option | Default | Description |
|--------|---------|-------------|
| `separator` | `/` | Separator between items |
| `class` | `breadcrumbs` | CSS class for nav element |
| `item_class` | `breadcrumb-item` | CSS class for each item |
| `active_class` | `active` | CSS class for last item |
| `schema` | `true` | Include Schema.org microdata |

### Usage in Blade

```blade
{{-- Basic usage --}}
{!! render_breadcrumbs($breadcrumbs) !!}

{{-- With custom separator --}}
{!! render_breadcrumbs($breadcrumbs, ['separator' => '→']) !!}

{{-- With custom classes --}}
{!! render_breadcrumbs($breadcrumbs, [
    'class' => 'my-breadcrumbs',
    'item_class' => 'crumb',
    'active_class' => 'current',
]) !!}

{{-- Without Schema.org markup --}}
{!! render_breadcrumbs($breadcrumbs, ['schema' => false]) !!}

{{-- Using PageService breadcrumbs --}}
{!! render_breadcrumbs(AvePage::getBreadcrumbs()) !!}
```

### Usage in Liquid

```liquid
{# Basic usage #}
{{ breadcrumbs | breadcrumbs }}

{# With custom separator #}
{{ breadcrumbs | breadcrumbs: '→' }}

{# With arrow separator #}
{{ breadcrumbs | breadcrumbs: '>' }}
```

### Generated HTML

```html
<nav class="breadcrumbs" aria-label="Breadcrumb">
    <ol itemscope itemtype="https://schema.org/BreadcrumbList">
        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="/"><span itemprop="name">Home</span></a>
            <meta itemprop="position" content="1">
        </li>
        <li class="breadcrumb-separator" aria-hidden="true">/</li>
        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="/services"><span itemprop="name">Services</span></a>
            <meta itemprop="position" content="2">
        </li>
        <li class="breadcrumb-separator" aria-hidden="true">/</li>
        <li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <span itemprop="name">Web Development</span>
            <meta itemprop="position" content="3">
        </li>
    </ol>
</nav>
```

### Building Breadcrumbs

PageService automatically builds breadcrumbs from page hierarchy:

```php
// In controller
$page = AvePage::create($pageModel, $settings);
// Breadcrumbs are auto-built from parent pages

// In view
{!! render_breadcrumbs($breadcrumbs) !!}
```

Manually add breadcrumbs:

```php
AvePage::startBreadcrumbs(); // Clears and adds Home
AvePage::addBreadcrumbs('Products', '/products');
AvePage::addBreadcrumbs('Laptops', '/products/laptops');
AvePage::addBreadcrumbs('MacBook Pro'); // Last item, no URL needed
```

---

## Sitemap

Automatic sitemap.xml generation based on routes and models.

### Configuration

Add to `config/ave-site.php`:

```php
'sitemap' => [
    // Enable/disable sitemap route
    'enabled' => true,

    // Cache TTL in seconds (0 to disable)
    'cache_ttl' => 3600,

    // Include simple GET routes without parameters
    'include_simple_routes' => false,

    // Exclude URL patterns (supports wildcards *)
    'exclude' => [
        'admin/*',
        'api/*',
        'login',
        'register',
    ],

    // Exclude route names (supports wildcards *)
    'exclude_names' => [
        'debugbar.*',
        'ignition.*',
    ],

    // Routes with model bindings
    'routes' => [
        'page.show' => [
            'model' => \Monstrex\AveSite\Models\Page::class,
            'param' => 'slug',
            'field' => 'slug',
            'scope' => 'published',
            'lastmod' => 'updated_at',
            'changefreq' => 'weekly',
            'priority' => 0.8,
        ],
    ],

    // Static URLs
    'static' => [
        '/' => ['priority' => 1.0, 'changefreq' => 'daily'],
        '/contacts' => ['priority' => 0.8, 'changefreq' => 'monthly'],
    ],

    // Defaults
    'defaults' => [
        'changefreq' => 'monthly',
        'priority' => 0.5,
    ],
],
```

### Route Configuration

#### Single Parameter Routes

```php
// Route: /pages/{slug}
'page.show' => [
    'model' => \Monstrex\AveSite\Models\Page::class,
    'param' => 'slug',        // Route parameter name
    'field' => 'slug',        // Model field to use
    'scope' => 'published',   // Optional: model scope
    'with' => [],             // Optional: eager load
    'lastmod' => 'updated_at', // Field for lastmod
    'changefreq' => 'weekly',
    'priority' => 0.8,
],
```

#### Multiple Parameter Routes

```php
// Route: /catalog/{category}/{product}
'product.show' => [
    'model' => \App\Models\Product::class,
    'params' => [
        'category' => 'category.slug', // Dot notation for relations
        'product' => 'slug',
    ],
    'with' => ['category'],
    'scope' => 'active',
    'changefreq' => 'daily',
    'priority' => 0.7,
],
```

### Accessing Sitemap

The sitemap is automatically available at `/sitemap.xml`.

```
https://yoursite.com/sitemap.xml
```

### Generated Output

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://yoursite.com/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://yoursite.com/pages/about</loc>
        <lastmod>2025-11-20</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://yoursite.com/pages/services</loc>
        <lastmod>2025-11-15</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
</urlset>
```

### Clearing Cache

```php
use Monstrex\AveSite\Services\SitemapService;

app(SitemapService::class)->clearCache();
```

Or create an artisan command:

```php
// In your application
Artisan::command('sitemap:clear', function () {
    app(\Monstrex\AveSite\Services\SitemapService::class)->clearCache();
    $this->info('Sitemap cache cleared!');
});
```

### Adding to robots.txt

```
User-agent: *
Allow: /

Sitemap: https://yoursite.com/sitemap.xml
```

---

## Complete Example

### Controller

```php
class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->published()->firstOrFail();

        return AvePage::create($page, AveSite::getSettings())->view();
    }
}
```

### Layout Template

```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="keywords" content="{{ $seo['keywords'] }}">
</head>
<body>
    {{-- Breadcrumbs with Schema.org --}}
    {!! render_breadcrumbs($breadcrumbs, ['separator' => '›']) !!}

    <main>
        @yield('content')
    </main>
</body>
</html>
```

### Config

```php
// config/ave-site.php
'sitemap' => [
    'enabled' => true,
    'cache_ttl' => 3600,
    'routes' => [
        'page.show' => [
            'model' => \Monstrex\AveSite\Models\Page::class,
            'param' => 'slug',
            'field' => 'slug',
            'scope' => 'published',
            'changefreq' => 'weekly',
            'priority' => 0.8,
        ],
    ],
    'static' => [
        '/' => ['priority' => 1.0, 'changefreq' => 'daily'],
    ],
],
```

---

## URL Redirects

Manage HTTP redirects for moved or renamed pages to maintain SEO rankings and prevent broken links.

### Features

- **Multiple redirect types**: 301 (Permanent), 302 (Temporary), 307 (Temporary, preserve method), 308 (Permanent, preserve method)
- **Hit tracking**: Automatic counting of redirect usage
- **Enable/disable**: Toggle redirects without deleting them
- **Last hit timestamp**: Track when redirect was last used

### Admin Interface

Access redirects through **Ave Admin Panel → SEO → Redirects**

### Creating Redirects

**Via Admin Panel:**
1. Navigate to **SEO → Redirects**
2. Click "Create"
3. Fill in the form:
   - **From URL**: Old/source URL (e.g., `/old-page`)
   - **To URL**: New/target URL (e.g., `/new-page` or full URL)
   - **Status Code**: Select redirect type (301, 302, 307, 308)
   - **Active**: Toggle to enable/disable
4. Save

**Programmatically:**

```php
use Monstrex\AveSite\Models\Redirect;

Redirect::create([
    'from_url' => '/old-contact-page',
    'to_url' => '/contact',
    'status_code' => 301,
    'is_active' => true,
]);
```

### Model API

```php
use Monstrex\AveSite\Models\Redirect;

// Find active redirect by path
$redirect = Redirect::findByPath('/old-page');

// Get all active redirects
$redirects = Redirect::active()->get();

// Record a hit
$redirect->recordHit();

// Check if redirect exists
if ($redirect = Redirect::findByPath($path)) {
    return redirect($redirect->to_url, $redirect->status_code);
}
```

### Redirect Types

| Code | Type | Description | Use Case |
|------|------|-------------|----------|
| **301** | Permanent | Page permanently moved | Renamed pages, restructured URLs |
| **302** | Temporary | Page temporarily moved | A/B testing, temporary maintenance |
| **307** | Temporary (preserve) | Like 302, but preserves HTTP method | API endpoints, POST requests |
| **308** | Permanent (preserve) | Like 301, but preserves HTTP method | Permanent API changes |

**Recommendation**: Use **301** for most SEO purposes (permanent URL changes).

### URL Formats

**Relative URLs** (within site):
```
From: /old-page
To: /new-page
```

**Absolute URLs** (external redirects):
```
From: /old-blog
To: https://blog.example.com
```

**With query strings**:
```
From: /product?id=123
To: /products/awesome-product
```

### Best Practices

1. **Use 301 for SEO**: Permanent redirects pass link equity (PageRank)
2. **Avoid redirect chains**: Don't redirect A→B→C, use A→C directly
3. **Test redirects**: Verify both source and destination URLs work
4. **Monitor hits**: Inactive redirects (0 hits) may indicate typos
5. **Regular cleanup**: Remove very old redirects that are no longer needed

### Integration with Middleware

The package automatically handles redirects through middleware. No additional setup required.

---

## Scripts Management

Inject custom JavaScript/CSS code snippets into different parts of your HTML document for analytics, tracking, ads, and other third-party integrations.

### Features

- **Position-based injection**: head, body_start, body_end
- **Order control**: Multiple scripts in same position, sorted by order
- **Enable/disable**: Toggle scripts without deleting
- **Auto-wrapping**: Automatically wraps code in `<script>` tags if needed
- **Options field**: Store additional metadata as JSON

### Admin Interface

Access scripts through **Ave Admin Panel → SEO → Scripts**

### Creating Scripts

**Via Admin Panel:**
1. Navigate to **SEO → Scripts**
2. Click "Create"
3. Fill in the form:
   - **Title**: Descriptive name (e.g., "Google Analytics")
   - **Key**: Unique identifier (e.g., `google-analytics`)
   - **Position**: Where to inject (head/body_start/body_end)
   - **Content**: JavaScript or CSS code
   - **Order**: Execution order (lower numbers first)
   - **Active**: Toggle to enable/disable
4. Save

### Helper Function

```php
scripts(string $position): string
```

**Parameters:**
- `$position` - One of: `'head'`, `'body_start'`, `'body_end'`

**Returns:** HTML string with all active scripts for the position

### Usage in Templates

**Blade template:**

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>

    {{-- Inject head scripts (analytics, fonts, etc.) --}}
    {!! scripts('head') !!}
</head>
<body>
    {{-- Inject body start scripts (GTM, etc.) --}}
    {!! scripts('body_start') !!}

    <main>
        @yield('content')
    </main>

    {{-- Inject body end scripts (deferred JS, chat widgets) --}}
    {!! scripts('body_end') !!}
</body>
</html>
```

### Position Guidelines

| Position | Use For | Examples |
|----------|---------|----------|
| **head** | Critical CSS, fonts, meta tags | Google Fonts, Favicon scripts, Critical CSS |
| **body_start** | Analytics, tracking (immediate) | Google Tag Manager, Facebook Pixel |
| **body_end** | Deferred JS, widgets, chat | Analytics (async), Chat widgets, Social buttons |

**Performance Tip**: Use `body_end` for most scripts to avoid blocking page rendering.

### Examples

**Google Analytics (head):**
```javascript
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-XXXXXXXXXX');
</script>
```

**Google Tag Manager (body_start):**
```html
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXXXXX"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
```

**Facebook Pixel (body_end):**
```javascript
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', 'YOUR_PIXEL_ID');
fbq('track', 'PageView');
```

### Auto-Wrapping Behavior

The helper automatically wraps code in `<script>` tags if needed:

**Input (plain JS):**
```javascript
console.log('Hello');
```

**Output:**
```html
<script>
console.log('Hello');
</script>
```

**Input (already wrapped):**
```html
<script src="https://example.com/script.js"></script>
```

**Output:** (unchanged)
```html
<script src="https://example.com/script.js"></script>
```

### Model API

```php
use Monstrex\AveSite\Models\Script;

// Get all active scripts for a position
$headScripts = Script::active()
    ->byPosition('head')
    ->ordered()
    ->get();

// Create a script programmatically
Script::create([
    'title' => 'Google Analytics',
    'key' => 'google-analytics',
    'position' => 'head',
    'content' => $analyticsCode,
    'order' => 10,
    'status' => true,
]);
```

### Best Practices

1. **Use descriptive keys**: `google-analytics`, not `ga1`
2. **Set proper order**: Critical scripts first (lower order number)
3. **Test in incognito**: Verify scripts load correctly
4. **Monitor console**: Check for JavaScript errors
5. **Use body_end**: For non-critical scripts (performance)
6. **Disable, don't delete**: Keep scripts for future use

---

## See Also

- [Pages](pages.md) - Page management
- [Configuration](configuration.md) - Full config reference
- [Helpers](helpers.md) - Helper functions
- [Models](models.md) - Redirect and Script models
