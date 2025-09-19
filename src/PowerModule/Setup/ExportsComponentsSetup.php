<?php

namespace Modular\Framework\PowerModule\Setup;

use Modular\Framework\Container\InstanceResolver\InstanceViaContainerResolver;
use Modular\Framework\PowerModule\Contract\CanSetupPowerModule;
use Modular\Framework\PowerModule\Contract\ExportsComponents;

class ExportsComponentsSetup implements CanSetupPowerModule
{
    public function setup(PowerModuleSetupDto $powerModuleSetupDto): void
    {
        if ($powerModuleSetupDto->setupPhase !== SetupPhase::Pre) {
            return;
        }

        if (!$powerModuleSetupDto->powerModule instanceof ExportsComponents) {
            return;
        }

        foreach ($powerModuleSetupDto->powerModule::exports() as $itemToExport) {
            $powerModuleSetupDto->rootContainer->set(
                $itemToExport,
                $powerModuleSetupDto->moduleContainer,
                InstanceViaContainerResolver::class,
            );
        }
    }
}
