<?php

namespace Modular\Framework\PowerModule\Contract;

use Modular\Framework\PowerModule\Setup\PowerModuleSetupDto;

interface CanSetupPowerModule
{
    public function setup(PowerModuleSetupDto $powerModuleSetupDto): void;
}
