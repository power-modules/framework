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

namespace Modular\Framework\Test\PowerModule\Sample\LibraryA;

use Modular\Framework\Config\Contract\HasConfig;
use Modular\Framework\Config\Contract\HasConfigTrait;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\Test\PowerModule\Sample\LibraryA\Config\Config;

class ConfigurableLibraryAModule implements PowerModule, HasConfig
{
    use HasConfigTrait;

    public function __construct(
    ) {
        $this->powerModuleConfig = Config::create();
    }

    public function register(ConfigurableContainerInterface $container): void
    {
    }
}
