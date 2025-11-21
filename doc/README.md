# Ave Site CMS Package

A comprehensive Site CMS extension for the Ave Admin Panel. Provides hierarchical pages, content blocks, forms with email notifications, site settings, and dynamic localizations using Liquid templates.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Requirements](#requirements)
- [Quick Start](#quick-start)
- [Documentation](#documentation)
- [Architecture](#architecture)

## Overview

Ave Site is a modular CMS package that extends the Ave Admin Panel with essential website management features. It provides a complete solution for managing pages, reusable content blocks, contact forms, site-wide settings, and multi-language support.

The package uses Liquid template engine for content rendering, allowing flexible and secure template creation without PHP code execution.

## Features

### Pages
- Hierarchical page structure with parent-child relationships
- SEO metadata (title, description, keywords)
- Custom templates per page
- DataSources for dynamic content
- Banner image inheritance from parent pages
- Automatic breadcrumb generation

### Blocks
- Reusable content components
- Region-based organization (header, footer, sidebar, etc.)
- URL-based visibility rules (show/hide on specific pages)
- Liquid template rendering
- Media attachments and FieldSet support
- DataSource integration

### Forms
- Form builder with validation rules
- Email notifications on submission
- File attachment support
- Google reCAPTCHA v3 integration
- Custom validation messages
- AJAX and traditional form submission

### Settings
- JSON-schema based configuration
- Grouped settings organization
- Media/image field support
- Mail configuration override
- Theme and SEO defaults

### Localizations
- Database-driven translations
- Automatic loading into Laravel translator
- 24-hour caching for performance
- Multiple locale support

## Requirements

- PHP 8.2+
- Laravel 12+
- Ave Admin Panel (ave.package)
- Liquid template engine (liquid/liquid)

## Quick Start

### 1. Install the package

```bash
php artisan ave-site:install
```

This will:
- Run database migrations
- Publish configuration file
- Create admin menu items

### 2. Basic Usage

**Render a block in Blade:**
```blade
{!! render_block('footer') !!}
```

**Get a setting value:**
```php
$email = site_setting('mail.from_address');
```

**Display a menu:**
```blade
{{ menu('main-menu', 'partials.menu') }}
```

**Render a form:**
```blade
{!! render_form('contact-form') !!}
```

## Documentation

| Document | Description |
|----------|-------------|
| [Installation](installation.md) | Detailed installation and setup guide |
| [Configuration](configuration.md) | Configuration options reference |
| [Pages](pages.md) | Page management and rendering |
| [Blocks](blocks.md) | Blocks and regions system |
| [Forms](forms.md) | Form handling and validation |
| [Settings](settings.md) | Site settings management |
| [Localization](localization.md) | Multi-language support |
| [SEO](seo.md) | SEO features: breadcrumbs, sitemap, redirects, scripts |
| [Templating](templating.md) | Liquid templates and shortcodes |
| [Images](images.md) | Image processing, resizing, and optimization |
| [Models](models.md) | Database models reference |
| [Services](services.md) | Service classes documentation |
| [Helpers](helpers.md) | Global helper functions |
| [Facades](facades.md) | Facade usage guide |
| [API Reference](api-reference.md) | Complete API documentation |

## Architecture

```
ave-site.package/
├── config/
│   └── ave-site.php          # Package configuration
├── database/
│   └── migrations/           # Database migrations
├── doc/                      # Documentation
├── resources/
│   └── views/                # Blade views
├── src/
│   ├── Commands/             # Artisan commands
│   ├── Contracts/            # Interfaces
│   ├── Facades/              # Laravel facades
│   ├── Helpers/              # Global helper functions
│   ├── Http/
│   │   └── Controllers/      # HTTP controllers
│   ├── Models/               # Eloquent models
│   ├── Notifications/        # Mail notifications
│   ├── Providers/            # Service provider
│   ├── Resources/            # Ave admin resources
│   ├── Services/             # Business logic services
│   ├── Templates/            # Liquid template engine
│   └── Validators/           # Custom validators
└── tests/                    # Test suite
```

### Database Tables

| Table | Purpose |
|-------|---------|
| `ave_site_pages` | Hierarchical pages |
| `ave_site_blocks` | Content blocks |
| `ave_site_block_regions` | Block container regions |
| `ave_site_forms` | Form definitions |
| `ave_site_settings` | Site configuration |
| `ave_site_localizations` | Database translations |
| `ave_site_redirects` | URL redirects (301, 302, 307, 308) |
| `ave_site_scripts` | JavaScript/CSS code snippets |

### Service Layer

The package follows a service-oriented architecture:

- **PageService** - Page lifecycle management
- **BlockService** - Block and form rendering
- **DataService** - Data fetching and image processing
- **SiteService** - Settings management
- **LocalizationService** - Translation management
- **ModelResolver** - Model-to-array conversion for templates

## License

MIT License
