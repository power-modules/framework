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

namespace Modular\Framework\PowerModule;

use Modular\Framework\PowerModule\Contract\ImportsComponents;
use Modular\Framework\PowerModule\Contract\ModuleDependencySorter;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Exception\CircularDependencyException;

class IterativeModuleDependencySorter implements ModuleDependencySorter
{
    /**
     * @param array<class-string<PowerModule>> $powerModuleClassNames
     * @return array<class-string<PowerModule>>
     * @throws CircularDependencyException
     */
    public function sort(array $powerModuleClassNames): array
    {
        $dependencies = [];
        $dependencyOf = [];
        $queue = new \SplQueue();

        $allModules = $powerModuleClassNames;

        // Build the dependency graph
        foreach ($allModules as $module) {
            if (!isset($dependencies[$module])) {
                $dependencies[$module] = [];
                $dependencyOf[$module] = [];
            }

            if (is_a($module, ImportsComponents::class, true)) {
                $imports = $module::imports();
                foreach ($imports as $import) {
                    $dependency = $import->moduleName;
                    if (!in_array($dependency, $allModules, true)) {
                        $allModules[] = $dependency; // Add missing dependencies to the list
                    }
                    $dependencies[$module][] = $dependency;
                    $dependencyOf[$dependency][] = $module;
                }
            }
        }

        // Initialize the queue with modules that have no dependencies
        foreach ($allModules as $module) {
            if (empty($dependencies[$module])) {
                $queue->enqueue($module);
            }
        }

        $sorted = [];
        while (!$queue->isEmpty()) {
            $module = $queue->dequeue();
            $sorted[] = $module;

            if (!isset($dependencyOf[$module])) {
                continue;
            }

            foreach ($dependencyOf[$module] as $dependentModule) {
                // Remove the edge from the graph
                $dependencies[$dependentModule] = array_filter(
                    $dependencies[$dependentModule],
                    fn ($dep) => $dep !== $module,
                );

                // If the dependent module has no other dependencies, add it to the queue
                if (empty($dependencies[$dependentModule])) {
                    $queue->enqueue($dependentModule);
                }
            }
        }

        if (count($sorted) !== count($allModules)) {
            throw new CircularDependencyException('Circular dependency detected in modules.');
        }

        // Filter out modules that were not in the original list
        return array_filter($sorted, fn ($module) => in_array($module, $powerModuleClassNames, true));
    }
}
