<?php

namespace Monstrex\AveSite\Facades;

use Illuminate\Support\Facades\Facade;

class AveBlock extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ave-block';
    }
}
