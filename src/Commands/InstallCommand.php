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

        // Check if Ave Admin is installed
        if (!class_exists(\Monstrex\Ave\Providers\AveServiceProvider::class)) {
            $this->error('❌ Ave Admin Panel is not installed!');
            $this->newLine();
            $this->comment('Please install Ave Admin Panel first:');
            $this->line('  composer require monstrex/ave');
            $this->newLine();
            return self::FAILURE;
        }

        // Check if ave_menus table exists
        if (!\Schema::hasTable('ave_menus')) {
            $this->error('❌ Ave Admin Panel is not properly set up!');
            $this->newLine();
            $this->comment('Please run Ave Admin migrations first:');
            $this->line('  php artisan migrate');
            $this->newLine();
            return self::FAILURE;
        }

        $this->info('✓ Ave Admin Panel detected');
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

        // Create default block regions
        $this->info('📋 Creating default block regions...');
        $this->createBlockRegions();
        $this->newLine();

        // Create default settings
        $this->info('⚙️  Creating default settings...');
        $this->createDefaultSettings();
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
        $menu = \DB::table('ave_menus')->where('key', 'admin')->first();

        if (!$menu) {
            $this->warn('Admin menu not found. Skipping menu items.');
            return;
        }

        $menuId = $menu->id;

        // Find Dashboard order to insert after it
        $dashboardItem = \DB::table('ave_menu_items')
            ->where('menu_id', $menuId)
            ->whereNull('parent_id')
            ->where('route', 'ave.dashboard')
            ->first();

        $startOrder = $dashboardItem ? $dashboardItem->order + 1 : 1;

        // Shift existing items to make room
        \DB::table('ave_menu_items')
            ->where('menu_id', $menuId)
            ->whereNull('parent_id')
            ->where('order', '>=', $startOrder)
            ->increment('order', 5);

        // Root level items (after Dashboard)
        $rootItems = [
            [
                'title' => __('ave-site::install.menu_pages'),
                'icon' => 'voyager-file-text',
                'resource_slug' => 'site-pages',
                'order' => $startOrder,
            ],
            [
                'title' => __('ave-site::install.menu_blocks'),
                'icon' => 'voyager-puzzle',
                'resource_slug' => 'site-blocks',
                'order' => $startOrder + 1,
            ],
            [
                'title' => __('ave-site::install.menu_forms'),
                'icon' => 'voyager-mail',
                'resource_slug' => 'site-forms',
                'order' => $startOrder + 2,
            ],
            [
                'title' => __('ave-site::install.menu_localizations'),
                'icon' => 'voyager-font',
                'resource_slug' => 'site-localizations',
                'order' => $startOrder + 3,
            ],
            [
                'title' => __('ave-site::install.menu_site_settings'),
                'icon' => 'voyager-tools',
                'resource_slug' => 'site-settings',
                'order' => $startOrder + 4,
            ],
        ];

        foreach ($rootItems as $item) {
            // Check if already exists
            $exists = \DB::table('ave_menu_items')
                ->where('menu_id', $menuId)
                ->where('resource_slug', $item['resource_slug'])
                ->exists();

            if (!$exists) {
                \DB::table('ave_menu_items')->insert([
                    'menu_id' => $menuId,
                    'parent_id' => null,
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
        }

        // Find Settings submenu
        $settingsMenu = \DB::table('ave_menu_items')
            ->where('menu_id', $menuId)
            ->whereNull('parent_id')
            ->where('title', 'Settings')
            ->first();

        if (!$settingsMenu) {
            // Try to find by Russian title
            $settingsMenu = \DB::table('ave_menu_items')
                ->where('menu_id', $menuId)
                ->whereNull('parent_id')
                ->where('title', 'Настройки')
                ->first();
        }

        if ($settingsMenu) {
            $maxSettingsOrder = \DB::table('ave_menu_items')
                ->where('menu_id', $menuId)
                ->where('parent_id', $settingsMenu->id)
                ->max('order') ?? 0;

            $settingsItems = [
                [
                    'title' => __('ave-site::install.menu_redirects'),
                    'icon' => 'voyager-forward',
                    'resource_slug' => 'site-redirects',
                    'order' => $maxSettingsOrder + 1,
                ],
                [
                    'title' => __('ave-site::install.menu_scripts'),
                    'icon' => 'voyager-code',
                    'resource_slug' => 'site-scripts',
                    'order' => $maxSettingsOrder + 2,
                ],
                [
                    'title' => __('ave-site::install.menu_block_regions'),
                    'icon' => 'voyager-resize-full',
                    'resource_slug' => 'site-block-regions',
                    'order' => $maxSettingsOrder + 3,
                ],
            ];

            foreach ($settingsItems as $item) {
                $exists = \DB::table('ave_menu_items')
                    ->where('menu_id', $menuId)
                    ->where('resource_slug', $item['resource_slug'])
                    ->exists();

                if (!$exists) {
                    \DB::table('ave_menu_items')->insert([
                        'menu_id' => $menuId,
                        'parent_id' => $settingsMenu->id,
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
            }
        } else {
            $this->warn('Settings menu not found. Redirects, Scripts and Block Regions were not added.');
        }

        $this->info('   Menu items created successfully');
    }

    protected function createBlockRegions(): void
    {
        $regions = [
            [
                'title' => __('ave-site::seeders.regions.content_before'),
                'key' => 'content-before',
                'order' => 1,
                'color' => '#2f8516',
            ],
            [
                'title' => __('ave-site::seeders.regions.content'),
                'key' => 'content',
                'order' => 2,
                'color' => '#2f8516',
            ],
            [
                'title' => __('ave-site::seeders.regions.content_after'),
                'key' => 'content-after',
                'order' => 3,
                'color' => '#4bc2a2',
            ],
            [
                'title' => __('ave-site::seeders.regions.no_position'),
                'key' => 'no-position',
                'order' => 4,
                'color' => '#c7c7c7',
            ],
            [
                'title' => __('ave-site::seeders.regions.sidebar'),
                'key' => 'sidebar',
                'order' => 5,
                'color' => '#e20a0f',
            ],
        ];

        foreach ($regions as $region) {
            \DB::table('ave_site_block_regions')->updateOrInsert(
                ['key' => $region['key']],
                [
                    'title' => $region['title'],
                    'order' => $region['order'],
                    'color' => $region['color'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->info('   Block regions created successfully');
    }

    protected function createDefaultSettings(): void
    {
        $settingModel = config('ave-site.models.setting');
        $t = 'ave-site::seeders.settings';

        // GENERAL SETTINGS
        $generalFields = [
            'fields' => [
                'section_main' => [
                    'type' => 'section',
                    'icon' => 'voyager-tools',
                    'label' => __("$t.general.section_main"),
                ],
                'site_title' => [
                    'label' => __("$t.general.site_title"),
                    'type' => 'text',
                    'value' => __("$t.general.site_title_value"),
                    'class' => 'col-md-12',
                ],
                'site_description' => [
                    'label' => __("$t.general.site_description"),
                    'type' => 'text',
                    'value' => __("$t.general.site_description_value"),
                    'class' => 'col-md-12',
                ],
                'section_pages' => [
                    'type' => 'section',
                    'icon' => 'voyager-documentation',
                    'label' => __("$t.general.section_pages"),
                ],
                'site_home_page' => [
                    'label' => __("$t.general.site_home_page"),
                    'type' => 'number',
                    'value' => '1',
                    'class' => 'col-md-12',
                ],
                'site_403_page' => [
                    'label' => __("$t.general.site_403_page"),
                    'type' => 'number',
                    'value' => '1',
                    'class' => 'col-md-12',
                ],
                'site_404_page' => [
                    'label' => __("$t.general.site_404_page"),
                    'type' => 'number',
                    'value' => '2',
                    'class' => 'col-md-12',
                ],
                'section_system' => [
                    'type' => 'section',
                    'icon' => 'voyager-exclamation',
                    'label' => __("$t.general.section_system"),
                ],
                'site_app_name' => [
                    'label' => __("$t.general.site_app_name"),
                    'type' => 'text',
                    'value' => __("$t.general.site_app_name_value"),
                    'class' => 'col-md-12',
                ],
                'section_captcha' => [
                    'type' => 'section',
                    'icon' => 'voyager-puzzle',
                    'label' => __("$t.general.section_captcha"),
                ],
                'site_captcha_site_key' => [
                    'label' => __("$t.general.site_captcha_site_key"),
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'site_captcha_secret_key' => [
                    'label' => __("$t.general.site_captcha_secret_key"),
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
            ],
        ];

        $settingModel::updateOrCreate(['key' => 'general'], [
            'title' => __("$t.general.title"),
            'group' => 'general',
            'order' => 1,
            'fields' => json_encode($generalFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        // MAIL SETTINGS
        $mailFields = [
            'fields' => [
                'to_address' => [
                    'label' => __("$t.mail.to_address"),
                    'type' => 'text',
                    'value' => __("$t.mail.to_address_value"),
                    'class' => 'col-md-12',
                ],
                'from_name' => [
                    'label' => __("$t.mail.from_name"),
                    'type' => 'text',
                    'value' => __("$t.mail.from_name_value"),
                    'class' => 'col-md-12',
                ],
                'from_address' => [
                    'label' => __("$t.mail.from_address"),
                    'type' => 'text',
                    'value' => __("$t.mail.from_address_value"),
                    'class' => 'col-md-12',
                ],
                'section_smtp' => [
                    'type' => 'section',
                    'icon' => 'voyager-mail',
                    'label' => __("$t.mail.section_smtp"),
                ],
                'smtp_driver' => [
                    'label' => __("$t.mail.smtp_driver"),
                    'type' => 'dropdown',
                    'value' => 'smtp',
                    'options' => [
                        'smtp' => __("$t.mail.smtp_driver_option_smtp"),
                        'mailgun' => __("$t.mail.smtp_driver_option_mailgun"),
                        'log' => __("$t.mail.smtp_driver_option_log"),
                    ],
                    'class' => 'col-md-12',
                ],
                'smtp_host' => [
                    'label' => __("$t.mail.smtp_host"),
                    'type' => 'text',
                    'value' => __("$t.mail.smtp_host_value"),
                    'class' => 'col-md-12',
                ],
                'smtp_port' => [
                    'label' => __("$t.mail.smtp_port"),
                    'type' => 'number',
                    'value' => __("$t.mail.smtp_port_value"),
                    'class' => 'col-md-12',
                ],
                'smtp_username' => [
                    'label' => __("$t.mail.smtp_username"),
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'smtp_password' => [
                    'label' => __("$t.mail.smtp_password"),
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'smtp_encryption' => [
                    'label' => __("$t.mail.smtp_encryption"),
                    'type' => 'radio',
                    'value' => 'tls',
                    'options' => [
                        'none' => __("$t.mail.smtp_encryption_option_none"),
                        'ssl' => __("$t.mail.smtp_encryption_option_ssl"),
                        'tls' => __("$t.mail.smtp_encryption_option_tls"),
                    ],
                    'class' => 'col-md-12',
                ],
                'test_mail' => [
                    'label' => __("$t.mail.test_mail"),
                    'type' => 'route',
                    'value' => 'ave-site.settings.test-mail',
                    'icon' => 'voyager-mail',
                    'class' => 'col-md-12',
                ],
            ],
        ];

        $settingModel::updateOrCreate(['key' => 'mail'], [
            'title' => __("$t.mail.title"),
            'group' => 'mail',
            'order' => 2,
            'fields' => json_encode($mailFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        // SEO SETTINGS
        $seoFields = [
            'fields' => [
                'seo_title_template' => [
                    'label' => __("$t.seo.seo_title_template"),
                    'type' => 'text',
                    'value' => __("$t.seo.seo_title_template_value"),
                    'class' => 'col-md-12',
                ],
                'seo_title' => [
                    'label' => __("$t.seo.seo_title"),
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'meta_description' => [
                    'label' => __("$t.seo.meta_description"),
                    'type' => 'textarea',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'meta_keywords' => [
                    'label' => __("$t.seo.meta_keywords"),
                    'type' => 'textarea',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'section_og' => [
                    'label' => __("$t.seo.section_og"),
                    'type' => 'section',
                    'class' => 'col-md-12',
                ],
                'og_site_name' => [
                    'label' => __("$t.seo.og_site_name"),
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-6',
                ],
                'og_type' => [
                    'label' => __("$t.seo.og_type"),
                    'type' => 'dropdown',
                    'value' => 'website',
                    'class' => 'col-md-6',
                    'options' => [
                        'website' => __("$t.seo.og_type_website"),
                        'article' => __("$t.seo.og_type_article"),
                    ],
                ],
                'og_image' => [
                    'label' => __("$t.seo.og_image"),
                    'type' => 'media',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'section_twitter' => [
                    'label' => __("$t.seo.section_twitter"),
                    'type' => 'section',
                    'class' => 'col-md-12',
                ],
                'twitter_card' => [
                    'label' => __("$t.seo.twitter_card"),
                    'type' => 'dropdown',
                    'value' => 'summary_large_image',
                    'class' => 'col-md-6',
                    'options' => [
                        'summary' => __("$t.seo.twitter_card_summary"),
                        'summary_large_image' => __("$t.seo.twitter_card_summary_large"),
                    ],
                ],
                'twitter_site' => [
                    'label' => __("$t.seo.twitter_site"),
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-6',
                ],
            ],
        ];

        $settingModel::updateOrCreate(['key' => 'seo'], [
            'title' => __("$t.seo.title"),
            'group' => 'seo',
            'order' => 3,
            'fields' => json_encode($seoFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        // THEME SETTINGS
        $themeFields = [
            'fields' => [
                'theme_logo' => [
                    'label' => __("$t.theme.theme_logo"),
                    'type' => 'media',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'theme_favicon' => [
                    'label' => __("$t.theme.theme_favicon"),
                    'type' => 'media',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'theme_banner_image' => [
                    'label' => __("$t.theme.theme_banner_image"),
                    'type' => 'media',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
            ],
        ];

        $settingModel::updateOrCreate(['key' => 'theme'], [
            'title' => __("$t.theme.title"),
            'group' => 'theme',
            'order' => 4,
            'fields' => json_encode($themeFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        $this->info('   Default settings created successfully');
    }
}
