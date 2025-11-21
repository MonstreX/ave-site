# Blocks System

Complete guide to content blocks and regions in Ave Site.

## Overview

The Blocks system provides:

- Reusable content components
- Region-based organization
- URL-based visibility rules
- Liquid template rendering
- Media and FieldSet support
- DataSource integration

## Concepts

### Blocks

Blocks are reusable content pieces rendered using Liquid templates. They can contain:
- Static content
- Dynamic data via DataSources
- Images and media
- FieldSets (repeatable field groups)

### Regions

Regions are named areas where blocks are placed:
- `header` - Site header
- `footer` - Site footer
- `sidebar` - Sidebar area
- Custom regions as needed

---

## Creating Blocks

### Via Admin Panel

1. Go to **Content > Blocks**
2. Click **Create**
3. Fill in block details:
   - **Title** - Display name
   - **Key** - Unique identifier
   - **Region** - Where block appears
   - **Status** - Active/Inactive
   - **Order** - Sort order within region
   - **URLs** - Visibility rules
   - **Rules** - EXCEPT or ONLY mode
   - **Content** - Liquid template
   - **Details** - DataSources, validator (for forms)

---

## Block Model

### Database Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `title` | string | Block title |
| `key` | string | Unique identifier |
| `region_id` | bigint | Foreign key to region |
| `order` | int | Sort order |
| `status` | boolean | Active status |
| `urls` | text | URL patterns |
| `rules` | tinyint | 0=EXCEPT, 1=ONLY |
| `content` | longtext | Liquid template |
| `images` | json | Image collections |
| `elements` | json | Additional elements |
| `details` | json | Configuration |

---

## Regions

### Creating Regions

1. Go to **Content > Block Regions**
2. Click **Create**
3. Enter:
   - **Key** - Identifier (e.g., `header`, `footer`)
   - **Name** - Display name

### Common Regions

| Key | Purpose |
|-----|---------|
| `header` | Site header area |
| `footer` | Site footer area |
| `sidebar` | Sidebar widgets |
| `before_content` | Before main content |
| `after_content` | After main content |
| `popup` | Modal/popup content |

---

## Visibility Rules

Control where blocks appear using URL patterns.

### Rules Modes

| Mode | Value | Behavior |
|------|-------|----------|
| EXCEPT | 0 | Show on all pages EXCEPT listed URLs |
| ONLY | 1 | Show ONLY on listed URLs |

### URL Pattern Syntax

```
<front>          # Homepage only
/about           # Exact match: /about
/blog/*          # Wildcard: /blog/any-slug
/products/**     # Deep wildcard: any depth under /products
```

### Examples

**Show on all pages except contact:**
```
Rules: EXCEPT
URLs:
/contact
```

**Show only on blog pages:**
```
Rules: ONLY
URLs:
/blog
/blog/*
```

**Show everywhere except homepage:**
```
Rules: EXCEPT
URLs:
<front>
```

---

## Rendering Blocks

### Single Block

```php
// By key
echo render_block('footer');

// By ID
echo render_block(123);

// By title
echo render_block('Footer Block');
```

```blade
{!! render_block('footer') !!}
@renderBlock('footer')
```

```liquid
{{ 'footer' | block }}
```

### Region (All Blocks)

```php
echo render_region('header');
echo render_region('footer');
echo render_region('sidebar');
```

```blade
{!! render_region('header') !!}
@renderRegion('header')
@renderRegion('sidebar')
```

### With Path Override

```php
// Force visibility check for specific path
echo render_region('sidebar', '/blog');
```

---

## Block Templates

### Template Variables

| Variable | Description |
|----------|-------------|
| `this` / `block` | Current block data |
| `data` | DataSources data |

### Block Data Structure

```liquid
{{ this.id }}
{{ this.title }}
{{ this.key }}
{{ this.content }}
{{ this.status }}

{# Images collection #}
{% for image in this.images %}
    {{ image.url }}
    {{ image.fullUrl }}
    {{ image.props.alt }}
{% endfor %}

{# Elements (FieldSet data) #}
{% for element in this.elements %}
    {{ element.title }}
    {{ element.description }}
{% endfor %}

{# DataSources #}
{% for item in data.testimonials %}
    {{ item.content }}
{% endfor %}
```

---

## Template Examples

### Simple Content Block

```liquid
<div class="content-block">
    <h2>{{ this.elements[0].title }}</h2>
    <div class="content">
        {{ this.elements[0].content }}
    </div>
</div>
```

### Hero Section

```liquid
<section class="hero">
    {% if this.images[0] %}
        <div class="hero-bg" style="background-image: url('{{ this.images[0].url | crop: 1920, 800 }}')"></div>
    {% endif %}

    <div class="hero-content">
        <h1>{{ this.elements[0].title | default: 'Welcome' }}</h1>
        <p>{{ this.elements[0].subtitle }}</p>

        {% if this.elements[0].button_text %}
            <a href="{{ this.elements[0].button_url }}" class="btn btn-primary">
                {{ this.elements[0].button_text }}
            </a>
        {% endif %}
    </div>
</section>
```

### Image Gallery

```liquid
<div class="gallery">
    <h3>{{ this.elements[0].title }}</h3>

    <div class="gallery-grid">
        {% for image in this.images %}
            <a href="{{ image.fullUrl }}" data-lightbox="gallery-{{ this.id }}">
                <img src="{{ image.url | crop: 300, 300 }}"
                     alt="{{ image.props.alt | default: this.title }}">
            </a>
        {% endfor %}
    </div>
</div>
```

### Testimonials Slider

```liquid
<div class="testimonials-slider">
    {% for testimonial in data.testimonials %}
        <div class="testimonial-item">
            {% if testimonial.photo %}
                <img src="{{ testimonial.photo | crop: 80, 80 }}"
                     alt="{{ testimonial.name }}"
                     class="avatar">
            {% endif %}

            <blockquote>{{ testimonial.content }}</blockquote>

            <cite>
                <strong>{{ testimonial.name }}</strong>
                {% if testimonial.position %}
                    <span>{{ testimonial.position }}</span>
                {% endif %}
            </cite>
        </div>
    {% endfor %}
</div>
```

### Call-to-Action

```liquid
<section class="cta" style="background-color: {{ this.elements[0].bg_color | default: '#f5f5f5' }}">
    <div class="container">
        <h2>{{ this.elements[0].title }}</h2>
        <p>{{ this.elements[0].description }}</p>

        <div class="cta-buttons">
            {% if this.elements[0].primary_btn_text %}
                <a href="{{ this.elements[0].primary_btn_url }}" class="btn btn-primary">
                    {{ this.elements[0].primary_btn_text }}
                </a>
            {% endif %}

            {% if this.elements[0].secondary_btn_text %}
                <a href="{{ this.elements[0].secondary_btn_url }}" class="btn btn-secondary">
                    {{ this.elements[0].secondary_btn_text }}
                </a>
            {% endif %}
        </div>
    </div>
</section>
```

### Contact Information

```liquid
<div class="contact-info">
    <h3>{{ this.elements[0].title | default: 'Contact Us' }}</h3>

    <ul>
        {% if this.elements[0].phone %}
            <li>
                <i class="icon-phone"></i>
                <a href="tel:{{ this.elements[0].phone | remove: ' ' }}">
                    {{ this.elements[0].phone }}
                </a>
            </li>
        {% endif %}

        {% if this.elements[0].email %}
            <li>
                <i class="icon-email"></i>
                <a href="mailto:{{ this.elements[0].email }}">
                    {{ this.elements[0].email }}
                </a>
            </li>
        {% endif %}

        {% if this.elements[0].address %}
            <li>
                <i class="icon-location"></i>
                {{ this.elements[0].address }}
            </li>
        {% endif %}
    </ul>

    {% if this.elements[0].map_embed %}
        <div class="map">
            {{ this.elements[0].map_embed }}
        </div>
    {% endif %}
</div>
```

### Social Links

```liquid
<div class="social-links">
    {% for social in this.elements %}
        <a href="{{ social.url }}"
           target="_blank"
           rel="noopener"
           title="{{ social.name }}">
            <i class="{{ social.icon }}"></i>
        </a>
    {% endfor %}
</div>
```

---

## DataSources

Blocks can load dynamic data using DataSources.

### Configuration (details JSON)

```json
{
    "datasources": {
        "testimonials": {
            "model": "Testimonial",
            "where": {"status": 1},
            "limit": 5,
            "random": true
        },
        "team": {
            "model": "TeamMember",
            "order": {"field": "order", "direction": "ASC"}
        }
    }
}
```

### Using DataSources

```liquid
{# Check if data exists #}
{% if data.testimonials.size > 0 %}
    {% for item in data.testimonials %}
        <div class="item">{{ item.title }}</div>
    {% endfor %}
{% else %}
    <p>No testimonials found.</p>
{% endif %}
```

---

## Form Blocks

Blocks can function as forms by adding a `validator` in details.

### Identifying Form Blocks

```php
$block = Block::find(1);

if ($block->isForm()) {
    // This block is a form
}
```

### Form Configuration

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
    "to_address": "contact@example.com"
}
```

See [Forms](forms.md) for complete form documentation.

---

## Block Scopes

### Active Blocks

```php
Block::active()->get();
```

### Blocks in Region

```php
Block::inRegion('header')->get();
Block::inRegion('footer')->active()->get();
```

### Ordered Blocks

```php
Block::ordered()->get();
Block::active()->inRegion('sidebar')->ordered()->get();
```

---

## Programmatic Access

### Get Block Data

```php
use Monstrex\AveSite\Facades\AveBlock;

// Get block
$block = AveBlock::getByKey('hero-section');
$block = AveBlock::getByID(123);
$block = AveBlock::getByTitle('Hero Section');

// Get specific field
$title = AveBlock::getBlockField($block, 'title');
$images = AveBlock::getBlockField($block, 'images');
```

### Render Programmatically

```php
// Single block
$html = AveBlock::render('footer');

// Region
$headerHtml = AveBlock::renderRegion('header');

// Form
$formHtml = AveBlock::renderForm('contact-form', 'Contact Request');
```

---

## Best Practices

### 1. Use Descriptive Keys

```
✓ hero-homepage
✓ footer-contact-info
✓ sidebar-newsletter
✗ block1
✗ test
```

### 2. Organize by Region

Group related blocks in appropriate regions for easier management.

### 3. Use Default Values

```liquid
{{ this.elements[0].title | default: 'Untitled' }}
{{ image.props.alt | default: this.title }}
```

### 4. Check for Empty Data

```liquid
{% if this.images.size > 0 %}
    {# Render images #}
{% endif %}

{% if data.items.size > 0 %}
    {# Render items #}
{% else %}
    <p>No items available.</p>
{% endif %}
```

### 5. Optimize Images

```liquid
{# Always specify dimensions #}
<img src="{{ image.url | crop: 400, 300 }}" width="400" height="300">

{# Use WebP for better performance #}
<img src="{{ image.url | crop: 800, 600, 'webp' }}">
```

---

## See Also

- [Templating](templating.md) - Liquid template guide
- [Forms](forms.md) - Form blocks documentation
- [Models](models.md) - Block model reference
- [Services](services.md) - BlockService documentation
