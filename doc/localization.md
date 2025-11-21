# Localization System

Complete guide to database-driven translations in Ave Site.

## Overview

The Localization system provides:

- Database-driven translations
- Automatic loading into Laravel translator
- 24-hour caching for performance
- Multiple locale support
- Admin panel management

## How It Works

1. Translations are stored in `ave_site_localizations` table
2. On application boot, translations load into Laravel's translator
3. Translations are cached for 24 hours
4. Cache clears automatically when translations are updated

---

## Localization Model

### Database Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `key` | string | Translation key (unique) |
| `en` | text | English translation |
| `ru` | text | Russian translation |
| `...` | text | Additional locale columns |

### Adding Locale Columns

To add a new language, create a migration:

```php
Schema::table('ave_site_localizations', function (Blueprint $table) {
    $table->text('de')->nullable(); // German
    $table->text('fr')->nullable(); // French
});
```

---

## Managing Translations

### Via Admin Panel

1. Go to **Content > Localizations**
2. Create or edit translations
3. Enter key and values for each locale

### Via Code

```php
use Monstrex\AveSite\Models\Localization;

// Create translation
Localization::create([
    'key' => 'messages.welcome',
    'en' => 'Welcome to our website!',
    'ru' => 'Добро пожаловать на наш сайт!',
]);

// Update translation
$trans = Localization::byKey('messages.welcome')->first();
$trans->en = 'Welcome!';
$trans->save();

// Delete translation
Localization::byKey('messages.welcome')->delete();
```

### Via Service

```php
$locService = app(LocalizationService::class);

// Set translation
$locService->set('messages.welcome', 'Welcome!', 'en');
$locService->set('messages.welcome', 'Добро пожаловать!', 'ru');

// Get translation
$welcome = $locService->get('messages.welcome', 'en');

// Delete
$locService->delete('messages.welcome');
```

---

## Using Translations

### Laravel's __() Helper

After translations load, use Laravel's standard helper:

```php
// In PHP
echo __('messages.welcome');

// With parameters
echo __('messages.greeting', ['name' => 'John']);
```

```blade
{{-- In Blade --}}
{{ __('messages.welcome') }}
{{ __('messages.greeting', ['name' => $user->name]) }}
```

### In Liquid Templates

```liquid
{{ 'messages.welcome' | lang }}
```

### Direct Model Access

```php
$trans = Localization::byKey('messages.welcome')->first();

$english = $trans->en;
$russian = $trans->ru;
```

---

## Translation Keys

### Naming Convention

Use dot notation for organization:

```
messages.welcome
messages.goodbye
forms.submit
forms.cancel
forms.validation.required
nav.home
nav.about
nav.contact
errors.not_found
errors.server_error
```

### Examples

| Key | EN | RU |
|-----|----|----|
| `site.title` | My Website | Мой сайт |
| `nav.home` | Home | Главная |
| `nav.about` | About Us | О нас |
| `forms.submit` | Submit | Отправить |
| `forms.name` | Your Name | Ваше имя |
| `messages.success` | Success! | Успех! |

---

## Service Methods

### LocalizationService

```php
$service = app(LocalizationService::class);
```

#### loadLocalizations()

Load all translations into Laravel translator.

```php
$service->loadLocalizations();
```

Called automatically by service provider.

#### get()

Get translation for key.

```php
$value = $service->get('messages.welcome', 'en');
$value = $service->get('messages.welcome'); // Current locale
```

#### set()

Store or update translation.

```php
$service->set('messages.welcome', 'Welcome!', 'en');
$service->set('messages.welcome', 'Добро пожаловать!', 'ru');
```

#### has()

Check if translation exists.

```php
if ($service->has('messages.welcome', 'en')) {
    // Translation exists
}
```

#### getByLocale()

Get all translations for locale.

```php
$translations = $service->getByLocale('en');
// Returns: ['messages.welcome' => 'Welcome!', 'nav.home' => 'Home', ...]
```

#### getAllKeys()

Get all translation keys.

```php
$keys = $service->getAllKeys();
// Returns: ['messages.welcome', 'nav.home', 'forms.submit', ...]
```

#### delete()

Delete translation.

```php
$service->delete('messages.old_key');
```

---

## Model Methods

### Localization Model

#### loadLocalizations() (Static)

Load translations into Laravel translator.

```php
Localization::loadLocalizations();
```

#### getLocalizedLines() (Static)

Get all translations for locale.

```php
$lines = Localization::getLocalizedLines('en');
```

### Scopes

```php
// Find by key
Localization::byKey('messages.welcome')->first();

// Find all with non-empty locale
Localization::byLocale('ru')->get();
```

---

## Caching

### Cache Duration

Translations are cached for 24 hours:

```php
const CACHE_TTL = 24 * 60; // 24 hours in minutes
```

### Cache Invalidation

Cache automatically clears when:
- Translation is created
- Translation is updated
- Translation is deleted

### Manual Cache Clear

```bash
php artisan cache:clear
```

Or in code:

```php
Cache::forget('ave_site_localizations_' . $locale);
```

---

## Locale Configuration

### Setting Current Locale

```php
// In middleware or controller
app()->setLocale('ru');
```

### Getting Current Locale

```php
$locale = app()->getLocale();
```

### Locale Middleware Example

```php
// app/Http/Middleware/SetLocale.php
namespace App\Http\Middleware;

use Closure;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // From session
        if ($locale = session('locale')) {
            app()->setLocale($locale);
        }

        // From URL parameter
        if ($locale = $request->get('lang')) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }

        // From browser
        if (!session('locale')) {
            $browserLocale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            if (in_array($browserLocale, ['en', 'ru'])) {
                app()->setLocale($browserLocale);
            }
        }

        return $next($request);
    }
}
```

---

## Usage Examples

### Multi-language Navigation

```blade
<nav>
    <a href="/">{{ __('nav.home') }}</a>
    <a href="/about">{{ __('nav.about') }}</a>
    <a href="/contact">{{ __('nav.contact') }}</a>
</nav>
```

### Form Labels

```blade
<form method="POST">
    <label>{{ __('forms.name') }}</label>
    <input type="text" name="name">

    <label>{{ __('forms.email') }}</label>
    <input type="email" name="email">

    <label>{{ __('forms.message') }}</label>
    <textarea name="message"></textarea>

    <button type="submit">{{ __('forms.submit') }}</button>
</form>
```

### Error Messages

```blade
@if($errors->any())
    <div class="alert alert-danger">
        <h4>{{ __('errors.validation_failed') }}</h4>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

### In Liquid Blocks

```liquid
<section class="contact">
    <h2>{{ 'contact.title' | lang }}</h2>
    <p>{{ 'contact.description' | lang }}</p>

    <div class="info">
        <p><strong>{{ 'contact.phone_label' | lang }}:</strong> {{ 'general.phone' | site_setting }}</p>
        <p><strong>{{ 'contact.email_label' | lang }}:</strong> {{ 'general.email' | site_setting }}</p>
    </div>
</section>
```

### Language Switcher

```blade
<div class="language-switcher">
    <a href="?lang=en" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
    <a href="?lang=ru" class="{{ app()->getLocale() === 'ru' ? 'active' : '' }}">RU</a>
</div>
```

---

## Integration with Laravel

### Fallback Locale

Database translations work alongside Laravel's file-based translations. If a key isn't found in database, Laravel falls back to files.

Configure in `config/app.php`:

```php
'locale' => 'en',
'fallback_locale' => 'en',
```

### Combining with File Translations

Database translations override file translations with the same key:

```
resources/lang/en/messages.php  →  Lower priority
database (ave_site_localizations)  →  Higher priority
```

---

## Best Practices

### 1. Use Meaningful Keys

```
✓ messages.welcome
✓ forms.validation.required
✓ nav.about_us
✗ msg1
✗ text
```

### 2. Group Related Keys

```
nav.*       - Navigation
forms.*     - Form labels and messages
messages.*  - User messages
errors.*    - Error messages
email.*     - Email content
```

### 3. Keep Translations Consistent

Maintain same structure across all locales:
- If key exists in English, add to all other locales
- Use placeholders consistently

### 4. Use Placeholders

```php
// In database
'greeting' => 'Hello, :name!'
'items_count' => ':count items found'

// In code
__('greeting', ['name' => $user->name])
__('items_count', ['count' => $items->count()])
```

### 5. Test All Locales

Regularly test all language versions to ensure:
- All keys have translations
- No broken placeholders
- Correct character encoding

---

## Troubleshooting

### Translations Not Loading

1. Check service provider is registered
2. Verify database table exists
3. Clear cache: `php artisan cache:clear`
4. Check locale column exists for current locale

### Wrong Translation Displayed

1. Check current locale: `app()->getLocale()`
2. Verify correct key spelling
3. Check for duplicate keys
4. Clear cache

### Characters Display Incorrectly

1. Ensure database uses `utf8mb4` charset
2. Check column collation: `utf8mb4_unicode_ci`
3. Verify HTML meta charset: `<meta charset="UTF-8">`

---

## See Also

- [Models](models.md) - Localization model reference
- [Services](services.md) - LocalizationService
- [Templating](templating.md) - Using `lang` filter
- [Configuration](configuration.md) - Package configuration
