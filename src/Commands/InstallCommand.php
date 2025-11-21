<?php

namespace Monstrex\AveSite\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'ave-site:install {--force : Force installation in production}';

    protected $description = 'Install Ave Site CMS package';

    public function handle(): int
    {
        $this->info('🚀 Installing Ave Site CMS Package...');
        $this->newLine();

        // Run migrations
        $this->info('📦 Running migrations...');
        $this->call('migrate', [
            '--force' => $this->option('force'),
        ]);
        $this->newLine();

        // Publish config
        $this->info('⚙️  Publishing configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'ave-site-config',
            '--force' => true,
        ]);
        $this->newLine();

        // Create menu items
        $this->info('📋 Creating menu items...');
        $this->createMenuItems();
        $this->newLine();

        // Publish views (optional)
        if ($this->confirm('Publish views to resources/views/vendor/ave-site?', false)) {
            $this->call('vendor:publish', [
                '--tag' => 'ave-site-views',
                '--force' => true,
            ]);
            $this->newLine();
        }

        $this->info('✅ Ave Site CMS package installed successfully!');
        $this->newLine();

        $this->comment('Next steps:');
        $this->line('  1. Create block regions in admin panel');
        $this->line('  2. Create pages and blocks');
        $this->line('  3. Configure settings via admin panel');
        $this->newLine();

        return self::SUCCESS;
    }

    protected function createMenuItems(): void
    {
        // Get admin menu
        $menu = \DB::table('ave_menus')->where('key', 'admin')->first();

        if (!$menu) {
            $this->warn('Admin menu not found. Skipping menu items.');
            return;
        }

        $menuId = $menu->id;

        // Get max order
        $maxOrder = \DB::table('ave_menu_items')
            ->where('menu_id', $menuId)
            ->whereNull('parent_id')
            ->max('order') ?? 0;

        // Create "Content" group
        $contentGroupId = \DB::table('ave_menu_items')->insertGetId([
            'menu_id' => $menuId,
            'parent_id' => null,
            'title' => __('ave-site::install.menu_content_group'),
            'status' => 1,
            'icon' => 'voyager-images',
            'route' => null,
            'url' => null,
            'target' => '_self',
            'order' => $maxOrder + 1,
            'permission_key' => null,
            'resource_slug' => null,
            'ability' => null,
            'is_divider' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Content group items
        $contentItems = [
            [
                'title' => __('ave-site::install.menu_pages'),
                'icon' => 'voyager-file-text',
                'resource_slug' => 'site-pages',
                'order' => 1,
            ],
            [
                'title' => __('ave-site::install.menu_blocks'),
                'icon' => 'voyager-puzzle',
                'resource_slug' => 'site-blocks',
                'order' => 2,
            ],
            [
                'title' => __('ave-site::install.menu_block_regions'),
                'icon' => 'voyager-resize-full',
                'resource_slug' => 'site-block-regions',
                'order' => 3,
            ],
            [
                'title' => __('ave-site::install.menu_forms'),
                'icon' => 'voyager-mail',
                'resource_slug' => 'site-forms',
                'order' => 4,
            ],
            [
                'title' => __('ave-site::install.menu_localizations'),
                'icon' => 'voyager-font',
                'resource_slug' => 'site-localizations',
                'order' => 5,
            ],
        ];

        foreach ($contentItems as $item) {
            \DB::table('ave_menu_items')->insert([
                'menu_id' => $menuId,
                'parent_id' => $contentGroupId,
                'title' => $item['title'],
                'status' => 1,
                'icon' => $item['icon'],
                'route' => null,
                'url' => null,
                'target' => '_self',
                'order' => $item['order'],
                'permission_key' => null,
                'resource_slug' => $item['resource_slug'],
                'ability' => 'viewAny',
                'is_divider' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Settings item (separate)
        \DB::table('ave_menu_items')->insert([
            'menu_id' => $menuId,
            'parent_id' => null,
            'title' => __('ave-site::install.menu_settings'),
            'status' => 1,
            'icon' => 'voyager-settings',
            'route' => null,
            'url' => null,
            'target' => '_self',
            'order' => $maxOrder + 2,
            'permission_key' => null,
            'resource_slug' => 'site-settings',
            'ability' => 'viewAny',
            'is_divider' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Redirects item (separate)
        \DB::table('ave_menu_items')->insert([
            'menu_id' => $menuId,
            'parent_id' => null,
            'title' => __('ave-site::install.menu_redirects'),
            'status' => 1,
            'icon' => 'voyager-forward',
            'route' => null,
            'url' => null,
            'target' => '_self',
            'order' => $maxOrder + 3,
            'permission_key' => null,
            'resource_slug' => 'site-redirects',
            'ability' => 'viewAny',
            'is_divider' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Scripts item (separate)
        \DB::table('ave_menu_items')->insert([
            'menu_id' => $menuId,
            'parent_id' => null,
            'title' => __('ave-site::install.menu_scripts'),
            'status' => 1,
            'icon' => 'voyager-code',
            'route' => null,
            'url' => null,
            'target' => '_self',
            'order' => $maxOrder + 4,
            'permission_key' => null,
            'resource_slug' => 'site-scripts',
            'ability' => 'viewAny',
            'is_divider' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info('   Menu items created successfully');
    }
}
