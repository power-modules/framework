<?php

declare(strict_types=1);

namespace Modular\Framework\Test\Container\InstanceResolver;

use Modular\Framework\Container\ContainerAwareInterface;
use Modular\Framework\Container\InstanceResolver\DefaultInstanceResolver;
use Modular\Framework\Container\InstanceResolver\InstanceResolverFactory;
use Modular\Framework\Container\InstanceResolver\InstanceViaContainerResolver;
use Modular\Framework\Container\InstanceResolver\RawValueInstanceResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use TypeError;

class InstanceResolverFactoryTest extends TestCase
{
    public function testGetResolverWillThrowInvalidArgumentException(): void
    {
        $class = new class () {};
        $this->expectException(TypeError::class);
        // @phpstan-ignore-next-line
        InstanceResolverFactory::getResolver(get_class($class));
    }

    public function testGetResolverReturnsCorrectInstance(): void
    {
        $this->assertInstanceOf(DefaultInstanceResolver::class, InstanceResolverFactory::getResolver(DefaultInstanceResolver::class));
        $this->assertInstanceOf(RawValueInstanceResolver::class, InstanceResolverFactory::getResolver(RawValueInstanceResolver::class));
        $this->assertInstanceOf(InstanceViaContainerResolver::class, InstanceResolverFactory::getResolver(InstanceViaContainerResolver::class));
    }

    public function testGetResolverInjectsContainerIfAware(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $resolver = InstanceResolverFactory::getResolver(DefaultInstanceResolver::class, $container);
        $this->assertInstanceOf(DefaultInstanceResolver::class, $resolver);
        $this->assertInstanceOf(ContainerAwareInterface::class, $resolver);
    }
}
