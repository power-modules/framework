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

namespace Modular\Framework\Test\Container\InstanceResolver;

use Modular\Framework\Container\InstanceResolver\InstanceResolverException;
use Modular\Framework\Container\InstanceResolver\InstanceViaContainerResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class InstanceViaContainerResolverTest extends TestCase
{
    public function testResolveReturnsValueFromContainer(): void
    {
        $container = new class () implements ContainerInterface {
            public function get(string $id)
            {
                return $id . '-resolved';
            }
            public function has(string $id): bool
            {
                return true;
            }
        };
        $resolver = new InstanceViaContainerResolver();
        $this->assertSame('foo-resolved', $resolver->resolve('foo', $container));
    }

    public function testResolveThrowsIfNotContainer(): void
    {
        $resolver = new InstanceViaContainerResolver();
        $this->expectException(InstanceResolverException::class);
        $resolver->resolve('foo', 'not-a-container');
    }
}
