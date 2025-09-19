<?php

namespace Modular\Framework\PowerModule\Setup;

use Modular\Framework\App\Config\Config;
use Modular\Framework\PowerModule\Contract\CanSetupPowerModule;

class ModularAppConfigInjector implements CanSetupPowerModule
{
    public function setup(PowerModuleSetupDto $powerModuleSetupDto): void
    {
        if ($powerModuleSetupDto->moduleContainer->has(Config::class) === true) {
            return;
        }

        $powerModuleSetupDto->moduleContainer->set(
            Config::class,
            $powerModuleSetupDto->modularAppConfig,
        );
    }
}
