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
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;

final readonly class PowerModuleSetupDto
{
    public function __construct(
        public SetupPhase $setupPhase,
        public PowerModule $powerModule,
        public ConfigurableContainerInterface $rootContainer,
        public ConfigurableContainerInterface $moduleContainer,
        public Config $modularAppConfig,
    ) {
    }
}
