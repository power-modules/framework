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

use Modular\Framework\Container\Contract\InstanceResolverInterface;
use Modular\Framework\Container\InstanceResolver\DefaultInstanceResolver;
use Psr\Container\ContainerInterface;

interface ConfigurableContainerInterface extends ContainerInterface
{
    /**
     * @param class-string<InstanceResolverInterface> $instanceResolver
     */
    public function set(string $id, mixed $value = null, string $instanceResolver = DefaultInstanceResolver::class): ServiceDefinition;

    public function addServiceDefinition(string $id, ServiceDefinition $serviceDefinition): ConfigurableContainerInterface;

    public function getServiceDefinition(string $id): ServiceDefinition;
}
