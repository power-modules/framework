<?php

namespace Modular\Framework\Container\Contract;

interface CanResolveViaContainer
{
    /**
     * @param array<mixed> $instances
     *
     * @return array<mixed>
     */
    public function resolveInstancesViaContainer(array $instances): array;
}
