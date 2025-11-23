<?php

namespace Monstrex\AveSite\Database\Seeders;

use Illuminate\Database\Seeder;
use Monstrex\AveSite\Models\Page;

class AveSitePagesSeeder extends Seeder
{
    public function run(): void
    {
        Page::firstOrCreate(
            ['slug' => 'home'],
            [
                'title' => __('ave-site::seeders.pages.home_title'),
                'menu' => true,
                'status' => true,
                'order' => 1,
            ]
        );

        Page::firstOrCreate(
            ['slug' => '404'],
            [
                'title' => __('ave-site::seeders.pages.error_title'),
                'menu' => false,
                'status' => true,
                'order' => 99,
            ]
        );
    }
}

