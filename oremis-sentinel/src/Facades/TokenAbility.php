<?php

namespace Oremis\Sentinel\Facades;

use Illuminate\Support\Facades\Facade;

class TokenAbility extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'token_ability';
    }
}
