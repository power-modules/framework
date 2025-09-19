<?php

namespace Modular\Framework\PowerModule;

use Modular\Framework\PowerModule\Contract\PowerModule;

trait GetPowerModuleNameTrait
{
    protected function getPowerModuleName(PowerModule $powerModule): string
    {
        return $powerModule::class;
    }
}
