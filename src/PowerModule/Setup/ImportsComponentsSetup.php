<?php

namespace Modular\Framework\PowerModule\Setup;

use Modular\Framework\Container\Exception\ContainerException;
use Modular\Framework\PowerModule\Contract\CanSetupPowerModule;
use Modular\Framework\PowerModule\Contract\ImportsComponents;

class ImportsComponentsSetup implements CanSetupPowerModule
{
    public function setup(PowerModuleSetupDto $powerModuleSetupDto): void
    {
        if ($powerModuleSetupDto->setupPhase !== SetupPhase::Post) {
            return;
        }

        if (!$powerModuleSetupDto->powerModule instanceof ImportsComponents) {
            return;
        }

        foreach ($powerModuleSetupDto->powerModule::imports() as $importItem) {
            foreach ($importItem->itemsToImport as $itemName) {
                if ($powerModuleSetupDto->rootContainer->has($itemName) === false) {
                    throw new ContainerException(
                        sprintf('Could not find item to import: %s (parent module: %s)', $itemName, $importItem->moduleName),
                    );
                }

                $powerModuleSetupDto->moduleContainer->addServiceDefinition(
                    $itemName,
                    $powerModuleSetupDto->rootContainer->getServiceDefinition($itemName),
                );
            }
        }
    }
}
