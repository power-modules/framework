<?php

namespace Modular\Framework\Container\InstanceResolver;

use Modular\Framework\Container\Contract\InstanceResolverInterface;
use Psr\Container\ContainerInterface;

class InstanceViaContainerResolver implements InstanceResolverInterface
{
    public function resolve(string $id, mixed $value, array $dependencies = []): mixed
    {
        if (!$value instanceof ContainerInterface) {
            throw new InstanceResolverException(
                sprintf('The definition is not an instance of %s', ContainerInterface::class),
            );
        }

        return $value->get($id);
    }
}
