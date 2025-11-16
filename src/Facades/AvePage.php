<?php

namespace Monstrex\AveSite\Facades;

use Illuminate\Support\Facades\Facade;

class AvePage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ave-page';
    }
}
