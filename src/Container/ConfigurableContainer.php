<?php

declare(strict_types=1);

namespace Modular\Framework\Container;

use Modular\Framework\Container\Exception\ContainerException;
use Modular\Framework\Container\Exception\ServiceDefinitionNotFound;
use Modular\Framework\Container\InstanceResolver\DefaultInstanceResolver;
use Modular\Framework\Container\InstanceResolver\InstanceResolverFactory;

class ConfigurableContainer extends Container implements ConfigurableContainerInterface
{
    public function set(string $id, mixed $value = null, string $instanceResolver = DefaultInstanceResolver::class): ServiceDefinition
    {
        return $this->declaredDefinitions[$id] = new ServiceDefinition(
            $id,
            $value ?? $id,
            InstanceResolverFactory::getResolver($instanceResolver, $this),
        );
    }

    public function addServiceDefinition(string $id, ServiceDefinition $serviceDefinition): ConfigurableContainerInterface
    {
        if (array_key_exists($id, $this->declaredDefinitions) === true) {
            throw new ContainerException(
                sprintf('Service definition duplicate: %s', $id),
            );
        }

        $this->declaredDefinitions[$id] = $serviceDefinition;

        return $this;
    }

    public function getServiceDefinition(string $id): ServiceDefinition
    {
        if ($this->has($id) === false) {
            throw new ServiceDefinitionNotFound($id);
        }

        return $this->declaredDefinitions[$id];
    }
}
