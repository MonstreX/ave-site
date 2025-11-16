<?php

namespace Monstrex\AveSite\Providers;

use Illuminate\Support\ServiceProvider;
use Monstrex\AveSite\Commands\InstallCommand;
use Monstrex\AveSite\Services\{
    DataService,
    ImageProcessingService,
    LiquidTemplateService,
    PageService,
    BlockRenderService,
    SettingsService,
    ChunkService
};

class AveSiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../config/ave-site.php', 'ave-site');

        // Register services as singletons
        $this->app->singleton(DataService::class);
        $this->app->singleton(ImageProcessingService::class);
        $this->app->singleton(LiquidTemplateService::class);
        $this->app->singleton(PageService::class);
        $this->app->singleton(BlockRenderService::class);
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(ChunkService::class);
    }

    public function boot(): void
    {
        // Migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'ave-site');

        // Translations
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'ave-site');

        // Routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        // Register Ave Resources
        if (!$this->app->runningInConsole()) {
            $this->registerAveResources();
        }

        // Apply runtime config (settings override)
        if (!$this->app->runningInConsole()) {
            app(SettingsService::class)->applyRuntimeConfig();
        }

        // Publishable resources
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/ave-site.php' => config_path('ave-site.php'),
            ], 'ave-site-config');

            $this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/ave-site'),
            ], 'ave-site-views');

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
                \Monstrex\AveSite\Admin\Resources\Page\Resource::class,
                \Monstrex\AveSite\Admin\Resources\Block\Resource::class,
                \Monstrex\AveSite\Admin\Resources\BlockRegion\Resource::class,
                \Monstrex\AveSite\Admin\Resources\Chunk\Resource::class,
                \Monstrex\AveSite\Admin\Resources\Setting\Resource::class,
            ];

            foreach ($resources as $resourceClass) {
                $resourceManager->register($resourceClass);
            }
        } catch (\Exception $e) {
            // ResourceManager not available (Ave not loaded)
        }
    }
}
