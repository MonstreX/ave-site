# Templating System

Complete documentation for the Liquid template engine and shortcodes system.

## Overview

Ave Site uses the [Liquid](https://shopify.github.io/liquid/) template engine for content rendering, providing a secure, sandboxed templating environment. The package also supports shortcodes for content embedding.

## Liquid Templates

### Where Liquid is Used

- **Block content** - `content` field in blocks
- **Form templates** - `content` field in forms
- **Page content** - `content` field in pages (when rendered via BlockService)

### Basic Liquid Syntax

**Variables:**
```liquid
{{ variable }}
{{ block.title }}
{{ data.articles[0].title }}
```

**Filters:**
```liquid
{{ 'hello' | upcase }}
{{ variable | default: 'fallback' }}
{{ date | date: '%Y-%m-%d' }}
```

**Control Flow:**
```liquid
{% if condition %}
  Content
{% elsif other_condition %}
  Other content
{% else %}
  Default content
{% endif %}

{% unless condition %}
  Content when false
{% endunless %}
```

**Loops:**
```liquid
{% for item in items %}
  {{ item.title }}
{% endfor %}

{% for item in items limit: 5 offset: 2 %}
  {{ forloop.index }}: {{ item.title }}
{% endfor %}
```

---

## Available Liquid Filters

The package registers 14 custom filters:

### site_setting

Get site setting value.

```liquid
{{ 'mail.from_address' | site_setting }}
{{ 'general.site_title' | site_setting: 'Default Title' }}
```

### menu

Render navigation menu (uses global `menu()` helper from ave.package).

```liquid
{{ 'main-menu' | menu }}
{{ 'footer-menu' | menu: 'partials.footer-nav' }}
```

### block

Render another block by key.

```liquid
{{ 'footer' | block }}
{{ 'sidebar-widget' | block }}
```

### form

Render a form block.

```liquid
{{ 'contact-form' | form }}
{{ 'contact-form' | form: 'Contact Request', 'Website' }}
```

**Parameters:**
1. Subject line
2. Subject suffix

### url

Generate URL.

```liquid
{{ '/about' | url }}
{{ page.slug | url }}
```

### route

Generate route URL.

```liquid
{{ 'home' | route }}
{{ 'page.show' | route: page.slug }}
```

### webp

Convert image to WebP format.

```liquid
{{ '/storage/photo.jpg' | webp }}
{{ image.url | webp }}
```

### crop

Resize/crop image.

```liquid
{{ '/storage/photo.jpg' | crop: 400, 300 }}
{{ image.url | crop: 800, 600, 'webp', 85 }}
```

**Parameters:**
1. Width
2. Height
3. Format (optional): 'webp', 'png', 'jpg'
4. Quality (optional): 1-100

### responsive_image

Generate responsive `<img>` tag with srcset.

```liquid
{{ '/storage/photo.jpg' | responsive_image: 800, 600 }}
{{ image.url | responsive_image: 1200 }}
```

**Parameters:**
1. Width
2. Height (optional)

Generates an `<img>` tag with multiple srcset sizes for responsive images.

### responsive_picture

Generate responsive `<picture>` element with WebP and fallback.

```liquid
{{ '/storage/photo.jpg' | responsive_picture: 800, 600 }}
{{ image.url | responsive_picture: 1200 }}
```

**Parameters:**
1. Width
2. Height (optional)

Generates a `<picture>` element with WebP source and fallback image.

### breadcrumbs

Render breadcrumbs with Schema.org markup.

```liquid
{{ breadcrumbs | breadcrumbs }}
{{ breadcrumbs | breadcrumbs: '→' }}
```

**Parameters:**
1. Separator (optional, default: '/')

Renders breadcrumb navigation with structured data markup.

### lang

Translate using Laravel translator.

```liquid
{{ 'messages.welcome' | lang }}
{{ 'forms.submit' | lang }}
```

### dump

Debug variable (only when `app.debug = true`).

```liquid
{{ variable | dump }}
{{ block | dump }}
```

---

## Template Variables

### Block Templates

When rendering a block, these variables are available:

```liquid
{# Current block data #}
{{ this.title }}
{{ block.title }}    {# alias for 'this' #}

{# Block fields #}
{{ this.key }}
{{ this.content }}
{{ this.status }}

{# Media collections #}
{% for image in this.images %}
  <img src="{{ image.url }}" alt="{{ image.props.alt }}">
{% endfor %}

{# Elements (additional media/data) #}
{% for element in this.elements %}
  {{ element.title }}
{% endfor %}

{# DataSources #}
{% for article in data.articles %}
  <h2>{{ article.title }}</h2>
  <p>{{ article.excerpt }}</p>
{% endfor %}
```

### Media Object Structure

```liquid
{{ image.url }}       {# Relative URL #}
{{ image.fullUrl }}   {# Absolute URL #}
{{ image.path }}      {# File system path #}
{{ image.fileName }}  {# File name #}
{{ image.size }}      {# File size in bytes #}
{{ image.mime }}      {# MIME type #}
{{ image.order }}     {# Sort order #}
{{ image.props.alt }} {# Custom properties #}
{{ image.props.title }}
```

---

## Block Template Examples

### Hero Section

```liquid
<section class="hero" style="background-image: url('{{ this.images[0].url | crop: 1920, 800 }}')">
  <div class="container">
    <h1>{{ this.elements[0].title | default: 'Welcome' }}</h1>
    <p>{{ this.elements[0].subtitle }}</p>
    {% if this.elements[0].button_text %}
      <a href="{{ this.elements[0].button_url }}" class="btn">
        {{ this.elements[0].button_text }}
      </a>
    {% endif %}
  </div>
</section>
```

### Testimonials Slider

```liquid
<div class="testimonials-slider">
  {% for testimonial in data.testimonials %}
    <div class="testimonial">
      {% if testimonial.photo %}
        <img src="{{ testimonial.photo | crop: 100, 100 }}" alt="{{ testimonial.name }}">
      {% endif %}
      <blockquote>{{ testimonial.content }}</blockquote>
      <cite>{{ testimonial.name }}, {{ testimonial.position }}</cite>
    </div>
  {% endfor %}
</div>
```

### Image Gallery

```liquid
<div class="gallery">
  {% for image in this.images %}
    <a href="{{ image.fullUrl }}" data-lightbox="gallery">
      <img src="{{ image.url | crop: 300, 300 }}" alt="{{ image.props.alt }}">
    </a>
  {% endfor %}
</div>
```

### Contact Information

```liquid
<div class="contact-info">
  <p>
    <strong>{{ 'contact.phone_label' | lang }}:</strong>
    {{ 'general.phone' | site_setting }}
  </p>
  <p>
    <strong>{{ 'contact.email_label' | lang }}:</strong>
    <a href="mailto:{{ 'general.email' | site_setting }}">
      {{ 'general.email' | site_setting }}
    </a>
  </p>
  <p>
    <strong>{{ 'contact.address_label' | lang }}:</strong>
    {{ 'general.address' | site_setting }}
  </p>
</div>
```

---

## Shortcodes

The package provides 4 shortcodes for embedding content in rich text fields.

### [block]

Embed a block.

```html
[block name="footer"]
[block name="sidebar-widget"]
```

### [form]

Embed a form.

```html
[form name="contact-form"]
[form name="contact-form" subject="Contact Request" suffix="Website"]
```

**Attributes:**
- `name` - Form key (required)
- `subject` - Email subject
- `suffix` - Subject suffix

### [div]

Wrap content in a div with CSS class.

```html
[div class="highlight-box"]
  This content will be wrapped in a div
[/div]

[div class="two-columns"]
  <div class="column">Left</div>
  <div class="column">Right</div>
[/div]
```

### [image]

Render processed image.

```html
[image url="/storage/photos/hero.jpg"]
[image url="/storage/photos/hero.jpg" crop="800,600" format="webp"]
[image url="/storage/photos/hero.jpg" width="400" height="300" class="rounded"]
[image url="/storage/photos/hero.jpg" lightbox="true" lightbox_class="gallery"]
```

**Attributes:**
- `url` - Image URL (required unless using `field`)
- `field` - Get URL from block field
- `index` - Index for array fields
- `width` - Target width
- `height` - Target height
- `crop` - Shorthand: "width,height"
- `format` - Output format: webp, png, jpg
- `class` - CSS class
- `lightbox` - Enable lightbox ("true")
- `lightbox_class` - Lightbox group class
- `picture` - Use picture element ("true")

**Examples:**

```html
<!-- Simple resize -->
[image url="/storage/photo.jpg" width="800"]

<!-- Crop with WebP -->
[image url="/storage/photo.jpg" crop="400,300" format="webp"]

<!-- With lightbox -->
[image url="/storage/photo.jpg" crop="300,300" lightbox="true" lightbox_class="product-gallery"]

<!-- From block field -->
[image field="main_image" crop="600,400"]
```

---

## Custom Liquid Filters

You can add custom Liquid filters by creating a filter class:

### 1. Create Filter Class

```php
// app/Templates/CustomFilters.php
namespace App\Templates;

use Liquid\Template;

class CustomFilters
{
    public function handle(Template $template, string $content): void
    {
        // Uppercase filter
        $template->registerFilter('uppercase', function ($value) {
            return strtoupper($value);
        });

        // Format price
        $template->registerFilter('price', function ($value, $currency = '$') {
            return $currency . number_format((float)$value, 2);
        });

        // Truncate with ellipsis
        $template->registerFilter('truncate_words', function ($value, $words = 20) {
            $arr = explode(' ', $value);
            if (count($arr) <= $words) {
                return $value;
            }
            return implode(' ', array_slice($arr, 0, $words)) . '...';
        });

        // Date formatting
        $template->registerFilter('format_date', function ($value, $format = 'F j, Y') {
            return date($format, strtotime($value));
        });
    }
}
```

### 2. Register in Configuration

```php
// config/ave-site.php
'template_filters' => App\Templates\CustomFilters::class,
```

### 3. Use in Templates

```liquid
{{ product.price | price: '€' }}
{{ article.content | truncate_words: 50 }}
{{ article.created_at | format_date: 'd.m.Y' }}
{{ title | uppercase }}
```

---

## Blade Integration

### Blade Directives

```blade
{{-- Render block --}}
@renderBlock('footer')

{{-- Render region --}}
@renderRegion('header')
@renderRegion('sidebar')

{{-- Render form --}}
@renderForm('contact-form')
@renderForm('contact-form', 'Subject', 'Suffix')
```

### Helper Functions in Blade

```blade
{{-- Blocks --}}
{!! render_block('footer') !!}
{!! render_region('header') !!}
{!! render_form('contact-form') !!}

{{-- Images --}}
<img src="{{ get_image_or_create($image, 800, 600, 'webp') }}" alt="">
<img src="{{ get_image_webp($image) }}" alt="">

{{-- Settings --}}
<title>{{ site_setting('general.site_title') }}</title>

{{-- Menu --}}
{{ menu('main-menu', 'partials.main-nav') }}
```

---

## Best Practices

### 1. Keep Templates Simple

```liquid
{# Good: Simple, readable #}
{% for item in data.items %}
  <div class="item">{{ item.title }}</div>
{% endfor %}

{# Bad: Complex logic in template #}
{% for item in data.items %}
  {% if item.status == 1 and item.featured == true and item.date > now %}
    ...complex nested logic...
  {% endif %}
{% endfor %}
```

### 2. Use Default Values

```liquid
{{ title | default: 'Untitled' }}
{{ image.props.alt | default: title }}
{{ 'seo.meta_description' | site_setting | default: 'Default description' }}
```

### 3. Check for Empty Collections

```liquid
{% if data.articles.size > 0 %}
  {% for article in data.articles %}
    ...
  {% endfor %}
{% else %}
  <p>No articles found.</p>
{% endif %}
```

### 4. Use Proper Image Processing

```liquid
{# Always specify dimensions for layout stability #}
<img src="{{ image.url | crop: 400, 300 }}" width="400" height="300" alt="">

{# Use WebP for better performance #}
<img src="{{ image.url | crop: 800, 600, 'webp' }}" alt="">
```

### 5. Escape User Content

Liquid automatically escapes output. For raw HTML:

```liquid
{{ content }}              {# Escaped (safe) #}
{{ content | raw }}        {# Unescaped (use with caution) #}
```

---

## Debugging

### Using dump Filter

```liquid
{{ this | dump }}
{{ data | dump }}
{{ variable | dump }}
```

Only works when `app.debug = true` in Laravel.

### Common Issues

**Variable not found:**
```liquid
{# Check variable exists #}
{% if variable %}
  {{ variable }}
{% else %}
  Variable not set
{% endif %}
```

**Loop not working:**
```liquid
{# Ensure it's an array #}
{% if items.size > 0 %}
  {% for item in items %}
    {{ item }}
  {% endfor %}
{% endif %}
```

---

## See Also

- [Images](images.md) - Image processing and optimization
- [Blocks](blocks.md) - Block system documentation
- [Forms](forms.md) - Form handling guide
- [Helpers](helpers.md) - Helper functions reference
