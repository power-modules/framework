<?php

declare(strict_types=1);

namespace Modular\Framework\Container\Contract;

interface InstanceResolverInterface
{
    /**
     * @param array<mixed> $dependencies
     */
    public function resolve(string $id, mixed $value, array $dependencies = []): mixed;
}
