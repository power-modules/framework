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

use Modular\Framework\PowerModule\Exception\CircularDependencyException;

interface ModuleDependencySorter
{
    /**
     * Sorts the given module class names based on their dependencies.
     *
     * @param array<class-string<PowerModule>> $powerModuleClassNames
     * @return array<class-string<PowerModule>>
     * @throws CircularDependencyException
     */
    public function sort(array $powerModuleClassNames): array;
}
