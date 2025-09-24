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

namespace Modular\Framework\Container;

use Psr\Container\ContainerInterface;
use RuntimeException;

trait ContainerAwareTrait
{
    private ?ContainerInterface $container = null;

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * @throws \RuntimeException
     */
    public function setContainer(ContainerInterface $container): ContainerAwareInterface
    {
        $this->container = $container;

        if ($this instanceof ContainerAwareInterface) {
            return $this;
        }

        throw new RuntimeException(sprintf(
            'Trait (%s) must be consumed by an instance of (%s)',
            __TRAIT__,
            ContainerAwareInterface::class,
        ));
    }
}
