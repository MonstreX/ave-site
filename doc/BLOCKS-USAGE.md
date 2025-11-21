# Block System Usage Guide

## Overview

Ave Site CMS provides a flexible block system with Liquid template engine integration, DataSource mechanism for dynamic content, and Elements fieldset for rich media galleries.

## Block Structure

### Database Fields

- **title** - Block title (for admin panel)
- **key** - Unique identifier (used in helpers)
- **region_id** - Block region ID
- **order** - Sort order within region
- **status** - Published status (boolean)
- **urls** - URL visibility rules (one per line)
- **rules** - Display rule: `0` = EXCEPT, `1` = ONLY
- **content** - Liquid template content
- **elements** - JSON array of media elements (NEW!)
- **options** - JSON configuration for DataSources

## Elements FieldSet

The `elements` field replaces the old Voyager `images` field with a more powerful structure:

### Element Structure

```json
[
    {
        "image": "/storage/blocks/element.jpg",
        "alt": "Image alt text",
        "title": "Element Title",
        "subtitle": "Element subtitle",
        "link": "/projects/project-1",
        "html": "<div class=\"custom\">Custom HTML</div>"
    }
]
```

### Admin Panel Features

- **Card Preview Mode** - Shows title and image thumbnail
- **Drag & Drop Sorting** - Reorder elements easily
- **Media Upload** - Direct image upload with collection `block_elements`
- **Validation** - Title is required
- **Limits** - Min: 0, Max: 50 elements

## Usage in Liquid Templates

### Basic Elements Loop

```liquid
{% for element in block.elements %}
  <div class="element-card">
    <img src="{{ element.image }}" alt="{{ element.alt }}" title="{{ element.title }}">
    <h3>{{ element.title }}</h3>
    <p class="subtitle">{{ element.subtitle }}</p>
    {% if element.link %}
      <a href="{{ element.link }}" class="btn">Read More</a>
    {% endif %}
  </div>
{% endfor %}
```

### Gallery with Image Resize

```liquid
<div class="image-gallery">
  {% for element in block.elements %}
    <div class="gallery-item">
      <a href="{{ element.image }}" data-gallery>
        <img src="{{ element.image | image: 400, 300 }}"
             alt="{{ element.alt }}"
             title="{{ element.title }}">
      </a>
      <p class="caption">{{ element.subtitle }}</p>
    </div>
  {% endfor %}
</div>
```

### Custom HTML Elements

```liquid
{% for element in block.elements %}
  {{ element.html }}
{% endfor %}
```

## DataSource Integration

### Configure DataSource in Options

```json
{
    "datasources": {
        "projects": {
            "model": "App\\Models\\Project",
            "where": {
                "status": 1,
                "featured": true
            },
            "order": {
                "field": "created_at",
                "direction": "DESC"
            },
            "limit": 6
        }
    }
}
```

### Use in Template

```liquid
<div class="projects-grid">
  {% for project in projects %}
    <div class="project-card">
      <h3>{{ project.title }}</h3>
      <p>{{ project.description }}</p>
      <a href="/projects/{{ project.slug }}">View Project</a>
    </div>
  {% endfor %}
</div>
```

## Visibility Rules

### Show on All Pages EXCEPT

Set `rules = 0` (EXCEPT) and list URLs:

```
home
about
contact/*
```

Block will show everywhere EXCEPT: `/`, `/about`, `/contact/*`

### Show ONLY on Specific Pages

Set `rules = 1` (ONLY) and list URLs:

```
<front>
projects/*
blog/*
```

Block will show ONLY on: `/`, `/projects/*`, `/blog/*`

## Rendering Helpers

### Render Block by Key

```php
{!! render_block('main-header') !!}
```

### Render Block Region

```php
{!! render_region('sidebar') !!}
```

### Render in Liquid Template

```liquid
{{ "main-header" | render_block }}
{{ "sidebar" | render_region }}
```

## Block Variables in Liquid

Available variables inside block content:

```liquid
{{ block.id }}          - Block ID
{{ block.key }}         - Block key
{{ block.title }}       - Block title
{{ block.content }}     - Raw content (not recommended)
{{ block.elements }}    - Elements array
{{ block.options }}     - Options object
```

## Example: Image Slider Block

### Block Content

```liquid
<div class="slider">
  {% for slide in block.elements %}
    <div class="slide">
      <img src="{{ slide.image | image: 1200, 600, 'webp' }}"
           alt="{{ slide.alt }}">
      <div class="slide-caption">
        <h2>{{ slide.title }}</h2>
        <p>{{ slide.subtitle }}</p>
        {% if slide.link %}
          <a href="{{ slide.link }}" class="btn btn-primary">Learn More</a>
        {% endif %}
      </div>
    </div>
  {% endfor %}
</div>

<script>
  // Initialize slider
  new TinySlider({
    container: '.slider',
    items: 1,
    slideBy: 'page',
    autoplay: true
  });
</script>
```

## Example: Team Members Block

### Block Elements

```json
[
    {
        "image": "/storage/team/john.jpg",
        "alt": "John Doe",
        "title": "John Doe",
        "subtitle": "CEO & Founder",
        "link": "mailto:john@example.com",
        "html": "<p>Expert in web development with 15+ years experience.</p>"
    },
    {
        "image": "/storage/team/jane.jpg",
        "alt": "Jane Smith",
        "title": "Jane Smith",
        "subtitle": "Lead Designer",
        "link": "mailto:jane@example.com",
        "html": "<p>Award-winning designer passionate about UX.</p>"
    }
]
```

### Block Content

```liquid
<div class="team-grid">
  {% for member in block.elements %}
    <div class="team-member">
      <div class="member-photo">
        <img src="{{ member.image | image: 300, 300 }}"
             alt="{{ member.alt }}">
      </div>
      <h3 class="member-name">{{ member.title }}</h3>
      <p class="member-role">{{ member.subtitle }}</p>
      {{ member.html }}
      {% if member.link %}
        <a href="{{ member.link }}" class="contact-link">Contact</a>
      {% endif %}
    </div>
  {% endfor %}
</div>
```

## Migration Guide: media → elements

If you have existing blocks with `media` field, the migration will automatically rename the column. Update your Liquid templates:

### Before (old Voyager style)

```liquid
{% for image in block.media %}
  <img src="{{ image }}">
{% endfor %}
```

### After (new elements structure)

```liquid
{% for element in block.elements %}
  <img src="{{ element.image }}" alt="{{ element.alt }}" title="{{ element.title }}">
  <h3>{{ element.title }}</h3>
{% endfor %}
```

## Best Practices

1. **Always provide alt text** for images (accessibility)
2. **Use title field** for element identification in admin panel
3. **Use subtitle** for captions or short descriptions
4. **Use link field** for CTAs and navigation
5. **Use html field** for custom markup when needed
6. **Optimize images** before upload (max 5MB recommended)
7. **Use image filter** for responsive images: `{{ image | image: width, height, format }}`

## Advanced: Custom Filters

Create custom Liquid filters in your template filters class:

```php
namespace App\AveSite\Filters;

class CustomFilters
{
    public function thumbnail($input, $size = 'medium')
    {
        $sizes = [
            'small' => [200, 200],
            'medium' => [400, 400],
            'large' => [800, 800],
        ];

        [$width, $height] = $sizes[$size] ?? $sizes['medium'];

        return app(\Monstrex\AveSite\Services\DataService::class)
            ->getImageOrCreate($input, $width, $height, 'webp', 85);
    }
}
```

Register in `config/ave-site.php`:

```php
'template_filters' => App\AveSite\Filters\CustomFilters::class,
```

Use in templates:

```liquid
<img src="{{ element.image | thumbnail: 'large' }}" alt="{{ element.alt }}">
```
