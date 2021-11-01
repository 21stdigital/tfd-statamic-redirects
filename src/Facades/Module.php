<?php

namespace TFD\Redirects\Facades;

use Illuminate\Support\Facades\Facade;
use TFD\Redirects\ModuleRepository;

class Module extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ModuleRepository::class;
    }
}