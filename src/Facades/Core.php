<?php

namespace Areaseb\Core\Facades;

use Illuminate\Support\Facades\Facade;

class Core extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'core';
    }
}
