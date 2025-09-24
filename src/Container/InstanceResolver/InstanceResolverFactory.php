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

namespace Modular\Framework\Container\InstanceResolver;

use Modular\Framework\Container\ContainerAwareInterface;
use Modular\Framework\Container\Contract\InstanceResolverInterface;
use Psr\Container\ContainerInterface;

class InstanceResolverFactory
{
    /**
     * @param class-string<InstanceResolverInterface> $instanceResolverClassName
     */
    public static function getResolver(string $instanceResolverClassName, ?ContainerInterface $container = null): InstanceResolverInterface
    {
        $resolver = self::getResolverInstance($instanceResolverClassName);

        if ($container !== null && $resolver instanceof ContainerAwareInterface) {
            $resolver->setContainer($container);
        }

        return $resolver;
    }

    /**
     * @param class-string<InstanceResolverInterface> $instanceResolverClassName
     */
    private static function getResolverInstance(string $instanceResolverClassName): InstanceResolverInterface
    {
        return new $instanceResolverClassName();
    }
}
