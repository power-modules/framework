<?php

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
