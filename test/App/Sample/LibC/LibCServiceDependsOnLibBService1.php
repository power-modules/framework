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

namespace Modular\Framework\Test\App\Sample\LibC;

use Modular\Framework\Test\App\Sample\LibB\LibBService1;

class LibCServiceDependsOnLibBService1
{
    public function __construct(
        public readonly LibBService1 $libBService1,
    ) {
    }
}
