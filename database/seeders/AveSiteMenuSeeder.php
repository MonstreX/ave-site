<?php

namespace Monstrex\AveSite\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Monstrex\AveSite\Support\MenuInstaller;

class AveSiteMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menu = DB::table('ave_menus')->where('key', 'admin')->first();

        if (! $menu) {
            $this->command?->warn('Admin menu not found. Skipping ave-site menu items.');
            return;
        }

        $settingsSeeded = MenuInstaller::install($menu->id);

        if (! $settingsSeeded) {
            $this->command?->warn('Settings menu not found. Redirects, Scripts and Block Groups were not added.');
        } else {
            $this->command?->info('Ave-site menu items ensured.');
        }
    }
}
