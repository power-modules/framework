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

use Modular\Framework\Container\InstanceResolver\DefaultInstanceResolver;
use Modular\Framework\Container\InstanceResolver\InstanceResolverFactory;
use Modular\Framework\Container\ServiceDefinition;
use Modular\Framework\Test\Container\Stub\AnotherClassToInjectWithSetter;
use Modular\Framework\Test\Container\Stub\ClassToInject;
use PHPUnit\Framework\TestCase;

class ServiceDefinitionTest extends TestCase
{
    public function testResolveWillReturnClass(): void
    {
        $serviceDefinition = new ServiceDefinition(
            ClassToInject::class,
            ClassToInject::class,
            InstanceResolverFactory::getResolver(DefaultInstanceResolver::class),
        );
        $this->assertInstanceOf(ClassToInject::class, $serviceDefinition->resolve());
    }

    public function testResolveWillPassArgumentsToResolverAsDependencies(): void
    {
        $serviceDefinition = new ServiceDefinition(
            ClassToInject::class,
            ClassToInject::class,
            InstanceResolverFactory::getResolver(DefaultInstanceResolver::class),
        );
        $serviceDefinition->addArguments(
            [
                'MyString',
                3_600,
            ],
        );

        /** @var ClassToInject $obj */
        $obj = $serviceDefinition->resolve();

        $this->assertSame('MyString', $obj->stringDependency);
        $this->assertSame(3_600, $obj->intDependency);
    }

    public function testResolveWillCallMethodsAfterCreation(): void
    {
        $this->assertNull((new AnotherClassToInjectWithSetter())->classDependency);

        $serviceDefinition = new ServiceDefinition(
            AnotherClassToInjectWithSetter::class,
            AnotherClassToInjectWithSetter::class,
            InstanceResolverFactory::getResolver(DefaultInstanceResolver::class),
        );
        $serviceDefinition->addMethod(
            'setClassDependency',
            [
                new ClassToInject('customValueForDependency'),
            ],
        );

        /** @var AnotherClassToInjectWithSetter $obj */
        $obj = $serviceDefinition->resolve();

        $this->assertInstanceOf(ClassToInject::class, $obj->classDependency);
        $this->assertSame('customValueForDependency', $obj->classDependency->stringDependency);
        $this->assertSame(86_400, $obj->classDependency->intDependency);
    }

    public function testResolveWillThrowInvalidArgumentExceptionOnNonObject(): void
    {
        $serviceDefinition = new ServiceDefinition(
            '123',
            123,
            InstanceResolverFactory::getResolver(DefaultInstanceResolver::class),
        );
        $serviceDefinition->addMethod(
            'methodName',
            [
                'argument1',
            ],
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to to call method on non object: int');
        $serviceDefinition->resolve();
    }
}
