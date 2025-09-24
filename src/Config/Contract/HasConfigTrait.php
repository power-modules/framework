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

namespace Modular\Framework\Config\Contract;

// @phpstan-ignore-next-line
trait HasConfigTrait
{
    protected PowerModuleConfig $powerModuleConfig;

    public function setConfig(PowerModuleConfig $powerModuleConfig): void
    {
        $this->powerModuleConfig = $powerModuleConfig;
    }

    public function getConfig(): PowerModuleConfig
    {
        return $this->powerModuleConfig;
    }

    public function getConfigValue(\BackedEnum|\UnitEnum $configPropertyEnum): mixed
    {
        return $this->powerModuleConfig->get($configPropertyEnum);
    }
}
