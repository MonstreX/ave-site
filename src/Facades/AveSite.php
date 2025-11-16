<?php

namespace Monstrex\AveSite\Facades;

use Illuminate\Support\Facades\Facade;

class AveSite extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ave-site';
    }
}
