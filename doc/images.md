# Image Processing

Complete guide to image manipulation, resizing, cropping, and format conversion in Ave Site.

## Overview

Ave Site provides powerful image processing capabilities:

- On-the-fly image resizing and cropping
- Format conversion (WebP, PNG, JPEG)
- Quality control
- Automatic caching of processed images
- Support for both Blade and Liquid templates

## How It Works

1. You call an image helper with source URL and desired parameters
2. System checks if processed version exists in `cache/` directory
3. If not, creates the processed image and saves it
4. Returns URL to the processed image

Processed images are stored in:
```
storage/app/public/{original_path}/cache/{filename}-{width}x{height}.{format}
```

---

## Helper Functions

### get_image_or_create()

Main function for image processing.

```php
get_image_or_create(
    string $image_path,
    ?int $width = null,
    ?int $height = null,
    ?string $format = null,
    ?int $quality = null
): string
```

**Parameters:**
- `$image_path` - Source image path (relative URL like `/storage/...` or full URL)
- `$width` - Target width in pixels (null to auto-calculate from height)
- `$height` - Target height in pixels (null to auto-calculate from width)
- `$format` - Output format: `'webp'`, `'png'`, `'jpg'`, `'jpeg'`, `'gif'`
- `$quality` - Image quality 1-100 (default: 80)

**Returns:** URL to processed image (e.g., `/storage/files/cache/image-200x200.webp`)

### get_image_webp()

Convert image to WebP format without resizing.

```php
get_image_webp(string $image_path): string
```

### get_image_or_create_webp()

Resize and convert to WebP in one call.

```php
get_image_or_create_webp(
    string $image_path,
    ?int $width = null,
    ?int $height = null,
    ?int $quality = null
): string
```

---

## Blade Templates

### Basic Usage

```blade
{{-- Crop to exact dimensions (200x200) --}}
<img src="{{ get_image_or_create($image, 200, 200) }}" alt="">

{{-- Resize by width only (height proportional) --}}
<img src="{{ get_image_or_create($image, 400) }}" alt="">

{{-- Resize by height only (width proportional) --}}
<img src="{{ get_image_or_create($image, null, 300) }}" alt="">
```

### Format Conversion

```blade
{{-- Convert to WebP --}}
<img src="{{ get_image_or_create($image, 800, 600, 'webp') }}" alt="">

{{-- Simple WebP conversion (no resize) --}}
<img src="{{ get_image_webp($image) }}" alt="">

{{-- WebP with resize --}}
<img src="{{ get_image_or_create_webp($image, 400, 300) }}" alt="">

{{-- Convert to PNG --}}
<img src="{{ get_image_or_create($image, 800, 600, 'png') }}" alt="">
```

### Quality Control

```blade
{{-- High quality (90%) --}}
<img src="{{ get_image_or_create($image, 800, 600, 'webp', 90) }}" alt="">

{{-- Low quality for thumbnails (60%) --}}
<img src="{{ get_image_or_create($image, 100, 100, 'webp', 60) }}" alt="">

{{-- Maximum quality --}}
<img src="{{ get_image_or_create($image, 1200, 800, 'jpg', 100) }}" alt="">
```

### Direct URL Strings

```blade
{{-- From string path --}}
<img src="{{ get_image_or_create('/storage/uploads/photo.jpg', 400, 300) }}" alt="">

{{-- From full URL --}}
<img src="{{ get_image_or_create('https://example.com/storage/images/banner.jpg', 1200, 600) }}" alt="">
```

### Page Model Images

```blade
{{-- Page featured image --}}
@if($page->image)
    <img src="{{ get_image_or_create($page->image, 800, 400, 'webp') }}"
         alt="{{ $page->title }}">
@endif

{{-- Page banner with fallback --}}
<div class="hero" style="background-image: url('{{ get_image_or_create($banner ?? $page->image ?? '/images/default-banner.jpg', 1920, 600, 'webp') }}')">
    <h1>{{ $page->title }}</h1>
</div>
```

### Block Media Fields

When working with Block images (from Media library):

```blade
{{-- Single image from block --}}
@if(isset($block->images[0]))
    <img src="{{ get_image_or_create($block->images[0]['url'], 600, 400, 'webp') }}"
         alt="{{ $block->images[0]['props']['alt'] ?? $block->title }}">
@endif

{{-- Loop through block images --}}
@foreach($block->images as $image)
    <figure>
        <img src="{{ get_image_or_create($image['url'], 300, 200, 'webp') }}"
             alt="{{ $image['props']['alt'] ?? '' }}">
        @if(!empty($image['props']['title']))
            <figcaption>{{ $image['props']['title'] }}</figcaption>
        @endif
    </figure>
@endforeach
```

### Responsive Images

```blade
{{-- srcset for responsive images --}}
<img src="{{ get_image_or_create($image, 800, 600, 'webp') }}"
     srcset="{{ get_image_or_create($image, 400, 300, 'webp') }} 400w,
             {{ get_image_or_create($image, 800, 600, 'webp') }} 800w,
             {{ get_image_or_create($image, 1200, 900, 'webp') }} 1200w"
     sizes="(max-width: 400px) 400px, (max-width: 800px) 800px, 1200px"
     alt="">

{{-- Picture element with WebP fallback --}}
<picture>
    <source srcset="{{ get_image_or_create($image, 800, 600, 'webp') }}" type="image/webp">
    <source srcset="{{ get_image_or_create($image, 800, 600, 'jpg') }}" type="image/jpeg">
    <img src="{{ get_image_or_create($image, 800, 600, 'jpg') }}" alt="">
</picture>

{{-- Art direction with different crops --}}
<picture>
    {{-- Mobile: square crop --}}
    <source media="(max-width: 768px)"
            srcset="{{ get_image_or_create($image, 400, 400, 'webp') }}">
    {{-- Desktop: wide crop --}}
    <source media="(min-width: 769px)"
            srcset="{{ get_image_or_create($image, 1200, 600, 'webp') }}">
    <img src="{{ get_image_or_create($image, 1200, 600, 'jpg') }}" alt="">
</picture>
```

### Background Images

```blade
{{-- Inline style --}}
<div class="hero" style="background-image: url('{{ get_image_or_create($banner, 1920, 800, 'webp') }}')">
    ...
</div>

{{-- With CSS variable --}}
<section class="banner" style="--bg-image: url('{{ get_image_or_create($page->image, 1920, 600, 'webp') }}')">
    ...
</section>

{{-- Multiple backgrounds --}}
<div style="background-image:
    linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
    url('{{ get_image_or_create($image, 1920, 1080, 'webp') }}')">
    ...
</div>
```

### Gallery Component

```blade
{{-- Image gallery with lightbox --}}
<div class="gallery">
    @foreach($images as $image)
        <a href="{{ $image['url'] }}"
           data-lightbox="gallery"
           data-title="{{ $image['props']['title'] ?? '' }}">
            <img src="{{ get_image_or_create($image['url'], 300, 300, 'webp', 80) }}"
                 alt="{{ $image['props']['alt'] ?? '' }}"
                 loading="lazy">
        </a>
    @endforeach
</div>
```

### Lazy Loading

```blade
{{-- Native lazy loading --}}
<img src="{{ get_image_or_create($image, 400, 300, 'webp') }}"
     alt=""
     loading="lazy">

{{-- With placeholder --}}
<img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3C/svg%3E"
     data-src="{{ get_image_or_create($image, 400, 300, 'webp') }}"
     alt=""
     class="lazyload">
```

---

## Liquid Templates

### Basic Filters

```liquid
{{-- Crop to exact dimensions --}}
{{ image.url | crop: 200, 200 }}

{{-- Resize by width only --}}
{{ image.url | crop: 400 }}

{{-- Convert to WebP --}}
{{ image.url | webp }}

{{-- Crop and convert to WebP --}}
{{ image.url | crop: 800, 600, 'webp' }}

{{-- With quality --}}
{{ image.url | crop: 800, 600, 'webp', 85 }}
```

### In Block Templates

```liquid
<div class="block-content">
    {% if this.images.size > 0 %}
        {% for image in this.images %}
            <img src="{{ image.url | crop: 300, 200, 'webp' }}"
                 alt="{{ image.props.alt | default: this.title }}">
        {% endfor %}
    {% endif %}
</div>
```

### Hero Section

```liquid
<section class="hero">
    {% if this.images[0] %}
        <div class="hero-bg" style="background-image: url('{{ this.images[0].url | crop: 1920, 800, 'webp' }}')"></div>
    {% endif %}

    <div class="hero-content">
        <h1>{{ this.elements[0].title }}</h1>
        <p>{{ this.elements[0].subtitle }}</p>
    </div>
</section>
```

### Gallery Block

```liquid
<div class="gallery-grid">
    {% for image in this.images %}
        <a href="{{ image.fullUrl }}"
           data-lightbox="gallery-{{ this.id }}"
           data-title="{{ image.props.title }}">
            <img src="{{ image.url | crop: 300, 300, 'webp' }}"
                 alt="{{ image.props.alt | default: 'Gallery image' }}">
        </a>
    {% endfor %}
</div>
```

### Team Members with DataSource

```liquid
<div class="team-grid">
    {% for member in data.team %}
        <div class="team-member">
            {% if member.photo %}
                <img src="{{ member.photo | crop: 200, 200, 'webp' }}"
                     alt="{{ member.name }}"
                     class="avatar">
            {% else %}
                <img src="/images/default-avatar.jpg"
                     alt="{{ member.name }}"
                     class="avatar">
            {% endif %}

            <h3>{{ member.name }}</h3>
            <p>{{ member.position }}</p>
        </div>
    {% endfor %}
</div>
```

### Product Cards

```liquid
<div class="products">
    {% for product in data.products %}
        <article class="product-card">
            <a href="/products/{{ product.slug }}">
                {% if product.image %}
                    <img src="{{ product.image | crop: 400, 400, 'webp' }}"
                         alt="{{ product.title }}">
                {% endif %}
                <h3>{{ product.title }}</h3>
                <span class="price">{{ product.price }}</span>
            </a>
        </article>
    {% endfor %}
</div>
```

### Responsive Images in Liquid

```liquid
<img src="{{ image.url | crop: 800, 600, 'webp' }}"
     srcset="{{ image.url | crop: 400, 300, 'webp' }} 400w,
             {{ image.url | crop: 800, 600, 'webp' }} 800w,
             {{ image.url | crop: 1200, 900, 'webp' }} 1200w"
     sizes="(max-width: 400px) 400px, (max-width: 800px) 800px, 1200px"
     alt="{{ image.props.alt }}">
```

### Conditional Image Sizes

```liquid
{% if this.elements[0].layout == 'full' %}
    <img src="{{ this.images[0].url | crop: 1200, 600, 'webp' }}" alt="">
{% elsif this.elements[0].layout == 'half' %}
    <img src="{{ this.images[0].url | crop: 600, 400, 'webp' }}" alt="">
{% else %}
    <img src="{{ this.images[0].url | crop: 300, 200, 'webp' }}" alt="">
{% endif %}
```

---

## Image Data Structure

### Block Images Array

When accessing images from a Block, each image has this structure:

```php
[
    'url' => '/storage/files/media/blocks/2025/11/image.jpg',
    'fullUrl' => 'https://example.com/storage/files/media/blocks/2025/11/image.jpg',
    'path' => '/var/www/storage/app/public/files/media/blocks/2025/11/image.jpg',
    'fileName' => 'image.jpg',
    'size' => 102400,
    'mime' => 'image/jpeg',
    'order' => 1,
    'props' => [
        'alt' => 'Alternative text',
        'title' => 'Image title',
        // ... custom properties
    ]
]
```

### Accessing Image Properties

**In Blade:**
```blade
{{ $image['url'] }}
{{ $image['props']['alt'] ?? '' }}
{{ $image['props']['title'] ?? '' }}
```

**In Liquid:**
```liquid
{{ image.url }}
{{ image.props.alt }}
{{ image.props.title }}
```

---

## Shortcode

Use the `[image]` shortcode in content fields:

```html
{{-- Basic --}}
[image url="/storage/uploads/photo.jpg"]

{{-- With crop --}}
[image url="/storage/uploads/photo.jpg" crop="400,300"]

{{-- With format --}}
[image url="/storage/uploads/photo.jpg" crop="800,600" format="webp"]

{{-- With dimensions and class --}}
[image url="/storage/uploads/photo.jpg" width="800" height="600" class="responsive"]

{{-- With lightbox --}}
[image url="/storage/uploads/photo.jpg" crop="400,300" lightbox="true" lightbox_class="gallery"]
```

---

## Performance Tips

### 1. Use WebP Format

WebP provides 25-35% better compression than JPEG:

```blade
{{-- Always prefer WebP --}}
<img src="{{ get_image_or_create($image, 800, 600, 'webp') }}" alt="">
```

### 2. Appropriate Quality Settings

| Use Case | Recommended Quality |
|----------|-------------------|
| Thumbnails | 60-70 |
| Gallery images | 75-80 |
| Hero/Banner | 80-85 |
| Product photos | 85-90 |

### 3. Lazy Loading

```blade
<img src="{{ get_image_or_create($image, 400, 300, 'webp') }}"
     loading="lazy"
     alt="">
```

### 4. Specify Dimensions

Always specify width and height to prevent layout shift:

```blade
<img src="{{ get_image_or_create($image, 400, 300, 'webp') }}"
     width="400"
     height="300"
     alt="">
```

### 5. Cache Headers

Processed images are cached on disk. Configure your web server to cache `/storage/` URLs:

```nginx
location /storage/ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

---

## Troubleshooting

### Image Not Processing

1. **Check file exists:**
   ```php
   Storage::disk('public')->exists('files/media/image.jpg');
   ```

2. **Check storage link:**
   ```bash
   php artisan storage:link
   ```

3. **Check permissions:**
   ```bash
   chmod -R 775 storage/app/public
   ```

### Wrong Image Path

Input URL must start with `/storage/` or be a full URL:
```php
// Correct
get_image_or_create('/storage/uploads/photo.jpg', 400, 300);

// Correct
get_image_or_create('https://example.com/storage/uploads/photo.jpg', 400, 300);

// Wrong - missing /storage/ prefix
get_image_or_create('/uploads/photo.jpg', 400, 300);
```

### GD Extension Missing

Ensure PHP GD extension is installed:
```bash
php -m | grep gd
```

### WebP Not Working

Check WebP support in GD:
```php
gd_info()['WebP Support']; // Should be true
```

---

## See Also

- [Helpers](helpers.md) - All helper functions
- [Templating](templating.md) - Liquid template guide
- [Blocks](blocks.md) - Working with blocks and media
- [API Reference](api-reference.md) - Complete API documentation
