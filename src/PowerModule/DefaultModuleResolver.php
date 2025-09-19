<?php

declare(strict_types=1);

namespace Modular\Framework\PowerModule;

use Modular\Framework\PowerModule\Contract\CanCreatePowerModuleInstance;
use Modular\Framework\PowerModule\Contract\PowerModule;

class DefaultModuleResolver implements CanCreatePowerModuleInstance
{
    public function create(string $id): PowerModule
    {
        return new $id();
    }
}
