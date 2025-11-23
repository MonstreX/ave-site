<?php

namespace Monstrex\AveSite\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class UninstallCommand extends Command
{
    protected $signature = 'ave-site:uninstall
                            {--force : Skip confirmation prompts}
                            {--keep-config : Keep published config file}
                            {--keep-views : Keep published views}
                            {--keep-menu : Keep admin menu items}
                            {--dry-run : Preview actions without executing them}';

    protected $description = 'Completely uninstall Ave Site CMS package';

    protected bool $isDryRun = false;

    protected array $rootMenuSlugs = [
        'site-pages',
        'site-blocks',
        'site-forms',
        'site-localizations',
        'site-settings',
    ];

    protected array $settingsMenuSlugs = [
        'site-redirects',
        'site-scripts',
        'site-block-regions',
    ];

    public function handle(): int
    {
        $this->isDryRun = (bool) $this->option('dry-run');

        $this->displayWarning();

        if (!$this->option('force') && !$this->confirm('This will remove Ave Site CMS. Continue?', false)) {
            $this->info('Uninstall cancelled.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Starting Ave Site CMS uninstallation...');
        $this->newLine();

        $this->dropDatabaseTables();
        $this->cleanMigrationRecords();

        if ($this->option('keep-menu')) {
            $this->comment('Skipping menu deletion (--keep-menu)');
        } else {
            $this->deleteMenuItems();
        }

        if ($this->option('keep-config')) {
            $this->comment('Skipping config deletion (--keep-config)');
        } else {
            $this->deletePublishedConfig();
        }

        if ($this->option('keep-views')) {
            $this->comment('Skipping view deletion (--keep-views)');
        } else {
            $this->deletePublishedViews();
        }

        $this->newLine();
        $this->info($this->isDryRun ? 'Dry run completed. No changes were made.' : 'Ave Site CMS uninstalled successfully.');
        $this->newLine();

        $this->showNextSteps();

        return self::SUCCESS;
    }

    protected function displayWarning(): void
    {
        $this->newLine();
        $this->warn('WARNING: The following items will be removed.');
        $this->newLine();

        $tables = $this->getAveSiteTables();
        $configExists = File::exists(config_path('ave-site.php'));
        $viewsExist = File::exists(resource_path('views/vendor/ave-site'));
        $menuItems = $this->countMenuItems();

        if (!empty($tables)) {
            $this->line('  - Database tables (' . count($tables) . '): ' . implode(', ', $tables));
        } else {
            $this->line('  - No Ave Site tables detected');
        }

        if ($menuItems > 0 && !$this->option('keep-menu')) {
            $this->line('  - Admin menu items: ' . $menuItems);
        }

        if ($configExists && !$this->option('keep-config')) {
            $this->line('  - Config file: config/ave-site.php');
        }

        if ($viewsExist && !$this->option('keep-views')) {
            $this->line('  - Published views: resources/views/vendor/ave-site');
        }

        $this->line('  - Migration records for Ave Site tables');

        if ($this->isDryRun) {
            $this->newLine();
            $this->info('DRY RUN MODE: No changes will be made.');
        }

        $this->newLine();
    }

    protected function dropDatabaseTables(): void
    {
        $this->comment('Dropping Ave Site tables...');

        $tables = $this->getAveSiteTables();

        if (empty($tables)) {
            $this->info('No Ave Site tables found.');
            return;
        }

        if (!$this->isDryRun) {
            $this->disableForeignKeyChecks();
        }

        foreach ($tables as $table) {
            if ($this->isDryRun) {
                $this->line("  [DRY RUN] Would drop table: {$table}");
                continue;
            }

            if (Schema::hasTable($table)) {
                Schema::drop($table);
                $this->line("  - Dropped table: {$table}");
            }
        }

        if (!$this->isDryRun) {
            $this->enableForeignKeyChecks();
        }

        if (!$this->isDryRun) {
            $this->info('All Ave Site tables dropped.');
        }
    }

    protected function cleanMigrationRecords(): void
    {
        $this->comment('Cleaning migration records...');

        if (!Schema::hasTable('migrations')) {
            $this->info('Migrations table not found.');
            return;
        }

        $migrationNames = $this->getMigrationNames();

        if (empty($migrationNames)) {
            $this->info('No Ave Site migration records detected.');
            return;
        }

        $query = DB::table('migrations')->whereIn('migration', $migrationNames);

        if ($this->isDryRun) {
            $count = $query->count();
            $this->line("  [DRY RUN] Would delete {$count} migration record(s)");
            return;
        }

        $deleted = $query->delete();
        $this->info("Removed {$deleted} migration record(s).");
    }

    protected function deleteMenuItems(): void
    {
        $this->comment('Deleting admin menu items...');

        if (!Schema::hasTable('ave_menu_items') || !Schema::hasTable('ave_menus')) {
            $this->info('Ave menu tables not found.');
            return;
        }

        $menu = DB::table('ave_menus')->where('key', 'admin')->first();

        if (!$menu) {
            $this->info('Admin menu not found.');
            return;
        }

        $resourceSlugs = $this->allMenuSlugs();

        $query = DB::table('ave_menu_items')
            ->where('menu_id', $menu->id)
            ->whereIn('resource_slug', $resourceSlugs);

        if ($this->isDryRun) {
            $count = $query->count();
            $this->line("  [DRY RUN] Would delete {$count} menu item(s)");
            return;
        }

        $rootOrders = DB::table('ave_menu_items')
            ->where('menu_id', $menu->id)
            ->whereNull('parent_id')
            ->whereIn('resource_slug', $this->rootMenuSlugs)
            ->pluck('order');

        $deleted = $query->delete();
        $this->info("Removed {$deleted} menu item(s).");

        $minOrder = $rootOrders->min();

        if ($minOrder !== null) {
            DB::table('ave_menu_items')
                ->where('menu_id', $menu->id)
                ->whereNull('parent_id')
                ->where('order', '>', $minOrder)
                ->decrement('order', count($this->rootMenuSlugs));

            $this->line('  Adjusted menu item order.');
        }
    }

    protected function deletePublishedConfig(): void
    {
        $this->comment('Deleting config/ave-site.php...');

        $path = config_path('ave-site.php');

        if (!File::exists($path)) {
            $this->info('Config file not found.');
            return;
        }

        if ($this->isDryRun) {
            $this->line('  [DRY RUN] Would delete config/ave-site.php');
            return;
        }

        File::delete($path);
        $this->info('Removed config/ave-site.php.');
    }

    protected function deletePublishedViews(): void
    {
        $this->comment('Deleting published views...');

        $path = resource_path('views/vendor/ave-site');

        if (!File::exists($path)) {
            $this->info('Published views not found.');
            return;
        }

        if ($this->isDryRun) {
            $this->line('  [DRY RUN] Would delete resources/views/vendor/ave-site');
            return;
        }

        File::deleteDirectory($path);
        $this->info('Removed resources/views/vendor/ave-site.');
    }

    protected function showNextSteps(): void
    {
        $this->comment('Next steps:');
        $this->line('  1. Remove the package from composer.json: composer remove monstrex/ave-site');
        $this->line('  2. Clear caches: php artisan cache:clear && php artisan config:clear');
        $this->line('  3. Remove custom AveSite resources if you created them manually (app/AveSite/*).');
    }

    protected function getAveSiteTables(): array
    {
        $database = DB::getDatabaseName();
        $tables = [];

        foreach (Schema::getTableListing() as $table) {
            $name = $table;

            if (str_contains($table, '.')) {
                [$db, $name] = explode('.', $table, 2);

                if ($db !== $database) {
                    continue;
                }
            }

            if (str_starts_with($name, 'ave_site_')) {
                $tables[] = $name;
            }
        }

        return $tables;
    }

    protected function getMigrationNames(): array
    {
        $path = __DIR__ . '/../../database/migrations';

        if (!is_dir($path)) {
            return [];
        }

        $files = File::glob($path . '/*.php');

        return array_map(static function ($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, $files);
    }

    protected function countMenuItems(): int
    {
        if (!Schema::hasTable('ave_menu_items')) {
            return 0;
        }

        return (int) DB::table('ave_menu_items')
            ->whereIn('resource_slug', $this->allMenuSlugs())
            ->count();
    }

    protected function allMenuSlugs(): array
    {
        return array_merge($this->rootMenuSlugs, $this->settingsMenuSlugs);
    }

    protected function disableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();

        match ($driver) {
            'mysql' => DB::statement('SET FOREIGN_KEY_CHECKS=0'),
            'pgsql' => DB::statement('SET CONSTRAINTS ALL DEFERRED'),
            'sqlite' => DB::statement('PRAGMA foreign_keys = OFF'),
            default => null,
        };
    }

    protected function enableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();

        match ($driver) {
            'mysql' => DB::statement('SET FOREIGN_KEY_CHECKS=1'),
            'pgsql' => DB::statement('SET CONSTRAINTS ALL IMMEDIATE'),
            'sqlite' => DB::statement('PRAGMA foreign_keys = ON'),
            default => null,
        };
    }
}
