<?php

namespace Monstrex\AveSite\Facades;

use Illuminate\Support\Facades\Facade;

class AveData extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ave-data';
    }
}
