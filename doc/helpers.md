# Helper Functions Reference

Complete documentation for all global helper functions provided by the Ave Site package.

## Overview

The package provides 18 global helper functions organized into categories:

- [Settings & Configuration](#settings--configuration)
- [Content Rendering](#content-rendering)
- [Image Processing](#image-processing)
- [Data Utilities](#data-utilities)
- [File Handling](#file-handling)

All helpers are defined in `src/Helpers/helpers.php` and are available globally after package installation.

---

## Settings & Configuration

### site_setting()

Get a site setting value by "group.field" key.

```php
function site_setting(string $key, $default = null): mixed
```

**Parameters:**
- `$key` - Setting key in "group.field" format
- `$default` - Default value if setting not found

**Examples:**
```php
// Get mail settings
$fromAddress = site_setting('mail.from_address');
$smtpHost = site_setting('mail.smtp_host', 'localhost');

// Get general settings
$siteTitle = site_setting('general.site_title', 'My Website');

// Get SEO defaults
$metaDesc = site_setting('seo.meta_description');
```

---

### site_settings_group()

Get all settings in a group as key=>value array.

```php
function site_settings_group(string $key): array
```

**Parameters:**
- `$key` - Settings group key

**Returns:** Associative array of field values

**Example:**
```php
$mailSettings = site_settings_group('mail');
// Returns:
// [
//     'smtp_host' => 'smtp.example.com',
//     'smtp_port' => '587',
//     'from_address' => 'noreply@example.com',
//     'from_name' => 'My Website',
// ]

// Configure mail dynamically
config([
    'mail.mailers.smtp.host' => $mailSettings['smtp_host'],
    'mail.mailers.smtp.port' => $mailSettings['smtp_port'],
]);
```

---

## Content Rendering

### render_block()

Render a block by its key.

```php
function render_block(string $key): string
```

**Parameters:**
- `$key` - Block key, ID, or title

**Returns:** Rendered HTML string

**Examples:**
```php
// In PHP
$footer = render_block('footer');
$sidebar = render_block('sidebar-widget');

// In Blade
{!! render_block('footer') !!}
```

**Blade Directive:**
```blade
@renderBlock('footer')
```

---

### render_region()

Render all active blocks in a region.

```php
function render_region(string $key, ?string $path = null): string
```

**Parameters:**
- `$key` - Region key (e.g., 'header', 'footer', 'sidebar')
- `$path` - Optional current path override for visibility rules

**Returns:** Concatenated HTML of all visible blocks in the region

**Examples:**
```php
// Render header blocks
$header = render_region('header');

// Render footer blocks
$footer = render_region('footer');

// Force path for visibility checking
$sidebar = render_region('sidebar', '/blog');
```

**Blade Directive:**
```blade
@renderRegion('header')
@renderRegion('sidebar')
```

---

### render_form()

Render a form block with email configuration.

```php
function render_form(string $key, ?string $subject = null, ?string $suffix = null): string
```

**Parameters:**
- `$key` - Form key
- `$subject` - Email subject line
- `$suffix` - Suffix appended to email subject

**Returns:** Rendered form HTML with CSRF token and hidden fields

**Examples:**
```php
// Simple form
$contactForm = render_form('contact-form');

// With subject configuration
$orderForm = render_form('order-form', 'New Order Request', 'Website');
// Subject will be: "New Order Request - Website"
```

**Blade Directive:**
```blade
@renderForm('contact-form')
@renderForm('contact-form', 'Contact Request', 'From Website')
```

---

### render_layout()

Render a layout from JSON configuration.

```php
function render_layout($layout, $page): string
```

**Parameters:**
- `$layout` - Layout JSON configuration
- `$page` - Page model for context

**Returns:** Rendered layout HTML

**Example:**
```php
$layout = json_decode($page->details['layout'] ?? '[]');
$html = render_layout($layout, $page);
```

---

### get_block_field()

Extract a specific field value from a block.

```php
function get_block_field($block, string $field): mixed
```

**Parameters:**
- `$block` - Block model or resolved block array
- `$field` - Field name to extract

**Returns:** Field value or null

**Example:**
```php
$block = Block::where('key', 'hero-section')->first();
$title = get_block_field($block, 'title');
$images = get_block_field($block, 'images');
```

---

## Image Processing

### get_image_or_create()

Resize, crop, or convert an image with caching.

```php
function get_image_or_create(
    string $image_path,
    ?int $width = null,
    ?int $height = null,
    ?string $format = null,
    ?int $quality = null
): string
```

**Parameters:**
- `$image_path` - Source image path (relative or absolute URL)
- `$width` - Target width in pixels
- `$height` - Target height in pixels
- `$format` - Output format: 'webp', 'png', 'jpg'
- `$quality` - Image quality (1-100, default 80)

**Returns:** URL to processed image

**Behavior:**
- Only width: Resize maintaining aspect ratio
- Only height: Resize maintaining aspect ratio
- Both: Crop to exact dimensions (fit mode)
- Images cached in `thumbnails/` subdirectory

**Examples:**
```php
// Resize to width 800, maintain aspect ratio
$thumb = get_image_or_create('/storage/photos/hero.jpg', 800);

// Resize to height 400, maintain aspect ratio
$thumb = get_image_or_create('/storage/photos/hero.jpg', null, 400);

// Crop to exact 400x300
$thumb = get_image_or_create('/storage/photos/hero.jpg', 400, 300);

// Convert to WebP
$webp = get_image_or_create('/storage/photos/hero.jpg', 800, 600, 'webp');

// Full control
$optimized = get_image_or_create('/storage/photos/hero.jpg', 1200, 800, 'webp', 85);
```

**In Blade:**
```blade
<img src="{{ get_image_or_create($page->image, 800, 600, 'webp') }}" alt="">
```

---

### get_image_webp()

Convert image to WebP format (no resize).

```php
function get_image_webp(string $image_path): string
```

**Parameters:**
- `$image_path` - Source image path

**Returns:** URL to WebP version

**Example:**
```php
$webp = get_image_webp('/storage/photos/hero.jpg');
// Returns: /storage/photos/thumbnails/hero.webp
```

---

### get_image_or_create_webp()

Resize image and convert to WebP format.

```php
function get_image_or_create_webp(
    string $image_path,
    ?int $width = null,
    ?int $height = null,
    ?int $quality = null
): string
```

**Parameters:**
- `$image_path` - Source image path
- `$width` - Target width
- `$height` - Target height
- `$quality` - WebP quality (1-100)

**Returns:** URL to resized WebP image

**Example:**
```php
$thumb = get_image_or_create_webp('/storage/photos/hero.jpg', 400, 300, 80);
```

---

## Data Utilities

### flat_to_tree()

Convert flat array with parent_id to hierarchical tree structure.

```php
function flat_to_tree(array $array, $parent_id = null): array
```

**Parameters:**
- `$array` - Flat array with 'id' and 'parent_id' keys
- `$parent_id` - Starting parent ID (null for root)

**Returns:** Hierarchical array with 'children' key

**Example:**
```php
$flat = [
    ['id' => 1, 'parent_id' => null, 'title' => 'Home'],
    ['id' => 2, 'parent_id' => null, 'title' => 'About'],
    ['id' => 3, 'parent_id' => 2, 'title' => 'Team'],
    ['id' => 4, 'parent_id' => 2, 'title' => 'Contact'],
];

$tree = flat_to_tree($flat);
// Returns:
// [
//     ['id' => 1, 'parent_id' => null, 'title' => 'Home'],
//     [
//         'id' => 2,
//         'parent_id' => null,
//         'title' => 'About',
//         'children' => [
//             ['id' => 3, 'parent_id' => 2, 'title' => 'Team'],
//             ['id' => 4, 'parent_id' => 2, 'title' => 'Contact'],
//         ]
//     ],
// ]
```

---

### get_first_not_empty()

Return first non-empty value from array.

```php
function get_first_not_empty(array $values): mixed
```

**Parameters:**
- `$values` - Array of values to check

**Returns:** First non-empty value or null

**Example:**
```php
$title = get_first_not_empty([
    $page->seo['seo_title'],
    $page->title,
    $settings['site_title'],
]);
// Returns first non-empty value from the list
```

---

### seo_meta()

Extract normalized SEO metadata from model or array.

```php
function seo_meta(mixed $model): array
```

**Parameters:**
- `$model` - Model with seoMeta() method or array

**Returns:** Array with keys: `seo_title`, `meta_description`, `meta_keywords`

**Example:**
```php
// From model
$seo = seo_meta($page);

// From array
$seo = seo_meta([
    'seo_title' => 'Custom Title',
    'meta_description' => 'Description',
]);

// In Blade
<title>{{ seo_meta($page)['seo_title'] ?: $page->title }}</title>
```

---

### translit_cyrillic()

Convert Cyrillic characters to Latin equivalents.

```php
function translit_cyrillic(string $string): string
```

**Parameters:**
- `$string` - String with Cyrillic characters

**Returns:** Transliterated string

**Example:**
```php
$slug = translit_cyrillic('Привет мир');
// Returns: "Privet_mir"

$filename = translit_cyrillic('Документ.pdf');
// Returns: "Dokument.pdf"
```

---

## File Handling

### get_file()

Parse file path from JSON or string format.

```php
function get_file($file_path): string
```

**Parameters:**
- `$file_path` - JSON string with download_link or plain path

**Returns:** Storage URL to file

**Example:**
```php
// Plain path
$url = get_file('documents/report.pdf');

// JSON format (from file upload field)
$url = get_file('[{"download_link":"documents/report.pdf","original_name":"Report.pdf"}]');
```

---

### store_post_files()

Store uploaded files from request.

```php
function store_post_files(Request $request, string $slug, string $field, string $public = 'public'): string
```

**Parameters:**
- `$request` - HTTP request
- `$slug` - Storage subdirectory
- `$field` - Form field name
- `$public` - Storage disk

**Returns:** JSON string with file info

**Example:**
```php
$files = store_post_files($request, 'uploads', 'attachments');
// Returns: '[{"download_link":"uploads/November2024/file.pdf","original_name":"document.pdf"}]'
```

---

### generate_filename()

Generate unique filename with transliteration.

```php
function generate_filename($file, string $path): string
```

**Parameters:**
- `$file` - Uploaded file
- `$path` - Storage path for uniqueness check

**Returns:** Unique filename without extension

**Example:**
```php
$filename = generate_filename($uploadedFile, 'uploads/');
// Returns unique filename like "document" or "document1" if exists
```

---

## Usage in Blade Templates

### Direct Function Calls

```blade
{{-- Settings --}}
{{ site_setting('general.site_title') }}

{{-- Blocks --}}
{!! render_block('footer') !!}
{!! render_region('header') !!}
{!! render_form('contact-form') !!}

{{-- Images --}}
<img src="{{ get_image_or_create($image, 800, 600, 'webp') }}" alt="">
```

### Blade Directives

```blade
{{-- Block rendering --}}
@renderBlock('footer')

{{-- Region rendering --}}
@renderRegion('header')
@renderRegion('sidebar')

{{-- Form rendering --}}
@renderForm('contact-form')
@renderForm('contact-form', 'Subject', 'Suffix')
```

---

## See Also

- [Facades](facades.md) - Service facade documentation
- [Templating](templating.md) - Liquid template filters
- [Services](services.md) - Service class documentation
