# Models Reference

Complete documentation for all Eloquent models in the Ave Site package.

## Overview

The package provides 6 core models:

| Model | Table | Purpose |
|-------|-------|---------|
| [Page](#page) | `ave_site_pages` | Hierarchical pages with SEO |
| [Block](#block) | `ave_site_blocks` | Reusable content blocks |
| [BlockRegion](#blockregion) | `ave_site_block_regions` | Block container areas |
| [Form](#form) | `ave_site_forms` | Form definitions |
| [Setting](#setting) | `ave_site_settings` | Site configuration |
| [Localization](#localization) | `ave_site_localizations` | Database translations |

---

## Page

**Namespace:** `Monstrex\AveSite\Models\Page`
**Table:** `ave_site_pages`
**Traits:** `HasMedia`, `HasSeoMeta`

### Database Schema

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `parent_id` | bigint (nullable) | Parent page ID for hierarchy |
| `status` | boolean | Publication status (1=published) |
| `menu` | boolean | Show in menu (1=yes) |
| `title` | string | Page title |
| `slug` | string (unique) | URL slug |
| `content` | text (nullable) | Page content (Liquid template) |
| `image` | string (nullable) | Featured image path |
| `images` | json (nullable) | Additional images array |
| `order` | unsigned int (nullable) | Sort order |
| `seo` | json | SEO metadata |
| `details` | json | Page options/configuration |
| `created_at` | timestamp | Creation date |
| `updated_at` | timestamp | Last update date |

### SEO JSON Structure

```json
{
    "seo_title": "Custom SEO Title",
    "meta_description": "Page description for search engines",
    "meta_keywords": "keyword1, keyword2, keyword3"
}
```

### Details JSON Structure

```json
{
    "datasources": {
        "articles": {
            "model": "Article",
            "where": {"category_id": 1},
            "order": {"field": "created_at", "direction": "DESC"},
            "limit": 10
        }
    },
    "banner": "/storage/banners/page-banner.jpg",
    "template": "pages.custom",
    "template_layout": "layouts.wide"
}
```

### Relationships

```php
// Self-referential for hierarchy
public function parent(): BelongsTo
public function children(): HasMany
```

### Scopes

```php
// Only published pages
Page::published()->get();

// Only root pages (no parent)
Page::roots()->get();

// Combined
Page::published()->roots()->orderBy('order')->get();
```

### SEO Methods (HasSeoMeta Trait)

```php
// Get normalized SEO array
$seo = $page->seoMeta();
// Returns: ['seo_title' => '', 'meta_description' => '', 'meta_keywords' => '']

// Access as attribute
$seo = $page->seo_meta;
```

### Usage Examples

```php
use Monstrex\AveSite\Models\Page;

// Find by slug
$page = Page::where('slug', 'about-us')->first();

// Get published pages for menu
$menuPages = Page::published()
    ->where('menu', true)
    ->orderBy('order')
    ->get();

// Get page hierarchy
$rootPages = Page::roots()->with('children')->get();

// Access SEO data
$title = $page->seoMeta()['seo_title'] ?: $page->title;
```

---

## Block

**Namespace:** `Monstrex\AveSite\Models\Block`
**Table:** `ave_site_blocks`
**Traits:** `HasMedia`, `HasFieldSet`

### Database Schema

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `title` | string | Block title |
| `key` | string (unique) | Block identifier |
| `region_id` | bigint (nullable) | Foreign key to BlockRegion |
| `order` | integer | Sort order within region |
| `status` | boolean | Active status |
| `urls` | text (nullable) | URL patterns for visibility |
| `rules` | tinyint | Visibility rule (0=EXCEPT, 1=ONLY) |
| `content` | longtext (nullable) | Liquid template content |
| `images` | json | Image collections |
| `elements` | json | Additional elements/media |
| `details` | json | Block configuration |
| `created_at` | timestamp | Creation date |
| `updated_at` | timestamp | Last update date |

### Visibility Rules

| Value | Behavior |
|-------|----------|
| `0` (EXCEPT) | Show on all pages EXCEPT those matching `urls` |
| `1` (ONLY) | Show ONLY on pages matching `urls` |

**URL Patterns:**
- One URL per line
- Use `<front>` for homepage
- Supports wildcards: `/blog/*`

Example `urls` field:
```
<front>
/about
/contact
/blog/*
```

### Details JSON Structure

```json
{
    "validator": {
        "name": "required|string|max:255",
        "email": "required|email",
        "message": "required|string"
    },
    "messages": {
        "name.required": "Please enter your name"
    },
    "to_address": "admin@example.com",
    "datasources": {
        "testimonials": {
            "model": "Testimonial",
            "limit": 5,
            "random": true
        }
    }
}
```

### Relationships

```php
public function region(): BelongsTo
```

### Scopes

```php
// Active blocks only
Block::active()->get();

// Blocks in specific region
Block::inRegion('header')->get();

// Ordered by sort order
Block::ordered()->get();

// Combined
Block::active()->inRegion('footer')->ordered()->get();
```

### Methods

```php
// Check if block is a form
$isForm = $block->isForm();
// Returns true if details.validator exists
```

### Usage Examples

```php
use Monstrex\AveSite\Models\Block;

// Get block by key
$footer = Block::where('key', 'footer')->first();

// Get all active blocks in header region
$headerBlocks = Block::active()
    ->inRegion('header')
    ->ordered()
    ->get();

// Check if block is a form
if ($block->isForm()) {
    // Handle form-specific logic
}

// Access block content
$html = $block->content; // Liquid template
```

---

## BlockRegion

**Namespace:** `Monstrex\AveSite\Models\BlockRegion`
**Table:** `ave_site_block_regions`

### Database Schema

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `key` | string (unique) | Region identifier |
| `name` | string | Display name |
| `created_at` | timestamp | Creation date |
| `updated_at` | timestamp | Last update date |

### Relationships

```php
public function blocks(): HasMany
```

### Usage Examples

```php
use Monstrex\AveSite\Models\BlockRegion;

// Get region with blocks
$header = BlockRegion::where('key', 'header')
    ->with(['blocks' => fn($q) => $q->active()->ordered()])
    ->first();

// Iterate blocks
foreach ($header->blocks as $block) {
    echo $block->title;
}

// Create new region
BlockRegion::create([
    'key' => 'sidebar',
    'name' => 'Sidebar Area'
]);
```

---

## Form

**Namespace:** `Monstrex\AveSite\Models\Form`
**Table:** `ave_site_forms`

### Database Schema

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `status` | boolean | Active status |
| `order` | integer | Sort order |
| `title` | string | Form title |
| `key` | string (unique) | Form identifier |
| `content` | longtext | Liquid template for form HTML |
| `details` | json | Validation rules, messages, email config |
| `created_at` | timestamp | Creation date |
| `updated_at` | timestamp | Last update date |

### Details JSON Structure

```json
{
    "validator": {
        "name": "required|string|max:255",
        "email": "required|email",
        "phone": "nullable|string",
        "message": "required|string|min:10",
        "g-recaptcha-response": "recaptcha"
    },
    "messages": {
        "name.required": "Please provide your name",
        "email.required": "Email address is required",
        "email.email": "Please enter a valid email address"
    },
    "to_address": "contact@example.com"
}
```

### Scopes

```php
// Active forms only
Form::active()->get();
```

### Usage Examples

```php
use Monstrex\AveSite\Models\Form;

// Get form by key
$contactForm = Form::where('key', 'contact-form')->first();

// Access validation rules
$rules = $contactForm->details['validator'] ?? [];

// Access custom messages
$messages = $contactForm->details['messages'] ?? [];

// Get recipient email
$toAddress = $contactForm->details['to_address'] ?? null;
```

---

## Setting

**Namespace:** `Monstrex\AveSite\Models\Setting`
**Table:** `ave_site_settings`
**Traits:** `HasMedia`

### Database Schema

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `key` | string (unique) | Setting identifier |
| `group` | string | Settings group name |
| `title` | string | Display title |
| `order` | integer | Sort order |
| `fields` | json | Field definitions and values |
| `created_at` | timestamp | Creation date |
| `updated_at` | timestamp | Last update date |

### Fields JSON Structure

```json
{
    "fields": [
        {
            "name": "site_title",
            "type": "text",
            "label": "Site Title",
            "value": "My Website"
        },
        {
            "name": "logo",
            "type": "media",
            "label": "Site Logo",
            "value": 123
        },
        {
            "name": "section_seo",
            "type": "section",
            "label": "SEO Settings"
        },
        {
            "name": "meta_description",
            "type": "textarea",
            "label": "Default Meta Description",
            "value": "Welcome to our website"
        }
    ]
}
```

### Scopes

```php
// Find by key
Setting::byKey('general')->first();

// Find by group
Setting::byGroup('mail')->get();
```

### Methods

```php
// Get fields as key=>value array
$values = $setting->getFieldsArray();
// Returns: ['site_title' => 'My Website', 'meta_description' => '...']

// Get single media item from collection
$logo = $setting->getMediaItem('logo');

// Clear media collection
$setting->clearMediaCollection('logo');

// Resolve media ID to URL
$url = $setting->resolveMediaValue($mediaId);
```

### Usage Examples

```php
use Monstrex\AveSite\Models\Setting;

// Get all mail settings
$mailSettings = Setting::byKey('mail')->first();
$values = $mailSettings->getFieldsArray();

$smtpHost = $values['smtp_host'] ?? 'localhost';
$smtpPort = $values['smtp_port'] ?? 587;

// Using the helper function (recommended)
$siteTitle = site_setting('general.site_title', 'Default Title');
```

---

## Localization

**Namespace:** `Monstrex\AveSite\Models\Localization`
**Table:** `ave_site_localizations`

### Database Schema

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `key` | string (unique) | Translation key |
| `en` | text (nullable) | English translation |
| `ru` | text (nullable) | Russian translation |
| ... | text | Additional locale columns |
| `created_at` | timestamp | Creation date |
| `updated_at` | timestamp | Last update date |

### Caching

Translations are cached for 24 hours (`CACHE_TTL = 24 * 60` minutes). Cache is automatically cleared when records are saved or deleted.

### Static Methods

```php
// Load all translations into Laravel translator
Localization::loadLocalizations();

// Get translations for specific locale
$translations = Localization::getLocalizedLines('en');
// Returns: ['welcome.title' => 'Welcome', 'welcome.message' => '...']
```

### Scopes

```php
// Find by key
Localization::byKey('welcome.title')->first();

// Find all with non-empty locale
Localization::byLocale('ru')->get();
```

### Usage Examples

```php
use Monstrex\AveSite\Models\Localization;

// Create translation
Localization::create([
    'key' => 'forms.submit_button',
    'en' => 'Submit',
    'ru' => 'Отправить',
]);

// Update translation
$trans = Localization::byKey('forms.submit_button')->first();
$trans->en = 'Send Message';
$trans->save();

// Use in templates (after loadLocalizations)
echo __('forms.submit_button'); // Uses Laravel translator
```

---

## HasSeoMeta Concern

**Namespace:** `Monstrex\AveSite\Models\Concerns\HasSeoMeta`

A trait that provides SEO metadata functionality to models.

### Methods

```php
// Get normalized SEO array
public function seoMeta(): array

// Accessor for attribute-style access
public function getSeoMetaAttribute(): array
```

### Return Structure

```php
[
    'seo_title' => 'string',
    'meta_description' => 'string',
    'meta_keywords' => 'string'
]
```

### Internal Methods

```php
// Normalize various input formats to array
protected function normalizeSeoMeta($value): array

// Ensure all required keys exist
protected function ensureSeoKeys(array $value): array

// Return empty SEO structure
protected function emptySeoMeta(): array
```

### Usage

```php
// In a model
class Article extends Model
{
    use HasSeoMeta;

    protected $casts = [
        'seo' => 'array', // Important: cast seo field to array
    ];
}

// Using the model
$article = Article::find(1);
$seo = $article->seoMeta();

// In Blade
<title>{{ $article->seo_meta['seo_title'] ?: $article->title }}</title>
<meta name="description" content="{{ $article->seo_meta['meta_description'] }}">
```

---

## Model Customization

To use custom model classes:

### 1. Create Custom Model

```php
// app/Models/CustomPage.php
namespace App\Models;

use Monstrex\AveSite\Models\Page as BasePage;

class CustomPage extends BasePage
{
    // Add custom methods
    public function getFullUrlAttribute(): string
    {
        return url($this->slug);
    }

    // Override existing methods
    public function scopePublished($query)
    {
        return $query->where('status', true)
                     ->where('published_at', '<=', now());
    }
}
```

### 2. Update Configuration

```php
// config/ave-site.php
'models' => [
    'page' => 'App\\Models\\CustomPage',
    // ...
],
```

## See Also

- [Services](services.md) - Service layer documentation
- [Pages](pages.md) - Page management guide
- [Blocks](blocks.md) - Block system guide
- [Forms](forms.md) - Form handling guide
