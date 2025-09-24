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
