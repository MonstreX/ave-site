<?php

namespace Monstrex\AveSite\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Monstrex\AveSite\Commands\InstallCommand;
use Monstrex\AveSite\Models\Setting;
use Monstrex\AveSite\Services\{
    DataService,
    SiteService,
    PageService,
    BlockService,
    SettingsService,
    LocalizationService
};
use Monstrex\AveSite\Facades;

class AveSiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../config/ave-site.php', 'ave-site');

        // Register services as singletons
        $this->app->singleton(DataService::class);
        $this->app->singleton(SiteService::class);
        $this->app->singleton(PageService::class);
        $this->app->singleton(BlockService::class);
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(LocalizationService::class);

        // Register facades
        $this->registerAliases();
    }

    public function boot(): void
    {
        // Migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'ave-site');

        // Translations
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'ave-site');

        // Register Ave Resources
        if (!$this->app->runningInConsole()) {
            $this->registerAveResources();
            $this->registerRoutes();
            $this->registerBreadcrumbs();
        }

        // Load localizations from database
        if (!$this->app->runningInConsole()) {
            $this->loadLocalizations();
        }

        // Override Laravel config from database settings
        if (!$this->app->runningInConsole()) {
            $this->overrideConfig();
        }

        // Register shortcodes
        $this->registerShortcodes();

        // Register Blade directives
        $this->registerBlades();

        // Publishable resources
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/ave-site.php' => config_path('ave-site.php'),
            ], 'ave-site-config');

            // Register commands
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    protected function registerAveResources(): void
    {
        try {
            $resourceManager = app(\Monstrex\Ave\Core\ResourceManager::class);

            $resources = [
                \Monstrex\AveSite\Resources\Page\Resource::class,
                \Monstrex\AveSite\Resources\Block\Resource::class,
                \Monstrex\AveSite\Resources\BlockRegion\Resource::class,
                \Monstrex\AveSite\Resources\Localization\Resource::class,
                \Monstrex\AveSite\Resources\Setting\Resource::class,
            ];

            foreach ($resources as $resourceClass) {
                $resourceManager->register($resourceClass);
            }
        } catch (\Exception $e) {
            // ResourceManager not available (Ave not loaded)
        }
    }

    protected function registerRoutes(): void
    {
        $settingsController = 'Monstrex\AveSite\Http\Controllers\SettingsController';

        \Route::middleware(['web', \Monstrex\Ave\Http\Middleware\SetLocaleMiddleware::class])->group(function () use ($settingsController) {
            // Test mail route MUST be before {key} route to match correctly
            \Route::get('/admin/site-settings/test-mail', $settingsController.'@testMail')
                ->name('ave-site.settings.test-mail');
            \Route::get('/admin/site-settings/{key}/edit', $settingsController.'@edit')
                ->name('ave-site.settings.edit');
            \Route::put('/admin/site-settings/{key}', $settingsController.'@update')
                ->name('ave-site.settings.update');
        });
    }

    /**
     * Register Facades
     */
    protected function registerAliases(): void
    {
        $aliases = [
            'AveSite' => [
                'Facade' => Facades\AveSite::class,
                'Alias' => 'ave-site',
                'Class' => app(SiteService::class),
            ],
            'AveData' => [
                'Facade' => Facades\AveData::class,
                'Alias' => 'ave-data',
                'Class' => app(DataService::class),
            ],
            'AvePage' => [
                'Facade' => Facades\AvePage::class,
                'Alias' => 'ave-page',
                'Class' => app(PageService::class),
            ],
            'AveBlock' => [
                'Facade' => Facades\AveBlock::class,
                'Alias' => 'ave-block',
                'Class' => app(BlockService::class),
            ],
        ];

        $loader = AliasLoader::getInstance();
        foreach ($aliases as $key => $alias) {
            $loader->alias($key, $alias['Facade']);
            $this->app->singleton($alias['Alias'], function () use($alias) {
                return $alias['Class'];
            });
        }

        // Register Shortcode facade alias if package is installed
        if (class_exists(\Webwizo\Shortcodes\Facades\Shortcode::class)) {
            $this->app->booting(function() {
                $loader = AliasLoader::getInstance();
                $loader->alias('Shortcode', \Webwizo\Shortcodes\Facades\Shortcode::class);
            });
        }
    }

    /**
     * Register Shortcodes
     */
    protected function registerShortcodes(): void
    {
        // Only register if shortcodes package is installed
        if (!class_exists(\Webwizo\Shortcodes\Facades\Shortcode::class)) {
            return;
        }

        \Shortcode::register('block', 'Monstrex\AveSite\Templates\CustomShortcodes@block');
        \Shortcode::register('form', 'Monstrex\AveSite\Templates\CustomShortcodes@form');
        \Shortcode::register('div', 'Monstrex\AveSite\Templates\CustomShortcodes@div');
        \Shortcode::register('image', 'Monstrex\AveSite\Templates\CustomShortcodes@image');
    }

    /**
     * Register Blade Directives
     */
    protected function registerBlades(): void
    {
        Blade::directive('renderBlock', function ($expression) {
            $expression = trim($expression, "\'\"");
            return "<?php echo \Monstrex\AveSite\Facades\AveBlock::render({$expression}); ?>";
        });

        Blade::directive('renderForm', function ($expression) {
            return "<?php
                \$args = explode(',', {$expression});
                \$form = isset(\$args[0]) ? \$args[0] : null;
                \$subject = isset(\$args[1]) ? \$args[1] : null;
                \$suffix = isset(\$args[2]) ? \$args[2] : null;
                echo \Monstrex\AveSite\Facades\AveBlock::renderForm(trim(\$form, \"'\\\" \"), \$subject, trim(\$suffix, \"'\\\" \"));
            ?>";
        });

        Blade::directive('renderRegion', function ($expression) {
            $expression = trim($expression, "\'\"");
            return "<?php echo \Monstrex\AveSite\Facades\AveBlock::renderRegion({$expression}); ?>";
        });
    }

    /**
     * Load localizations from database
     */
    protected function loadLocalizations(): void
    {
        try {
            $localizationService = app(LocalizationService::class);
            $localizationService->loadLocalizations();
        } catch (\Exception $e) {
            // Table may not exist during initial setup
            // Log silently to avoid breaking the application
        }
    }

    /**
     * Override Laravel config from database settings
     * Based on filament-site implementation for Laravel 12
     */
    protected function overrideConfig(): void
    {
        try {
            // Check if table exists first
            if (!Schema::hasTable('ave_site_settings')) {
                return;
            }

            $settingsService = app(SettingsService::class);
            $mail = $settingsService->getGroup('mail');

            if (empty($mail)) {
                return;
            }

            // Trim all mail settings
            $mail = collect($mail)
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->all();

            $driver = strtolower((string) ($mail['smtp_driver'] ?? config('mail.default', 'smtp')));
            $encryptionSource = strtoupper((string) ($mail['smtp_encryption'] ?? config('mail.mailers.smtp.encryption')));
            $encryption = $encryptionSource === 'NONE' ? null : strtolower($encryptionSource);

            // Set mail driver and from address
            config([
                'mail.default' => $driver,
                'mail.from.address' => $mail['from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $mail['from_name'] ?? config('mail.from.name'),
            ]);

            // Merge SMTP settings with existing config (preserves scheme, url, local_domain, etc.)
            if ($driver === 'smtp') {
                config([
                    'mail.mailers.smtp' => array_replace_recursive(config('mail.mailers.smtp', []), [
                        'transport' => 'smtp',
                        'host' => $mail['smtp_host'] ?? config('mail.mailers.smtp.host'),
                        'port' => isset($mail['smtp_port']) ? (int) $mail['smtp_port'] : config('mail.mailers.smtp.port'),
                        'username' => $mail['smtp_username'] ?? config('mail.mailers.smtp.username'),
                        'password' => $mail['smtp_password'] ?? config('mail.mailers.smtp.password'),
                        'encryption' => $encryption,
                        'timeout' => 10, // 10 seconds timeout to prevent hanging
                    ]),
                ]);
            }

            // Apply general settings
            $general = $settingsService->getGroup('general');
            if (!empty($general['site_app_name'])) {
                config(['app.name' => $general['site_app_name']]);
            }
        } catch (\Exception $e) {
            // Silently fail during setup
        }
    }

    /**
     * Register breadcrumb resolvers for Settings pages
     */
    protected function registerBreadcrumbs(): void
    {
        try {
            $breadcrumbService = app(\Monstrex\Ave\Services\BreadcrumbService::class);

            // Register resolver for Settings edit page
            $breadcrumbService->register('ave-site.settings.edit', function ($request) {
                $key = $request->route('key');
                $setting = Setting::where('key', $key)->first();

                if (!$setting) {
                    return null;
                }

                $dashboardRoute = \Illuminate\Support\Facades\Route::has('ave.dashboard')
                    ? route('ave.dashboard')
                    : null;

                return collect([
                    [
                        'url' => $dashboardRoute ? $dashboardRoute . '/resource/site-settings' : null,
                        'label' => 'Site Settings',
                        'icon' => null,
                    ],
                    [
                        'url' => null,
                        'label' => $setting->title,
                        'icon' => null,
                        'active' => true,
                    ],
                ]);
            });
        } catch (\Exception $e) {
            // BreadcrumbService may not be available if Ave is not fully loaded
        }
    }
}
