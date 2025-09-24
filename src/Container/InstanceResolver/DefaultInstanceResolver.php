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
use Modular\Framework\Container\ContainerAwareTrait;
use Modular\Framework\Container\Contract\CanResolveViaContainer;
use Modular\Framework\Container\Contract\InstanceResolverInterface;

class DefaultInstanceResolver implements InstanceResolverInterface, CanResolveViaContainer, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function resolve(string $id, mixed $value, array $dependencies = []): mixed
    {
        if (is_callable($value) === true) {
            return $value(...$this->resolveInstancesViaContainer($dependencies));
        }

        if ($this->shouldPassRawValue($value) === true) {
            return $value;
        }

        /** @var string $value */
        if (class_exists($value) === false) {
            return $value;
        }

        return new $value(...$this->resolveInstancesViaContainer($dependencies));
    }

    /**
     * @param array<mixed> $instances
     *
     * @return array<mixed>
     */
    public function resolveInstancesViaContainer(array $instances): array
    {
        if (count($instances) === 0) {
            return [];
        }

        $container = $this->getContainer();

        $resolved = [];

        foreach ($instances as $dependency) {
            if ($dependency instanceof \Closure) {
                $closureParams = array_map(
                    static function (\ReflectionParameter $reflectionParameter) {
                        $reflectionType = $reflectionParameter->getType();

                        if ($reflectionType instanceof \ReflectionNamedType) {
                            return $reflectionType->getName();
                        }
                    },
                    new \ReflectionFunction($dependency)->getParameters(),
                );

                $resolved[] = $this->resolve('', $dependency, $closureParams);

                continue;
            }

            if ($this->shouldPassRawValue($dependency) === true) {
                $resolved[] = $dependency;

                continue;
            }

            /** @var class-string|string $dependency */
            if ($container !== null && $container->has($dependency) === true) {
                $resolved[] = $container->get($dependency);

                continue;
            }

            $resolved[] = $this->resolve($dependency, $dependency); // Try to create instance manually
        }

        return $resolved;
    }

    private function shouldPassRawValue(mixed $dependency): bool
    {
        return in_array(
            gettype($dependency),
            [
                'boolean',
                'integer',
                'double',
                'array',
                'object',
                'resource',
                'NULL',
            ],
        );
    }
}
