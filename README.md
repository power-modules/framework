# Modular Framework

[![CI](https://github.com/power-modules/framework/actions/workflows/php.yml/badge.svg)](https://github.com/power-modules/framework/actions/workflows/php.yml)
[![Packagist Version](https://img.shields.io/packagist/v/power-modules/framework)](https://packagist.org/packages/power-modules/framework)
[![PHP Version](https://img.shields.io/packagist/php-v/power-modules/framework)](https://packagist.org/packages/power-modules/framework)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-blue)](#)

A powerful modular PHP framework where each module is a self-contained unit with its own Dependency Injection container. Modules interact via explicit import/export contracts, yielding clear boundaries, predictable composition, and excellent testability.

## Stability

This project is early production-ready. It’s designed and tested for small-to-medium applications; broader-scale validation is ongoing. Interfaces may evolve with feedback.

## 🚀 Key Innovations

- True Module Encapsulation: each module has its own isolated DI container with clear separation of concerns
- Explicit Import/Export System: dependencies are declared via contracts—no guessing
- Performance-Oriented: dependency sorting and caching for fast bootstraps
- PSR-Friendly: embraces PSR-4, PSR-11, PSR-16 with modern PHP 8+ typing
- Built for Modular Monoliths: boundaries first; extraction to services later if needed

## 🎯 Perfect For

- Enterprise Applications: large apps with clear module boundaries
- Modular Monoliths: keep complexity manageable as teams grow
- Reusable Libraries: self-contained components with explicit contracts
- Microservice Preparation: modules can be extracted when needed
- Team Collaboration: teams work independently within isolated modules

## How It Works

Each module owns:
- Its own DI container (internal services stay private by default)
- Optional exports so other modules (or app code) can consume selected services
- Optional imports to declare dependencies on other modules’ exports

Contracts:
- ExportsComponents: a module lists which services it exports
- ImportsComponents: a module lists what it needs from another module via ImportItem

The framework:
- Resolves the import/export graph
- Validates missing or cyclic dependencies
- Sorts modules deterministically
- Builds a root container that exposes exported services as aliases and each module’s container under its module class name

## Installation

Install via Composer:

```sh
composer require power-modules/framework
```

## 📚 Documentation

- **[Getting Started](docs/hello-module.md)** - Build your first module with a simple hello world example
- **[Architecture Overview](docs/lifecycle.md)** - Understanding the module lifecycle and framework internals

## Quick Start

```php
// /app/project/public/index.php
<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Modular\Framework\App\ModularAppBuilder;

$app = new ModularAppBuilder(__DIR__ . '/../')->build();

// The framework automatically resolves dependency order
$app->registerModules([
    \VendorA\LibraryA\LibraryAModule::class,
    \VendorC\LibraryC\LibraryCModule::class, // Depends on LibraryBModule
]);

// You can get any exported component directly from the app's root container
$service = $app->get(\VendorB\LibraryB\LibraryBComponent2::class);
// $service->doSomething();
```

## Application Architecture Overview

The ConfigurableContainer uses ServiceDefinition to declare how to instantiate objects, inject dependencies, and call setup methods.

Here is an example with three modules. The first is a simple module, the second exports a component, and the third imports that component.

### External Power Module Definitions

#### VendorA\LibraryA (Simple Module)
- Registers components for internal usage only.

```php
// \VendorA\LibraryA\LibraryAModule.php
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;

class LibraryAModule implements PowerModule
{
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(LibraryAComponent::class, LibraryAComponent::class);
    }
}
```

#### VendorB\LibraryB (Module with Exports)
- Exports LibraryBComponent2.

```php
// \VendorB\LibraryB\LibraryBModule.php
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\ExportsComponents;

class LibraryBModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [LibraryBComponent2::class];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(LibraryBComponent1::class, LibraryBComponent1::class);
        $container->set(LibraryBComponent2::class, LibraryBComponent2::class);
    }
}
```

#### VendorC\LibraryC (Module with Imports)
- Imports LibraryBComponent2 from LibraryBModule.

```php
// \VendorC\LibraryC\LibraryCModule.php
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\ImportsComponents;
use Modular\Framework\PowerModule\ImportItem;

class LibraryCModule implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [
            ImportItem::create(LibraryBModule::class, LibraryBComponent2::class),
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // LibraryBComponent2 is automatically available for injection here
        $container->set(LibraryCComponent::class, LibraryCComponent::class)
            ->addArguments([LibraryBComponent2::class]);
    }
}
```

## The Resulting App Structure

```
+ \Modular\Framework\App\App
    + root-container
        - \VendorA\LibraryA\LibraryAModule::class => ContainerInterface<LibraryAModule>
        - \VendorB\LibraryB\LibraryBModule::class => ContainerInterface<LibraryBModule>
        - \VendorC\LibraryC\LibraryCModule::class => ContainerInterface<LibraryCModule>
        - \VendorB\LibraryB\LibraryBComponent2::class => alias to LibraryBModule’s container service
```

## 🛠️ Developer Experience

- Minimal Configuration: list your modules in App::registerModules() and the framework resolves dependencies
- Type Safety: modern PHP 8+ typing throughout
- IDE Friendly: explicit interfaces with great autocompletion
- Testing Ready: isolated containers make mocking straightforward
- Clear Errors: helpful messages on dependency resolution failures

## API Reference

### Core Interfaces

#### PowerModule

```php
interface PowerModule
{
    public function register(ConfigurableContainerInterface $container): void;
}
```

#### ConfigurableContainerInterface

The set method defines services in the container.

```php
interface ConfigurableContainerInterface extends Psr\Container\ContainerInterface
{
    public function set(string $id, mixed $value = null, string $instanceResolver = DefaultInstanceResolver::class): ServiceDefinition;

    public function addServiceDefinition(string $id, ServiceDefinition $serviceDefinition): ConfigurableContainerInterface;

    public function getServiceDefinition(string $id): ServiceDefinition;
}
```

#### App

```php
class App implements Psr\Container\ContainerInterface
{
    /**
     * @param array<class-string<PowerModule>> $powerModuleClassNames
     */
    public function registerModules(array $powerModuleClassNames): self;

    public function addPowerModuleSetup(CanSetupPowerModule $canSetupPowerModule): self;

    /** @template T @param class-string<T> $id @return T */
    public function get(string $id);

    public function has(string $id): bool;
}
```

### Builder

Use ModularAppBuilder to construct an App with optional customizations.

```php
use Modular\Framework\App\ModularAppBuilder;
use Modular\Framework\App\Config\Config;
use Modular\Framework\App\Config\Setting;

$app = new ModularAppBuilder(__DIR__)
    // Optional: customize config, cache path, module resolver, etc.
    // ->withConfig(Config::forAppRoot(__DIR__)->set(Setting::CachePath, sys_get_temp_dir()))
    ->build();
```

### ServiceDefinition

```php
class ServiceDefinition
{
    public function addArguments(array $dependencies): self;

    public function addMethod(string $methodName, mixed $args): self;

    public function resolve(): mixed;
}
```

### Import/Export System

#### ImportItem

```php
class ImportItem
{
    public static function create(string $powerModuleName, string ...$itemsToImport): self;
}
```

#### Example

```php
// Exporting components
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\ExportsComponents;

class MyModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [MyService::class, MyMiddleware::class];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(MyRepository::class, MyRepository::class);
        $container->set(MyService::class, MyService::class)
            ->addArguments([MyRepository::class]);
        $container->set(MyMiddleware::class, MyMiddleware::class)
            ->addArguments([MyService::class]);
    }
}

// The following example demonstrates integration with the [power-modules/router](https://github.com/power-modules/router) package, which is an optional extension for HTTP routing. If you haven't installed it yet, add it via Composer: composer require power-modules/router
use Modular\Framework\PowerModule\Contract\ImportsComponents;
use Modular\Framework\PowerModule\ImportItem;
use Modular\Router\Contract\HasRoutes;
use Modular\Router\Route;

class ConsumerModule implements PowerModule, ImportsComponents, HasRoutes
{
    public static function imports(): array
    {
        return [
            ImportItem::create(MyModule::class, MyService::class, MyMiddleware::class),
        ];
    }

    public function getRoutes(): array
    {
        return [
            Route::get('/some-endpoint', [ConsumerService::class, 'handle'])
                ->addMiddleware([MyMiddleware::class]),
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // MyService and MyMiddleware are available for injection
        $container->set(ConsumerService::class, ConsumerService::class)
            ->addArguments([MyService::class]);
    }
}
```


## Development & Testing

Run tests, code style checks, and static analysis via the Makefile:

```sh
make test         # PHPUnit tests
make codestyle    # PHP CS Fixer
make phpstan      # Static analysis
make devcontainer # Build development container
```

## Ecosystem

- Router integration and routes setup extensions are available in Power Modules Router. See the [Power Modules Router](https://github.com/power-modules/router) for details.

## License

MIT License. See [LICENSE](LICENSE) for details.

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit: `git commit -m 'feat(...): add amazing feature'`
4. Push: `git push origin feature/amazing-feature`
5. Open a Pull Request

## Support

- Framework repository: [power-modules/framework](https://github.com/power-modules/framework)
- Router repository: [power-modules/router](https://github.com/power-modules/router)