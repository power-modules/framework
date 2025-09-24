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

use Modular\Framework\Container\Contract\CanResolveViaContainer;
use Modular\Framework\Container\Contract\InstanceResolverInterface;

class ServiceDefinition
{
    /**
     * @var array<mixed>
     */
    private array $arguments = [];

    /**
     * @var array<string, mixed>
     */
    private array $methods = [];

    public function __construct(
        private string $definitionName,
        private mixed $value,
        private InstanceResolverInterface $instanceResolver,
    ) {
    }

    /**
     * Resolve the service value from its definition.
     */
    public function resolve(): mixed
    {
        $instance = $this->instanceResolver->resolve($this->definitionName, $this->value, $this->arguments);

        if (count($this->methods) > 0) {
            if (is_object($instance) === false) {
                throw new \InvalidArgumentException(sprintf('Trying to to call method on non object: %s', gettype($instance)));
            }

            foreach ($this->methods as $methodName => $args) {
                if ($this->instanceResolver instanceof CanResolveViaContainer) {
                    $args = $this->instanceResolver->resolveInstancesViaContainer($args);
                }

                // @phpstan-ignore-next-line
                call_user_func_array([$instance, $methodName], $args);
            }
        }

        return $instance;
    }

    /**
     * Arguments to be passed to the constructor.
     *
     * @param array<mixed> $dependencies
     */
    public function addArguments(array $dependencies): self
    {
        foreach ($dependencies as $dependency) {
            $this->arguments[] = $dependency;
        }

        return $this;
    }

    /**
     * Method to call after initialization.
     */
    public function addMethod(string $methodName, mixed $args): self
    {
        $this->methods[$methodName] = $args;

        return $this;
    }
}
