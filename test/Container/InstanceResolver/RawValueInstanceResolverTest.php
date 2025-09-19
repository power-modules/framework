<?php

declare(strict_types=1);

namespace Modular\Framework\Test\Container\InstanceResolver;

use Modular\Framework\Container\InstanceResolver\RawValueInstanceResolver;
use PHPUnit\Framework\TestCase;

class RawValueInstanceResolverTest extends TestCase
{
    public function testResolveReturnsRawValue(): void
    {
        $resolver = new RawValueInstanceResolver();
        $this->assertSame(123, $resolver->resolve('id', 123));
        $this->assertSame('foo', $resolver->resolve('id', 'foo'));
        $this->assertSame([1,2,3], $resolver->resolve('id', [1,2,3]));
    }
}
