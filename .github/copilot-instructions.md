# Modular Framework - AI Coding Agent Instructions

This document provides guidance for AI coding agents to effectively contribute to the Modular Framework codebase.

## Big Picture Architecture

The Modular Framework is designed to build modular PHP applications where each module is a self-contained unit with its own Dependency Injection (DI) container, promoting encapsulation and clear boundaries.

### Core Concepts

- **Modules (`PowerModule`):** The fundamental building blocks of the framework. Each module has its own DI container and can register its own components. Key interface: `\Modular\Framework\PowerModule\Contract\PowerModule`.
- **Dependency Injection (`Container`):** The framework uses a custom DI container (`\Modular\Framework\Container\ConfigurableContainer`) that allows for fine-grained control over object instantiation and dependency management. The `\Modular\Framework\Container\ServiceDefinition` class is used to define how components are created and configured.
- **Import/Export Mechanism:** Modules can share components with each other through an explicit import/export mechanism.
    - **Exporting:** A module can expose its components to other modules by implementing the `\Modular\Framework\PowerModule\Contract\ExportsComponents` interface.
    - **Importing:** A module can consume components from other modules by implementing the `\Modular\Framework\PowerModule\Contract\ImportsComponents` interface. This makes dependencies between modules explicit and controlled.
- **Application (`App`):** The `\Modular\Framework\App\App` class is the entry point of the application. It is responsible for registering modules and managing the root DI container.
- **Dependency Sorting:** Module dependencies are resolved using an iterative topological sort algorithm (`\Modular\Framework\PowerModule\IterativeModuleDependencySorter`), which is then cached to improve performance on subsequent requests.
- **Builder Pattern:** Applications are created using `ModularAppBuilder` with fluent configuration methods for dependency injection, caching, and module registration.

### Module Design Patterns

**Simple Module (no dependencies):**
```php
class SimpleModule implements PowerModule
{
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(MyService::class, MyService::class);
    }
}
```

**Exporting Module:**
```php
class ExportingModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [PublicService::class];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(PrivateService::class, PrivateService::class);
        $container->set(PublicService::class, PublicService::class)
            ->addArguments([PrivateService::class]);
    }
}
```

**Importing Module:**
```php
class ImportingModule implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(ExportingModule::class, PublicService::class)];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        // PublicService is automatically available for injection
        $container->set(ConsumerService::class, ConsumerService::class)
            ->addArguments([PublicService::class]);
    }
}
```

**Application Builder Pattern:**
```php
$app = new ModularAppBuilder(__DIR__)
    ->withConfig(Config::forAppRoot(__DIR__)->set(Setting::CachePath, '/path/to/cache'))
    ->withModules(ExportingModule::class, ImportingModule::class)
    ->build();

// Access exported services through the app container
$service = $app->get(PublicService::class);
```

## Key Components and Directories

- `src/PowerModule/`: Contains the core interfaces and classes for creating modules (`PowerModule`, `ExportsComponents`, `ImportsComponents`, `ModuleDependencySorter`).
- `src/Container/`: Implements the Dependency Injection container (`ConfigurableContainer`, `ServiceDefinition`).
- `src/App/`: Contains the application builder (`ModularAppBuilder`) and the main application class (`App`).
- `src/Config/`: Handles configuration loading with the `ConfigModule` and `Loader`.
- `src/Cache/`: Contains the PSR-16 cache implementation (`FilesystemCache`).
- `test/`: Contains unit tests organized by component, with sample modules in `test/App/Sample/`.

## Testing Conventions

- Test modules in `test/App/Sample/` demonstrate proper module patterns: `LibAModule` (exports), `LibBModule` (simple), `LibCModule` (imports)
- Each test module has its own directory with service classes and module definition
- Tests use `ModularAppBuilder` with temporary cache paths: `->withConfig(Config::forAppRoot(__DIR__)->set(Setting::CachePath, sys_get_temp_dir()))`
- Verify export isolation: internal services should not be accessible from the app container
- Use `$app->has(ServiceClass::class)` to test service availability
- Services are singletons within their containers: `assertSame($instance1, $instance2)`

## Developer Workflows

The project uses a `Makefile` to streamline common development tasks:

```sh
make test         # Run PHPUnit tests (no coverage)
make codestyle    # Check PHP CS Fixer compliance
make phpstan      # Run static analysis with PHPStan level 8
make devcontainer # Build development container
```

## ServiceDefinition Patterns

The `ServiceDefinition` class supports method chaining for configuration:

```php
$container->set(ServiceClass::class, ServiceClass::class)
    ->addArguments([DependencyClass::class])
    ->addMethod('setLogger', [LoggerInterface::class]);
```

**Method injection:** Use `addMethod()` for setter injection after constructor injection.
**Arguments resolution:** Arguments are automatically resolved from the container using class names.

## Code Conventions

- **Strict Types:** All files use `declare(strict_types=1);`
- **PSR Standards:** PSR-4 autoloading, PSR-11 container interoperability, PSR-16 simple caching
- **PHP 8.4+:** Modern PHP features are utilized throughout the codebase
- **Interface-First:** Components are typically defined by interfaces first
- **Dependency Injection:** Constructor injection is preferred; use `ServiceDefinition::addArguments()`
- **Module Encapsulation:** Only exported services should be accessible outside a module's container

When adding new features or fixing bugs, ensure that new modules follow the encapsulation principles and that dependencies between modules are explicitly defined through the import/export mechanism.
