<?php

declare(strict_types=1);

namespace Modular\Framework\PowerModule\Contract;

interface CanCreatePowerModuleInstance
{
    /**
     * @param class-string<PowerModule> $id
     */
    public function create(string $id): PowerModule;
}
