# Modular Framework - AI Coding Agent Instructions

This document provides guidance for AI coding agents to effectively contribute to the Modular Framework codebase.

## Big Picture Architecture

The Modular Framework is a **general-purpose modular architecture framework** for PHP that builds applications where each module is a self-contained unit with its own Dependency Injection (DI) container, promoting true encapsulation and clear boundaries. It's designed for any PHP system: CLI tools, data pipelines, background processors, web APIs, and complex applications that benefit from modular design.

### Core Concepts

- **Modules (`PowerModule`):** The fundamental building blocks of the framework. Each module has its own isolated DI container and can register its own components. Key interface: `\Modular\Framework\PowerModule\Contract\PowerModule`.
- **Dependency Injection (`Container`):** The framework uses a custom DI container (`\Modular\Framework\Container\ConfigurableContainer`) that allows for fine-grained control over object instantiation and dependency management. The `\Modular\Framework\Container\ServiceDefinition` class is used to define how components are created and configured.
- **Import/Export Mechanism:** Modules can share components with each other through an explicit import/export mechanism that makes module relationships visible and controlled.
    - **Exporting:** A module can expose its components to other modules by implementing the `\Modular\Framework\PowerModule\Contract\ExportsComponents` interface.
    - **Importing:** A module can consume components from other modules by implementing the `\Modular\Framework\PowerModule\Contract\ImportsComponents` interface. This makes dependencies between modules explicit and controlled.
- **PowerModuleSetup:** The framework's most powerful feature - a mechanism that allows extending module functionality without breaking encapsulation. This is how the import/export system itself is built! Extensions can add capabilities like routing, events, validation, etc. across ALL modules automatically.
- **Application (`App`):** The `\Modular\Framework\App\App` class is the entry point of the application. It is responsible for registering modules and managing the root DI container.
- **Dependency Sorting:** Module dependencies are resolved using an iterative topological sort algorithm (`\Modular\Framework\PowerModule\IterativeModuleDependencySorter`), which is then cached to improve performance on subsequent requests.
- **Builder Pattern:** Applications are created using `ModularAppBuilder` with fluent configuration methods for dependency injection, caching, module registration, and PowerModuleSetup extensions.
- **Microservice Evolution:** The framework is designed with a clear evolution path from modular monolith to microservices, where module boundaries naturally become service boundaries.

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

**Application Builder Pattern with PowerModuleSetup:**
```php
$app = new ModularAppBuilder(__DIR__)
    ->withConfig(Config::forAppRoot(__DIR__)->set(Setting::CachePath, '/path/to/cache'))
    ->withModules(ExportingModule::class, ImportingModule::class)
    ->addPowerModuleSetup(new RoutingSetup())    // Adds HTTP routing to modules implementing HasRoutes interface
    ->addPowerModuleSetup(new EventBusSetup())   // Pulls module events into a central event bus
    ->build();

// Access exported services through the app container
$service = $app->get(PublicService::class);
```

**PowerModuleSetup Extension Pattern:**
```php
// PowerModuleSetup allows extending module functionality without breaking encapsulation
class CustomSetup implements CanSetupPowerModule
{
    public function setup(PowerModuleSetupDto $powerModuleSetupDto): void
    {
        // Add capabilities to ALL modules automatically
        // This pattern is used by extensions like power-modules/router
    }
}
```

## Key Features for AI Development

When working with the framework, keep these key architectural benefits in mind:

- **True Encapsulation**: Each module has its own isolated DI container, preventing accidental coupling
- **Explicit Dependencies**: Import/export contracts make module relationships visible and controllable
- **PowerModuleSetup Magic**: Extensions can add functionality to all modules without breaking encapsulation
- **Microservice Ready**: Module boundaries are designed to become service boundaries naturally
- **Plugin-Ready Architecture**: Third-party modules can extend functionality safely
- **Team Scalability**: Different teams can own different modules independently
- **Better Testing**: Modules can be tested in isolation with their own containers

## Microservice Evolution Path

The framework is designed with a clear evolution path from modular monolith to microservices:

**Today (Modular Monolith):**
```php
class UserModule implements PowerModule, ExportsComponents {
    public static function exports(): array {
        return [UserService::class];
    }
}

class OrderModule implements PowerModule, ImportsComponents {
    public static function imports(): array {
        return [ImportItem::create(UserModule::class, UserService::class)];
    }
}
```

**Tomorrow (Microservices):**
- `UserModule` → User API service
- `OrderModule` → Order API service  
- Import/export contracts → HTTP API contracts
- Zero architectural changes needed!

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
- Test PowerModuleSetup extensions to ensure they work across all modules

## Ecosystem and Extensions

The framework supports a rich ecosystem of extensions through PowerModuleSetup:

- **[power-modules/router](https://github.com/power-modules/router)**: HTTP routing with PSR-15 middleware support
- **power-modules/events**: Event-driven architecture (Coming soon!)
- **Custom extensions**: Authentication, logging, validation, and other cross-cutting concerns

Extensions work across ALL modules automatically while maintaining module isolation and testability.

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
