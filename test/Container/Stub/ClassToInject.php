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

namespace Modular\Framework\Test\Container\Stub;

class ClassToInject
{
    public string $stringDependency;

    public int $intDependency;

    public function __construct(
        string $stringDependency = 'stringDependencyValue',
        int $intDependency = 86_400,
    ) {
        $this->stringDependency = $stringDependency;
        $this->intDependency = $intDependency;
    }
}
