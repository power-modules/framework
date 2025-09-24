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

namespace Modular\Framework\Test\Container;

use Modular\Framework\Container\Container;
use Modular\Framework\Container\ContainerAwareTrait;
use PHPUnit\Framework\TestCase;

class ContainerAwareTraitTest extends TestCase
{
    public function testSetContainerWillThrowRuntimeException(): void
    {
        $class = new class () {
            use ContainerAwareTrait;
        };
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Trait (Modular\Framework\Container\ContainerAwareTrait) must be consumed by an instance of (Modular\Framework\Container\ContainerAwareInterface)');
        $class->setContainer(new Container());
    }
}
