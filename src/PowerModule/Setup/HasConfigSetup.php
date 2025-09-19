<?php

namespace Modular\Framework\PowerModule\Setup;

use Modular\Framework\App\Config\Setting;
use Modular\Framework\Config\Contract\HasConfig;
use Modular\Framework\Config\Loader;
use Modular\Framework\PowerModule\Contract\CanSetupPowerModule;

class HasConfigSetup implements CanSetupPowerModule
{
    public function __construct(
        private readonly Loader $powerModuleConfigLoader,
    ) {
    }

    public function setup(PowerModuleSetupDto $powerModuleSetupDto): void
    {
        if ($powerModuleSetupDto->setupPhase !== SetupPhase::Pre) {
            return;
        }

        if (!$powerModuleSetupDto->powerModule instanceof HasConfig) {
            return;
        }

        $powerModuleSetupDto
            ->powerModule
            ->setConfig(
                $this->powerModuleConfigLoader->getConfig($powerModuleSetupDto->powerModule),
            )
        ;

        $powerModuleSetupDto
            ->powerModule
            ->getConfig()
            ->set(
                Setting::AppRoot,
                $powerModuleSetupDto->modularAppConfig->get(Setting::AppRoot),
            )
        ;
    }
}
