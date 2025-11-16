<?php

namespace Monstrex\AveSite\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        Artisan::call('migrate:fresh');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
