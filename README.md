
# Modular Framework

The Modular Framework provides a universal way to define standalone or interdependent libraries, aka modules. Each module has its own Dependency Injection (DI) container, ensuring clear boundaries and encapsulation.

**Key Features:**

- **Modular Architecture:** Build your application from self-contained, reusable modules.
- **Dependency Injection:** A powerful, configurable DI container for managing your services.
- **Explicit Dependencies:** A clear import/export system makes dependencies between modules obvious and manageable.
- **Performance:** Module dependency resolution is optimized with an iterative sorter and a caching layer to ensure fast application bootstrapping.
- **PSR Compliant:** Follows modern standards like PSR-4, PSR-11, and PSR-16.

## How It Works

The framework's core principle is that each module is a self-contained unit with its own DI container.

- **Exporting Components:** If a module wants to make its internal components available to other modules or client code, it should implement the `ExportsComponents` interface. This explicitly declares which components are available for external use.
- **Importing Components:** If a module depends on components from another module, it should implement the `ImportsComponents` interface. The framework will automatically resolve the dependency graph and make the required components available in the module's container.

This approach enforces clear boundaries between modules, making the framework ideal for building modular monoliths and large-scale, maintainable applications.

## Installation

Install via Composer:

```sh
composer require power-modules/framework
```

## Application Architecture Overview

The `ConfigurableContainer` uses the `ServiceDefinition` class, which allows you to provide instructions on how to instantiate your objects, inject their dependencies, and set them up via methods.

Here is an example of an application with three modules. The first is a simple module, the second exports components, and the third depends on a component from the second module.

### External Power Module Definition:

#### `VendorA\LibraryA` (Simple Module)
- Has its components registered in the module container for internal usage only.
```php
// \VendorA\LibraryA\LibraryAModule.php
class LibraryAModule implements PowerModule
{
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(LibraryAComponent::class, LibraryAComponent::class);
    }
}
```

#### `VendorB\LibraryB` (Module with Exports)
- Exports `LibraryBComponent2` by implementing the `ExportsComponents` interface.
```php
// \VendorB\LibraryB\LibraryBModule.php
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

#### `VendorC\LibraryC` (Module with Imports)
- Depends on `LibraryBComponent2` from `LibraryBModule` by implementing the `ImportsComponents` interface.
```php
// \VendorC\LibraryC\LibraryCModule.php
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

## Usage Example

```php
// /app/project/public/index.php
<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Modular\Framework\App\ModularAppFactory;

$modularApp = ModularAppFactory::forAppRoot(__DIR__.'/../');

// The framework automatically resolves the dependency order
$modularApp->registerModules([
    \VendorA\LibraryA\LibraryAModule::class,
    \VendorC\LibraryC\LibraryCModule::class, // Depends on LibraryBModule
]);

// You can get any exported component directly from the app's root container
$service = $modularApp->get(\VendorB\LibraryB\LibraryBComponent2::class);
// $service->doSomething();
```

## The Resulting `App` Structure

```
+ \Modular\Framework\App\ModularApp
    + root-container
        - \VendorA\LibraryA\LibraryAModule::class => ContainerInterface<LibraryAModule>
        - \VendorB\LibraryB\LibraryBModule::class => ContainerInterface<LibraryBModule>
        - \VendorC\LibraryC\LibraryCModule::class => ContainerInterface<LibraryCModule>
        - \VendorB\LibraryB\LibraryBComponent2::class => (alias for the service in LibraryBModule's container)
```

## API Reference

### Core Interfaces

#### PowerModule Interface

```php
interface PowerModule
{
    public function register(ConfigurableContainerInterface $container): void;
}
```

#### ConfigurableContainer Interface

The "set" method is your entry point to define services in the container.

```php
interface ConfigurableContainerInterface extends ContainerInterface
{
    public function set(string $id, mixed $value = null, string $instanceResolver = DefaultInstanceResolver::class): ServiceDefinition;

    public function addServiceDefinition(string $id, ServiceDefinition $serviceDefinition): ConfigurableContainerInterface;

    public function getServiceDefinition(string $id): ServiceDefinition;
}
```

#### App Interface

```php
class App implements ContainerInterface
{
    public function registerModules(array $powerModuleClassNames): self;
    
    public function addPowerModuleSetup(CanSetupPowerModule $canSetupPowerModule): self;
    
    public function get(string $id): mixed;
    
    public function has(string $id): bool;
}
```

### Module Contracts

- **`ExportsComponents`**: Implement to make module components available to other modules
- **`ImportsComponents`**: Implement to depend on components from other modules
- **`CanCreatePowerModuleInstance`**: Implement to customize module instantiation
- **`CanSetupPowerModule`**: Implement to customize module setup behavior (see [Power Modules Router Documentation](https://github.com/power-modules/router) for an example)

### Service Definition

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

#### Example Usage

```php
// Exporting components
class MyModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [MyService::class, MyMiddleware::class];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(MyRepository::class, MyRepository::class);
        $container->set(
            MyService::class,
            MyService::class,
        )->addArguments([MyRepository::class]);
        $container->set(
            MyMiddleware::class,
            MyMiddleware::class,
        )->addArguments([MyService::class]);
    }
}

// Importing components
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
                ->middleware([MyMiddleware::class]),
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // MyService and MyMiddleware are now available for injection
        $container->set(
            ConsumerService::class,
            ConsumerService::class,
        )->addArguments([
            MyService::class,
        ]);
    }
}
```

## Development & Testing

Run tests, code style checks, and static analysis using the Makefile:

```sh
make test         # Run PHPUnit tests
make codestyle    # Check code style with PHP CS Fixer
make phpstan      # Run static analysis
make devcontainer # Build development container
```

## License

MIT License. See [LICENSE](LICENSE) for details.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat(...): added amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Support

- [Power Modules Framework Documentation](https://github.com/power-modules/framework)
- [League/Route Documentation](https://route.thephpleague.com/)
- [PSR-15 Middleware Documentation](https://www.php-fig.org/psr/psr-15/)