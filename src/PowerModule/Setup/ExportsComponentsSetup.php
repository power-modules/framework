<?php

/**
 * This file is part of the Modular Framework package.
 *
 * (c) 2025 Evgenii Teterin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

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
