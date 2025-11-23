# Installation Guide

This guide covers the installation and initial setup of the Ave Site CMS package.

## Prerequisites

Before installing Ave Site, ensure you have:

- PHP 8.2 or higher
- Laravel 12 or higher
- Ave Admin Panel (ave.package) installed and configured
- Composer package manager
- Database connection configured

## Installation Steps

### Step 1: Package Installation

The package is typically installed via Composer as a local repository. Add the package to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../ave-site.package"
        }
    ],
    "require": {
        "monstrex/ave-site": "*"
    }
}
```

Then run:

```bash
composer update monstrex/ave-site
```

### Step 2: Run the Install Command

Execute the installation command:

```bash
php artisan ave-site:install
```

This command performs the following actions:

1. **Runs migrations** - Creates all required database tables:
   - `ave_site_pages` - Hierarchical pages
   - `ave_site_blocks` - Content blocks
   - `ave_site_block_regions` - Block regions/areas
   - `ave_site_forms` - Form definitions
   - `ave_site_settings` - Site configuration
   - `ave_site_localizations` - Database translations

2. **Publishes configuration** - Copies `ave-site.php` to your `config/` directory

3. **Creates admin menu items** - Adds the following items to Ave Admin Panel:
   - **Content** (group)
     - Pages
     - Blocks
     - Block Regions
     - Forms
     - Localizations
   - **Site Settings**

4. **Optional: Publish views** - You can choose to publish views for customization

### Step 3: Verify Installation

After installation, verify that:

1. New menu items appear in the Ave Admin Panel sidebar
2. Database tables are created (check your database)
3. Configuration file exists at `config/ave-site.php`

## Manual Installation

If you need more control over the installation process:

### Run Migrations Only

```bash
php artisan migrate
```

### Publish Configuration

```bash
php artisan vendor:publish --tag=ave-site-config
```

### Publish Views (Optional)

```bash
php artisan vendor:publish --tag=ave-site-views
```

This copies views to `resources/views/vendor/ave-site/` for customization.

### Force Reinstall

To force overwrite existing files:

```bash
php artisan ave-site:install --force
```

## Post-Installation Setup

### 1. Create Block Regions

Before creating blocks, define regions where blocks will be placed:

1. Go to **Content > Block Regions** in admin panel
2. Create regions like:
   - `header` - Header area
   - `footer` - Footer area
   - `sidebar` - Sidebar area

### 2. Configure Mail Settings

To enable form email notifications:

1. Go to **Site Settings** in admin panel
2. Navigate to **Mail** settings group
3. Configure SMTP settings:
   - `mail.driver` - smtp, sendmail, etc.
   - `mail.host` - SMTP server
   - `mail.port` - SMTP port
   - `mail.username` - SMTP username
   - `mail.password` - SMTP password
   - `mail.from_address` - Sender email
   - `mail.from_name` - Sender name

4. Use **Test Mail** button to verify configuration

### 3. Configure SEO Defaults

1. Go to **Site Settings**
2. Navigate to **SEO** settings group
3. Set default values:
   - `seo.site_title` - Site name
   - `seo.seo_title_template` - Title template (e.g., `%s | Site Name`)
   - `seo.meta_description` - Default meta description
   - `seo.meta_keywords` - Default meta keywords

### 4. Set Up Templates

Configure your Blade templates in `config/ave-site.php`:

```php
'template' => 'template',           // Template namespace
'template_master' => 'layouts.master',  // Master layout
'template_layout' => 'layouts.main',    // Main layout
'template_page' => 'pages.page',        // Page template
```

Create corresponding Blade files in your `resources/views/` directory.

## Troubleshooting

### Menu Items Not Appearing

If admin menu items don't appear:

1. Clear application cache:
   ```bash
   php artisan cache:clear
   ```

2. Re-run install command:
   ```bash
   php artisan ave-site:install --force
   ```

### Migration Errors

If migrations fail:

1. Check database connection in `.env`
2. Ensure `ave_*` tables from ave.package exist
3. Run migrations manually:
   ```bash
   php artisan migrate --path=vendor/monstrex/ave-site/database/migrations
   ```

### Views Not Found

If views are not found:

1. Clear view cache:
   ```bash
   php artisan view:clear
   ```

2. Verify package service provider is loaded in `config/app.php`

### Service Provider Not Loaded

Add to `config/app.php` providers array (usually auto-discovered):

```php
'providers' => [
    // ...
    Monstrex\AveSite\Providers\AveSiteServiceProvider::class,
],
```

## Uninstallation

1. Run the uninstall command to clean database tables, migration records, config and menu entries:
   ```bash
   php artisan ave-site:uninstall
   ```
   Useful options:
   - `--dry-run` - preview actions without changing anything
   - `--keep-config`, `--keep-views`, `--keep-menu` - skip deleting specific resources
   - `--force` - skip confirmation prompts
2. Remove the dependency from `composer.json` and run `composer update`
3. Clear caches:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```
4. Delete any custom Ave Site resources or overrides you published manually (e.g. `app/AveSite`, `resources/views/vendor/ave-site`)

## Next Steps

After installation:

1. Read [Configuration](configuration.md) to customize package behavior
2. Learn about [Pages](pages.md) to create your first page
3. Explore [Blocks](blocks.md) to create reusable content
4. Set up [Forms](forms.md) for contact forms
