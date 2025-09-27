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
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\PowerModuleSetup;
use Modular\Framework\PowerModule\Setup\Exception\ExportCollisionException;

class ExportsComponentsSetup implements PowerModuleSetup
{
    /**
     * @var array<string,class-string<PowerModule>> Tracks which module exported which component
     */
    private array $exportRegistry = [];

    public function setup(PowerModuleSetupDto $powerModuleSetupDto): void
    {
        if ($powerModuleSetupDto->setupPhase !== SetupPhase::Pre) {
            return;
        }

        if (!$powerModuleSetupDto->powerModule instanceof ExportsComponents) {
            return;
        }

        foreach ($powerModuleSetupDto->powerModule::exports() as $itemToExport) {
            $this->checkExportCollision($itemToExport, $powerModuleSetupDto);

            $powerModuleSetupDto->rootContainer->set(
                $itemToExport,
                $powerModuleSetupDto->moduleContainer,
                InstanceViaContainerResolver::class,
            );
        }
    }

    private function checkExportCollision(string $itemToExport, PowerModuleSetupDto $powerModuleSetupDto): void
    {
        if ($powerModuleSetupDto->rootContainer->has($itemToExport)) {
            $existingModuleName = $this->exportRegistry[$itemToExport] ?? '<unknown module>';

            if ($existingModuleName === $powerModuleSetupDto->powerModule::class) {
                // The same module is trying to export the same item again; this is allowed.
                return;
            }

            throw new ExportCollisionException(
                $itemToExport,
                $existingModuleName,
                $powerModuleSetupDto->powerModule::class,
            );
        }

        $this->exportRegistry[$itemToExport] = $powerModuleSetupDto->powerModule::class;
    }
}
