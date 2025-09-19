<?php

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
