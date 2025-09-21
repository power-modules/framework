<?php

namespace Modular\Framework\PowerModule\Setup;

use Modular\Framework\App\Config\Setting;
use Modular\Framework\Config\Contract\HasConfig;
use Modular\Framework\Config\Loader;
use Modular\Framework\PowerModule\Contract\CanSetupPowerModule;
use RuntimeException;

class HasConfigSetup implements CanSetupPowerModule
{
    public function setup(PowerModuleSetupDto $powerModuleSetupDto): void
    {
        if ($powerModuleSetupDto->setupPhase !== SetupPhase::Pre) {
            return;
        }

        if (!$powerModuleSetupDto->powerModule instanceof HasConfig) {
            return;
        }

        if ($powerModuleSetupDto->rootContainer->has(Loader::class) === false) {
            throw new RuntimeException(Loader::class . ' not found in container');
        }

        $loader = $powerModuleSetupDto->rootContainer->get(Loader::class);

        if (!$loader instanceof Loader) {
            throw new RuntimeException(Loader::class . ' is not an instance of ' . Loader::class);
        }

        $powerModuleSetupDto
            ->powerModule
            ->setConfig(
                $loader->getConfig($powerModuleSetupDto->powerModule),
            )
        ;

        $powerModuleSetupDto
            ->powerModule
            ->getConfig()
            ->set(
                Setting::AppRoot,
                $powerModuleSetupDto->modularAppConfig->get(Setting::AppRoot),
            )
            ->set(
                Setting::CachePath,
                $powerModuleSetupDto->modularAppConfig->get(Setting::CachePath),
            )
        ;
    }
}
