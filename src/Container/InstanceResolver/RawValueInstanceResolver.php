<?php

namespace Modular\Framework\Container\InstanceResolver;

use Modular\Framework\Container\Contract\InstanceResolverInterface;

class RawValueInstanceResolver implements InstanceResolverInterface
{
    public function resolve(string $id, mixed $value, array $dependencies = []): mixed
    {
        return $value;
    }
}
