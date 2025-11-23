<?php

namespace Monstrex\AveSite\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AveSiteMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Locate the main admin menu (key "admin")
        $mainMenu = DB::table('ave_menus')->where('key', 'admin')->first();

        if (!$mainMenu) {
            $this->command->warn('Admin menu not found. Skipping ave-site menu items.');
            return;
        }

        // Determine the highest order among root items
        $maxOrder = DB::table('ave_menu_items')
            ->where('menu_id', $mainMenu->id)
            ->whereNull('parent_id')
            ->max('order') ?? 0;

        $order = $maxOrder + 1;

        // Create Redirects menu item (EN)
        $this->createMenuItem($mainMenu->id, [
            'title' => 'Redirects',
            'url' => '/admin/resource/site-redirects',
            'icon_class' => 'voyager-forward',
            'order' => $order,
            'locale' => 'en',
        ]);

        // Create Redirects menu item (RU)
        $this->createMenuItem($mainMenu->id, [
            'title' => 'Редиректы',
            'url' => '/admin/resource/site-redirects',
            'icon_class' => 'voyager-forward',
            'order' => $order,
            'locale' => 'ru',
        ]);

        $this->command->info('Ave-site menu items created successfully.');
    }

    protected function createMenuItem(int $menuId, array $data): void
    {
        $existing = DB::table('ave_menu_items')
            ->where('menu_id', $menuId)
            ->where('url', $data['url'])
            ->where('locale', $data['locale'])
            ->first();

        if ($existing) {
            $this->command->warn("Menu item '{$data['title']}' already exists.");
            return;
        }

        DB::table('ave_menu_items')->insert([
            'menu_id' => $menuId,
            'title' => $data['title'],
            'url' => $data['url'],
            'icon_class' => $data['icon_class'] ?? null,
            'color' => null,
            'parent_id' => null,
            'order' => $data['order'],
            'locale' => $data['locale'],
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("Created menu item: {$data['title']}");
    }
}
