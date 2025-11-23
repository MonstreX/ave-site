<?php

namespace Monstrex\AveSite\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Monstrex\AveSite\Models\Page;

class AveSitePagesSeeder extends Seeder
{
    public function run(): void
    {
        Page::firstOrCreate(
            ['slug' => Str::slug(__('ave-site::seeders.pages.home_slug'))],
            [
                'title' => __('ave-site::seeders.pages.home_title'),
                'menu' => true,
                'status' => true,
                'order' => 1,
            ]
        );

        Page::firstOrCreate(
            ['slug' => Str::slug(__('ave-site::seeders.pages.error_slug'))],
            [
                'title' => __('ave-site::seeders.pages.error_title'),
                'menu' => false,
                'status' => true,
                'order' => 99,
            ]
        );
    }
}

