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

use Modular\Framework\Container\ConfigurableContainer;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\Container\InstanceResolver\DefaultInstanceResolver;
use Modular\Framework\Test\Container\Stub\AnotherClassToInject;
use Modular\Framework\Test\Container\Stub\ClassToInject;
use PHPUnit\Framework\TestCase;

class ConfigurableContainerTest extends TestCase
{
    public function testHasWillReturnTrueAfterSet(): void
    {
        $container = $this->getContainer();
        $this->assertFalse($container->has(ClassToInject::class));

        $container->set(ClassToInject::class, ClassToInject::class);
        $this->assertTrue($container->has(ClassToInject::class));
    }

    public function testGetWillReturnTheSameObject(): void
    {
        $container = $this->getContainer();

        $container->set(ClassToInject::class, ClassToInject::class);

        $obj = $container->get(ClassToInject::class);

        $this->assertInstanceOf(ClassToInject::class, $obj);
        $this->assertSame($obj, $container->get(ClassToInject::class));
    }

    public function testHasWillReturnTrueForClosures(): void
    {
        $container = $this->getContainer();
        $this->assertFalse($container->has(ClassToInject::class));

        $container->set(
            ClassToInject::class,
            function (): ClassToInject {
                return new ClassToInject();
            },
        );

        $this->assertTrue($container->has(ClassToInject::class));
    }

    public function testGetWillResolveClosuresOnlyOnce(): void
    {
        $container = $this->getContainer();
        $this->assertFalse($container->has('closureId'));

        $container->set(
            'closureId',
            function (): ClassToInject {
                return new ClassToInject();
            },
            DefaultInstanceResolver::class,
        );

        $this->assertTrue($container->has('closureId'));

        $obj = $container->get('closureId');

        $this->assertInstanceOf(ClassToInject::class, $obj);
        $this->assertSame($obj, $container->get('closureId'));
    }

    public function testGetWillResolveClosuresWithDependencies(): void
    {
        $container = $this->getContainer();
        $this->assertFalse($container->has(ClassToInject::class));

        $container->set(ClassToInject::class, ClassToInject::class);
        $container->set(
            'closureWithDependency',
            function (ClassToInject $classToInject): AnotherClassToInject {
                return new AnotherClassToInject($classToInject);
            },
            DefaultInstanceResolver::class,
        )->addArguments(
            [
                ClassToInject::class,
            ],
        );

        $obj = $container->get('closureWithDependency');
        $this->assertInstanceOf(AnotherClassToInject::class, $obj);

        /** @var AnotherClassToInject $obj */
        $this->assertInstanceOf(ClassToInject::class, $obj->classDependency);
    }

    public function testSetShouldUseIdIfValueIsNull(): void
    {
        $container = $this->getContainer();
        $this->assertFalse($container->has(ClassToInject::class));

        $container->set(ClassToInject::class);
        $this->assertTrue($container->has(ClassToInject::class));

        $obj = $container->get(ClassToInject::class);
        $this->assertInstanceOf(ClassToInject::class, $obj);
    }

    private function getContainer(): ConfigurableContainerInterface
    {
        return new ConfigurableContainer();
    }
}
