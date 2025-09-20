<?php

declare(strict_types=1);

namespace Modular\Framework\PowerModule;

use Modular\Framework\PowerModule\Contract\PowerModule;

class PowerModuleHelper
{
    public static function getPowerModuleName(PowerModule $powerModule): string
    {
        return $powerModule::class;
    }
}
