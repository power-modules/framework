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

use Modular\Framework\Container\Exception\ServiceDefinitionNotFound;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array<string,mixed>
     */
    protected array $resolvedDefinitions = [];

    /**
     * @var array<string,ServiceDefinition>
     */
    protected array $declaredDefinitions = [];

    public function get(string $id)
    {
        if ($this->has($id) === false) {
            throw new ServiceDefinitionNotFound($id);
        }

        if (array_key_exists($id, $this->resolvedDefinitions) === false) {
            $this->resolvedDefinitions[$id] = $this->declaredDefinitions[$id]->resolve();
        }

        return $this->resolvedDefinitions[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->declaredDefinitions);
    }
}
