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
                'title' => 'Home',
                'menu' => true,
                'status' => true,
                'order' => 1,
            ]
        );

        Page::firstOrCreate(
            ['slug' => '404'],
            [
                'title' => 'Error 404',
                'menu' => false,
                'status' => true,
                'order' => 99,
            ]
        );
    }
}

