# Ave Site CMS

Site CMS extension for Ave Admin Panel

## Installation

This package is designed to work with Ave Admin Panel as a separate extension.

### Requirements

- PHP 8.2+
- Laravel 11.0+
- Ave Admin Panel 2.0+

### Installation Steps

1. Add the package to your `composer.json` repositories:

```json
"repositories": [
    {
        "type": "path",
        "url": "../ave-site.package"
    }
]
```

2. Require the package:

```bash
composer require monstrex/ave-site:@dev
```

3. Run migrations:

```bash
php artisan migrate
```

4. Publish configuration (optional):

```bash
php artisan vendor:publish --tag=ave-site-config
```

## Features

- **Pages**: Hierarchical page management with SEO
- **Blocks**: Content blocks with Liquid templates
- **Forms**: Form handling with email notifications and reCAPTCHA
- **Settings**: JSON-schema based settings management
- **Localizations**: Database-driven translations with caching
- **Redirects**: URL redirect management (301, 302, 307, 308) with hit tracking
- **Scripts**: JavaScript/CSS injection management (head, body_start, body_end)
- **DataSources**: Dynamic data loading from models
- **Liquid Engine**: Template engine with custom filters and shortcodes
- **Image Processing**: On-the-fly image manipulation, WebP conversion
- **Sitemap**: Automatic sitemap.xml generation

## Database Tables

The package creates the following tables:

- `ave_site_pages` - Hierarchical pages with SEO
- `ave_site_blocks` - Content blocks with Liquid templates
- `ave_site_block_regions` - Block regions/positions
- `ave_site_forms` - Form definitions with validation
- `ave_site_settings` - JSON-schema based site settings
- `ave_site_localizations` - Database translations
- `ave_site_redirects` - URL redirects with hit tracking
- `ave_site_scripts` - JavaScript/CSS code snippets

## Architecture

See [SITE-CMS-ARCHITECTURE.md](../../.doc/SITE-CMS-ARCHITECTURE.md) for detailed architecture documentation.

## License

MIT
