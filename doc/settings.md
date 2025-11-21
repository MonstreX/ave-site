# Settings System

Complete guide to site settings management in Ave Site.

## Overview

The Settings system provides:

- JSON-schema based configuration
- Grouped settings organization
- Multiple field types support
- Media/image field handling
- Mail configuration override
- Runtime settings access

## Settings Model

### Database Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `key` | string | Setting key (unique) |
| `group` | string | Settings group |
| `title` | string | Display title |
| `order` | int | Sort order |
| `fields` | json | Field definitions and values |

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
            "value": null
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
            "value": ""
        }
    ]
}
```

---

## Field Types

| Type | Description | Value Type |
|------|-------------|------------|
| `text` | Single line text | string |
| `textarea` | Multi-line text | string |
| `number` | Numeric input | number |
| `checkbox` | Boolean toggle | boolean |
| `radio` | Radio buttons | string |
| `dropdown` | Select menu | string |
| `media` | Media file upload | int (media ID) |
| `image` | Image upload | string (path) |
| `code_editor` | Code/JSON editor | string |
| `rich_text_box` | WYSIWYG editor | string |
| `section` | Visual divider | none |

---

## Creating Settings

### Via Admin Panel

Settings are typically created during installation. To add new settings:

1. Go to **Site Settings** in Admin Panel
2. Use the settings editor interface
3. Or insert directly into database

### Settings Groups

Common setting groups:

| Group | Purpose |
|-------|---------|
| `general` | Site info, contact details |
| `seo` | SEO defaults |
| `mail` | Email configuration |
| `theme` | Theme/appearance options |
| `social` | Social media links |

---

## Accessing Settings

### Using Helper Functions

```php
// Single value
$title = site_setting('general.site_title');
$email = site_setting('mail.from_address', 'default@example.com');

// Entire group
$mailSettings = site_settings_group('mail');
// Returns: ['smtp_host' => '...', 'smtp_port' => '...', ...]
```

### Using Facade

```php
use Monstrex\AveSite\Facades\AveSite;

$title = AveSite::setting('general.site_title');
$settings = AveSite::getSettings();
```

### Using Service

```php
$settingsService = app(SettingsService::class);

$title = $settingsService->get('general.site_title');
$mailGroup = $settingsService->getGroup('mail');
```

### Using Model Directly

```php
use Monstrex\AveSite\Models\Setting;

$setting = Setting::byKey('general')->first();
$values = $setting->getFieldsArray();

$title = $values['site_title'];
```

---

## In Templates

### Blade

```blade
<title>{{ site_setting('general.site_title') }}</title>

<footer>
    <p>Phone: {{ site_setting('general.phone') }}</p>
    <p>Email: {{ site_setting('general.email') }}</p>
</footer>
```

### Liquid

```liquid
<title>{{ 'general.site_title' | site_setting }}</title>

<footer>
    <p>Phone: {{ 'general.phone' | site_setting }}</p>
    <p>Email: {{ 'general.email' | site_setting }}</p>
</footer>
```

---

## Common Settings

### General Settings

| Key | Purpose |
|-----|---------|
| `general.site_title` | Site name |
| `general.site_description` | Site tagline |
| `general.phone` | Contact phone |
| `general.email` | Contact email |
| `general.address` | Physical address |
| `general.logo` | Site logo (media) |
| `general.favicon` | Favicon (media) |
| `general.default_banner` | Default banner image |

### SEO Settings

| Key | Purpose |
|-----|---------|
| `seo.seo_title` | Default SEO title |
| `seo.seo_title_template` | Title template (e.g., `%s | Site`) |
| `seo.meta_description` | Default meta description |
| `seo.meta_keywords` | Default meta keywords |
| `seo.og_image` | Default Open Graph image |

### Mail Settings

| Key | Purpose |
|-----|---------|
| `mail.driver` | Mail driver (smtp, sendmail) |
| `mail.host` | SMTP server |
| `mail.port` | SMTP port |
| `mail.username` | SMTP username |
| `mail.password` | SMTP password |
| `mail.encryption` | Encryption (tls, ssl) |
| `mail.from_address` | Sender email |
| `mail.from_name` | Sender name |

### reCAPTCHA Settings

| Key | Purpose |
|-----|---------|
| `general.site_captcha_site_key` | Public site key |
| `general.site_captcha_secret_key` | Secret key |

---

## Mail Configuration

### How It Works

The package automatically overrides Laravel's mail configuration with database settings:

```php
// In AveSiteServiceProvider boot()
$mailSettings = site_settings_group('mail');

if (!empty($mailSettings)) {
    config([
        'mail.default' => $mailSettings['driver'] ?? config('mail.default'),
        'mail.mailers.smtp.host' => $mailSettings['host'] ?? config('mail.mailers.smtp.host'),
        'mail.mailers.smtp.port' => $mailSettings['port'] ?? config('mail.mailers.smtp.port'),
        // ... etc
    ]);
}
```

### Testing Mail

1. Go to **Site Settings** in admin panel
2. Configure mail settings
3. Click **Test Mail** button
4. Enter recipient email
5. Check inbox for test message

---

## Media Fields

### Getting Media

```php
$setting = Setting::byKey('general')->first();

// Get single media item
$logo = $setting->getMediaItem('logo');
if ($logo) {
    $logoUrl = $logo->getUrl();
}

// Resolve media value to URL
$mediaId = $setting->fields['logo']['value'];
$url = $setting->resolveMediaValue($mediaId);
```

### In Templates

```blade
@php
    $logoMedia = Setting::byKey('general')->first()->getMediaItem('logo');
@endphp

@if($logoMedia)
    <img src="{{ $logoMedia->getUrl() }}" alt="Logo">
@endif
```

### Clear Media

```php
$setting->clearMediaCollection('logo');
```

---

## Creating Custom Settings

### 1. Create Migration (Optional)

If adding a new settings group:

```php
// database/migrations/xxxx_create_custom_settings.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCustomSettings extends Migration
{
    public function up()
    {
        DB::table('ave_site_settings')->insert([
            'key' => 'custom',
            'group' => 'custom',
            'title' => 'Custom Settings',
            'order' => 100,
            'fields' => json_encode([
                'fields' => [
                    [
                        'name' => 'custom_field',
                        'type' => 'text',
                        'label' => 'Custom Field',
                        'value' => ''
                    ],
                    // ... more fields
                ]
            ])
        ]);
    }
}
```

### 2. Access in Code

```php
$customValue = site_setting('custom.custom_field');
```

---

## Settings Controller

The package provides a settings controller at:

```
/admin/site-settings/{key}/edit
```

### Routes

| Method | URL | Purpose |
|--------|-----|---------|
| GET | `/admin/site-settings/{key}/edit` | Edit settings form |
| POST | `/admin/site-settings/{key}` | Update settings |
| POST | `/admin/site-settings/test-mail` | Send test email |

---

## Field Configuration Examples

### Text Field

```json
{
    "name": "site_title",
    "type": "text",
    "label": "Site Title",
    "value": "My Website"
}
```

### Textarea

```json
{
    "name": "description",
    "type": "textarea",
    "label": "Site Description",
    "value": ""
}
```

### Checkbox

```json
{
    "name": "maintenance_mode",
    "type": "checkbox",
    "label": "Enable Maintenance Mode",
    "value": false
}
```

### Dropdown

```json
{
    "name": "theme",
    "type": "dropdown",
    "label": "Color Theme",
    "options": [
        {"value": "light", "label": "Light"},
        {"value": "dark", "label": "Dark"},
        {"value": "auto", "label": "Auto"}
    ],
    "value": "light"
}
```

### Media Field

```json
{
    "name": "logo",
    "type": "media",
    "label": "Site Logo",
    "value": null
}
```

### Section Divider

```json
{
    "name": "section_social",
    "type": "section",
    "label": "Social Media"
}
```

---

## Best Practices

### 1. Group Related Settings

Organize settings into logical groups:
- `general` - Basic site info
- `seo` - SEO configuration
- `mail` - Email settings
- `social` - Social links
- `theme` - Appearance options

### 2. Use Meaningful Keys

```
✓ general.site_title
✓ mail.smtp_host
✓ seo.meta_description
✗ setting1
✗ value
```

### 3. Provide Defaults

```php
$title = site_setting('general.site_title', 'Default Title');
$email = site_setting('mail.from_address', 'noreply@example.com');
```

### 4. Cache Settings

Settings are read frequently. The package caches internally, but for heavy use:

```php
// Cache in config at boot
config(['site.title' => site_setting('general.site_title')]);

// Use throughout app
$title = config('site.title');
```

### 5. Validate Before Save

Add validation in your settings form or use Ave Resource field validation.

---

## Troubleshooting

### Settings Not Loading

1. Check database table exists:
   ```sql
   SELECT * FROM ave_site_settings;
   ```

2. Clear cache:
   ```bash
   php artisan cache:clear
   ```

3. Verify key format:
   ```php
   // Correct
   site_setting('general.site_title');

   // Wrong
   site_setting('site_title');
   site_setting('general/site_title');
   ```

### Mail Not Working

1. Check mail settings in admin panel
2. Use Test Mail feature
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify SMTP credentials

### Media Not Displaying

1. Ensure media was uploaded successfully
2. Check storage link: `php artisan storage:link`
3. Verify media ID in fields JSON

---

## See Also

- [Models](models.md) - Setting model reference
- [Services](services.md) - SiteService, SettingsService
- [Forms](forms.md) - Form email configuration
- [Installation](installation.md) - Initial setup
