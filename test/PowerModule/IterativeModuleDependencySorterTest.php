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

namespace Modular\Framework\Test\PowerModule;

use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\ImportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Exception\CircularDependencyException;
use Modular\Framework\PowerModule\ImportItem;
use Modular\Framework\PowerModule\IterativeModuleDependencySorter;
use PHPUnit\Framework\TestCase;

class IterativeModuleDependencySorterTest extends TestCase
{
    private IterativeModuleDependencySorter $sorter;

    protected function setUp(): void
    {
        $this->sorter = new IterativeModuleDependencySorter();
    }

    public function testSortEmptyArray(): void
    {
        $result = $this->sorter->sort([]);
        $this->assertSame([], $result);
    }

    public function testSortSingleModuleWithoutDependencies(): void
    {
        $modules = [SimpleModuleA::class];
        $result = $this->sorter->sort($modules);
        $this->assertSame($modules, $result);
    }

    public function testSortMultipleModulesWithoutDependencies(): void
    {
        $modules = [SimpleModuleA::class, SimpleModuleB::class];
        $result = $this->sorter->sort($modules);

        // Should return all modules, order may vary but all should be present
        $this->assertCount(2, $result);
        $this->assertContains(SimpleModuleA::class, $result);
        $this->assertContains(SimpleModuleB::class, $result);
    }

    public function testSortSimpleDependencyChain(): void
    {
        $modules = [ImportingModuleA::class, ExportingModuleA::class];
        $result = $this->sorter->sort($modules);

        // ExportingModuleA should come before ImportingModuleA
        $this->assertSame([ExportingModuleA::class, ImportingModuleA::class], $result);
    }

    public function testSortComplexDependencyChain(): void
    {
        // Create a dependency chain: C -> B -> A (C imports from B, B imports from A)
        $modules = [ImportingModuleC::class, ImportingModuleB::class, ExportingModuleA::class];
        $result = $this->sorter->sort($modules);

        // Should be ordered: ExportingModuleA, ImportingModuleB, ImportingModuleC
        $this->assertSame([ExportingModuleA::class, ImportingModuleB::class, ImportingModuleC::class], $result);
    }

    /**
     * This test specifically addresses the bug fix where dependency buckets
     * weren't properly pre-allocated, causing issues in complex dependency graphs.
     */
    public function testBucketPreallocationBugFix(): void
    {
        // This scenario tests the specific case where modules are processed in an order
        // that would expose the bucket pre-allocation bug:
        // - UserModule imports from both DatabaseModule and NotificationModule
        // - OrderModule imports from UserModule
        // - PaymentModule imports from OrderModule
        // The bug occurred when dependencies were referenced before their buckets were initialized

        $modules = [
            PaymentModule::class,    // Depends on OrderModule (not yet processed)
            UserModule::class,       // Depends on DatabaseModule and NotificationModule (not yet processed)
            OrderModule::class,      // Depends on UserModule (not yet processed)
            DatabaseModule::class,   // No dependencies
            NotificationModule::class, // No dependencies
        ];

        $result = $this->sorter->sort($modules);

        // Verify correct ordering: dependencies come before dependents
        $databasePos = array_search(DatabaseModule::class, $result);
        $notificationPos = array_search(NotificationModule::class, $result);
        $userPos = array_search(UserModule::class, $result);
        $orderPos = array_search(OrderModule::class, $result);
        $paymentPos = array_search(PaymentModule::class, $result);

        // DatabaseModule and NotificationModule should come before UserModule
        $this->assertLessThan($userPos, $databasePos);
        $this->assertLessThan($userPos, $notificationPos);

        // UserModule should come before OrderModule
        $this->assertLessThan($orderPos, $userPos);

        // OrderModule should come before PaymentModule
        $this->assertLessThan($paymentPos, $orderPos);
    }

    public function testCircularDependencyDetection(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency detected in modules.');

        $modules = [CircularModuleA::class, CircularModuleB::class];
        $this->sorter->sort($modules);
    }

    public function testSortWithMissingDependencies(): void
    {
        // When a module imports from a dependency not in the input list,
        // the dependency should be added automatically during processing
        // but only the originally requested modules should be in the final result
        $modules = [ImportingModuleA::class]; // Depends on ExportingModuleA (not in list)
        $result = $this->sorter->sort($modules);

        // Only the originally requested module should be in the result
        // The array keys might not be sequential, so we need to check values only
        $this->assertCount(1, $result);
        $this->assertContains(ImportingModuleA::class, $result);
    }

    public function testMultipleImportsFromSameModule(): void
    {
        $modules = [MultiImportModule::class, ExportingModuleA::class];
        $result = $this->sorter->sort($modules);

        $this->assertSame([ExportingModuleA::class, MultiImportModule::class], $result);
    }
}

// Test modules for dependency sorting

class SimpleModuleA implements PowerModule
{
    public function register(ConfigurableContainerInterface $container): void
    {
        // Simple module with no dependencies or exports
    }
}

class SimpleModuleB implements PowerModule
{
    public function register(ConfigurableContainerInterface $container): void
    {
        // Simple module with no dependencies or exports
    }
}

class ExportingModuleA implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return ['ServiceA', 'ServiceB'];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Exports services but has no dependencies
    }
}

class ImportingModuleA implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(ExportingModuleA::class, 'ServiceA')];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Imports from ExportingModuleA
    }
}

class ImportingModuleB implements PowerModule, ImportsComponents, ExportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(ExportingModuleA::class, 'ServiceA')];
    }

    public static function exports(): array
    {
        return ['ServiceC'];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Imports from ExportingModuleA and exports ServiceC
    }
}

class ImportingModuleC implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(ImportingModuleB::class, 'ServiceC')];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Imports from ImportingModuleB
    }
}

// Modules for testing the bucket pre-allocation bug fix

class DatabaseModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return ['DatabaseService'];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Base service with no dependencies
    }
}

class NotificationModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return ['NotificationService'];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Base service with no dependencies
    }
}

class UserModule implements PowerModule, ImportsComponents, ExportsComponents
{
    public static function imports(): array
    {
        return [
            ImportItem::create(DatabaseModule::class, 'DatabaseService'),
            ImportItem::create(NotificationModule::class, 'NotificationService'),
        ];
    }

    public static function exports(): array
    {
        return ['UserService'];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Depends on both DatabaseModule and NotificationModule
    }
}

class OrderModule implements PowerModule, ImportsComponents, ExportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(UserModule::class, 'UserService')];
    }

    public static function exports(): array
    {
        return ['OrderService'];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Depends on UserModule
    }
}

class PaymentModule implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(OrderModule::class, 'OrderService')];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Depends on OrderModule
    }
}

// Modules for testing circular dependencies

class CircularModuleA implements PowerModule, ImportsComponents, ExportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(CircularModuleB::class, 'ServiceB')];
    }

    public static function exports(): array
    {
        return ['ServiceA'];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Creates circular dependency with CircularModuleB
    }
}

class CircularModuleB implements PowerModule, ImportsComponents, ExportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(CircularModuleA::class, 'ServiceA')];
    }

    public static function exports(): array
    {
        return ['ServiceB'];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Creates circular dependency with CircularModuleA
    }
}

// Module for testing multiple imports from same module

class MultiImportModule implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(ExportingModuleA::class, 'ServiceA', 'ServiceB')];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Imports multiple services from the same module
    }
}
