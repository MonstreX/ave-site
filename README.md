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
- **Chunks**: Simple text/code snippets
- **Settings**: JSON-schema based settings management
- **DataSources**: Dynamic data loading from models
- **Liquid Engine**: Template engine with custom filters
- **Image Processing**: On-the-fly image manipulation
- **Forms**: Form handling with email notifications

## Database Tables

The package creates the following tables:

- `site_pages` - Hierarchical pages
- `site_block_regions` - Block regions/positions
- `site_blocks` - Content blocks
- `site_chunks` - Simple text/code snippets
- `site_settings` - Site settings

## Architecture

See [SITE-CMS-ARCHITECTURE.md](../../.doc/SITE-CMS-ARCHITECTURE.md) for detailed architecture documentation.

## License

MIT
