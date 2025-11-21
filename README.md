# Ave Site CMS

Site CMS extension for Ave Admin Panel

## Installation

This package is designed to work with Ave Admin Panel as a separate extension.

### Requirements

- PHP 8.2+
- Laravel 11.0 or 12.0+
- **Ave Admin Panel 2.0+** (required dependency)

### Quick Installation

The easiest way to install Ave Site CMS is using the installation command:

```bash
# 1. Install Ave Admin Panel first (if not installed)
composer require monstrex/ave

# 2. Install Ave Site CMS
composer require monstrex/ave-site

# 3. Run the installation command
php artisan ave-site:install
```

The installation command will:
- Check if Ave Admin Panel is installed
- Run database migrations
- Publish configuration file
- Create admin menu items
- Optionally publish views

### Manual Installation

If you prefer manual installation:

```bash
# 1. Install the package
composer require monstrex/ave-site

# 2. Run migrations
php artisan migrate

# 3. Publish configuration
php artisan vendor:publish --tag=ave-site-config

# 4. (Optional) Publish views
php artisan vendor:publish --tag=ave-site-views
```

### Development Installation

For local development with path repository:

```json
"repositories": [
    {
        "type": "path",
        "url": "../ave-site.package",
        "options": {
            "symlink": false
        }
    }
]
```

Then run: `composer require monstrex/ave-site:@dev`

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
