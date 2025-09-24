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

namespace Modular\Framework\Container\Contract;

interface CanResolveViaContainer
{
    /**
     * @param array<mixed> $instances
     *
     * @return array<mixed>
     */
    public function resolveInstancesViaContainer(array $instances): array;
}
