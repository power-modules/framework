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

namespace Modular\Framework\PowerModule;

use Modular\Framework\PowerModule\Contract\CanCreatePowerModuleInstance;
use Modular\Framework\PowerModule\Contract\PowerModule;

class DefaultModuleResolver implements CanCreatePowerModuleInstance
{
    public function create(string $id): PowerModule
    {
        return new $id();
    }
}
