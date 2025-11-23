<?php

namespace Monstrex\AveSite\Support;

use Monstrex\Ave\Models\MenuItem;

class MenuInstaller
{
    public static function install(int $menuId): bool
    {
        return (new self($menuId))->run();
    }

    public function __construct(protected int $menuId)
    {
    }

    protected function run(): bool
    {
        $this->seedRootItems();

        return $this->seedSettingsItems();
    }

    protected function seedRootItems(): void
    {
        $items = [
            [
                'slug' => 'site-pages',
                'icon' => 'voyager-file-text',
                'title' => __('ave-site::install.menu_pages'),
                'order' => 50,
            ],
            [
                'slug' => 'site-blocks',
                'icon' => 'voyager-puzzle',
                'title' => __('ave-site::install.menu_blocks'),
                'order' => 51,
            ],
            [
                'slug' => 'site-forms',
                'icon' => 'voyager-mail',
                'title' => __('ave-site::install.menu_forms'),
                'order' => 52,
            ],
            [
                'slug' => 'site-localizations',
                'icon' => 'voyager-font',
                'title' => __('ave-site::install.menu_localizations'),
                'order' => 53,
            ],
            [
                'slug' => 'site-settings',
                'icon' => 'voyager-tools',
                'title' => __('ave-site::install.menu_site_settings'),
                'order' => 54,
            ],
        ];

        foreach ($items as $item) {
            $this->upsertMenuItem([
                'parent_id' => null,
                'resource_slug' => $item['slug'],
                'route' => null,
                'url' => null,
            ], [
                'title' => $item['title'],
                'icon' => $item['icon'],
                'status' => 1,
                'target' => '_self',
                'order' => $item['order'],
                'permission_key' => null,
                'ability' => 'viewAny',
            ]);
        }
    }

    protected function seedSettingsItems(): bool
    {
        $settings = $this->findSettingsMenu();

        if (! $settings) {
            return false;
        }

        $baseOrder = MenuItem::query()
            ->where('menu_id', $this->menuId)
            ->where('parent_id', $settings->id)
            ->max('order') ?? 100;

        $items = [
            [
                'slug' => 'site-redirects',
                'icon' => 'voyager-forward',
                'title' => __('ave-site::install.menu_redirects'),
                'order' => $baseOrder + 1,
            ],
            [
                'slug' => 'site-scripts',
                'icon' => 'voyager-code',
                'title' => __('ave-site::install.menu_scripts'),
                'order' => $baseOrder + 2,
            ],
            [
                'slug' => 'site-block-regions',
                'icon' => 'voyager-resize-full',
                'title' => __('ave-site::install.menu_block_regions'),
                'order' => $baseOrder + 3,
            ],
        ];

        foreach ($items as $item) {
            $this->upsertMenuItem([
                'parent_id' => $settings->id,
                'resource_slug' => $item['slug'],
                'route' => null,
                'url' => null,
            ], [
                'title' => $item['title'],
                'icon' => $item['icon'],
                'status' => 1,
                'target' => '_self',
                'order' => $item['order'],
                'permission_key' => null,
                'ability' => 'viewAny',
            ]);
        }

        return true;
    }

    protected function upsertMenuItem(array $criteria, array $attributes): MenuItem
    {
        $criteria = array_merge([
            'menu_id' => $this->menuId,
        ], $criteria);

        return MenuItem::updateOrCreate(
            $criteria,
            array_merge($attributes, ['menu_id' => $this->menuId])
        );
    }

    protected function findSettingsMenu(): ?MenuItem
    {
        return MenuItem::query()
            ->where('menu_id', $this->menuId)
            ->whereNull('parent_id')
            ->whereNull('route')
            ->whereNull('url')
            ->whereNull('resource_slug')
            ->where('icon', 'voyager-settings')
            ->first();
    }
}
