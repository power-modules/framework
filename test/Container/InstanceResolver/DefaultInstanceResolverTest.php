<?php

declare(strict_types=1);

namespace Modular\Framework\Test\Container\InstanceResolver;

use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\Container\InstanceResolver\DefaultInstanceResolver;
use Modular\Framework\Container\ServiceDefinition;
use PHPUnit\Framework\TestCase;

class DefaultInstanceResolverTest extends TestCase
{
    public function testResolveReturnsCallableResult(): void
    {
        $resolver = new DefaultInstanceResolver();
        $result = $resolver->resolve('', fn ($a, $b) => $a + $b, [2, 3]);
        $this->assertSame(5, $result);
    }

    public function testResolveReturnsRawValue(): void
    {
        $resolver = new DefaultInstanceResolver();
        $this->assertSame(42, $resolver->resolve('', 42));
        $this->assertSame('foo', $resolver->resolve('', 'foo'));
        $this->assertSame([1,2,3], $resolver->resolve('', [1,2,3]));
    }

    public function testResolveReturnsNewInstance(): void
    {
        $resolver = new DefaultInstanceResolver();
        $class = new class () {
            public function __construct(public int $a = 1, public int $b = 2)
            {
            }
        };
        $className = get_class($class);
        $instance = $resolver->resolve('', $className, []);
        $this->assertInstanceOf($className, $instance);
        $this->assertSame(1, $instance->a);
        $this->assertSame(2, $instance->b);
    }

    public function testResolveInstancesViaContainerWithContainer(): void
    {
        $container = new class () implements ConfigurableContainerInterface {
            public function has($id): bool
            {
                return $id === 'foo';
            }
            public function get($id)
            {
                return $id === 'foo' ? 'bar' : null;
            }
            public function set(string $id, mixed $value = null, string $instanceResolver = DefaultInstanceResolver::class): ServiceDefinition
            {
                return new ServiceDefinition($id, $value, new DefaultInstanceResolver());
            }
            public function addServiceDefinition(string $id, ServiceDefinition $serviceDefinition): ConfigurableContainerInterface
            {
                return $this;
            }
            public function getServiceDefinition(string $id): ServiceDefinition
            {
                return new ServiceDefinition($id, null, new DefaultInstanceResolver());
            }
        };
        $resolver = new DefaultInstanceResolver();
        $resolver->setContainer($container);
        $result = $resolver->resolveInstancesViaContainer(['foo', 'baz']);
        $this->assertSame(['bar', 'baz'], $result);
    }
}
