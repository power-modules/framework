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

use Modular\Framework\App\Config\Setting;
use Modular\Framework\Config\Contract\HasConfig;
use Modular\Framework\Config\Loader;
use Modular\Framework\PowerModule\Contract\PowerModuleSetup;
use RuntimeException;

class HasConfigSetup implements PowerModuleSetup
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
