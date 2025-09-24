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
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{
    public function testHasShouldReturnFalse(): void
    {
        $container = $this->getContainer();
        $this->assertFalse($container->has('asd'));
    }

    public function testGetShouldThrowNotFoundExceptionInterfaceIfIdIsNotString(): void
    {
        $container = $this->getContainer();
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Service definition with id "asd" was not found.');
        $container->get('asd');
    }

    private function getContainer(): ContainerInterface
    {
        return new Container();
    }
}
