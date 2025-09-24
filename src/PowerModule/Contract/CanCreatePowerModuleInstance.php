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

namespace Modular\Framework\PowerModule\Contract;

interface CanCreatePowerModuleInstance
{
    /**
     * @param class-string<PowerModule> $id
     */
    public function create(string $id): PowerModule;
}
