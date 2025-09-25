# API Reference

Complete reference for all Modular Framework interfaces and classes.

## Additional Guides

- **[Advanced Patterns](advanced-patterns.md)** - Plugin systems, composition patterns, and performance optimization
- **[Migration Guide](migration-guide.md)** - Step-by-step guide for migrating existing applications

## Core Interfaces

### PowerModule

The fundamental interface that all modules must implement.

```php
interface PowerModule
{
    /**
     * Register services in the module's container.
     */
    public function register(ConfigurableContainerInterface $container): void;
}
```

### ExportsComponents

Modules implementing this interface declare which services they make available to other modules.

```php
interface ExportsComponents
{
    /**
     * Returns array of service class names that this module exports.
     * 
     * @return array<class-string>
     */
    public static function exports(): array;
}
```

### ImportsComponents

Modules implementing this interface declare their dependencies on other modules.

```php
interface ImportsComponents
{
    /**
     * Returns array of ImportItem instances declaring dependencies.
     * 
     * @return array<ImportItem>
     */
    public static function imports(): array;
}
```

### HasConfig

Modules implementing this interface can receive configuration from external files.

```php
interface HasConfig
{
    public function getConfig(): PowerModuleConfig;
    public function setConfig(PowerModuleConfig $powerModuleConfig): void;
}
```

## Core Classes

### App

The main application container implementing PSR-11.

```php
class App implements ContainerInterface
{
    /**
     * Get a service from the root container.
     * 
     * @template T
     * @param class-string<T> $id
     * @return T
     */
    public function get(string $id);

    /**
     * Check if a service is available in the root container.
     */
    public function has(string $id): bool;

    /**
     * Register modules with the application.
     * Note: Prefer using ModularAppBuilder::withModules() for better fluent API.
     * 
     * @param array<class-string<PowerModule>> $powerModuleClassNames
     */
    public function registerModules(array $powerModuleClassNames): self;

    /**
     * Add setup extensions that run during module loading.
     * Note: Prefer using ModularAppBuilder::withPowerSetup() for better fluent API.
     */
    public function addPowerModuleSetup(PowerModuleSetup $powerModuleSetup): self;
}
```

### ModularAppBuilder

Builder for creating and configuring App instances.

```php
class ModularAppBuilder
{
    public function __construct(private readonly string $appRoot) {}

    /**
     * Override the default configuration.
     */
    public function withConfig(Config $config): self;

    /**
     * Override the default root container.
     */
    public function withRootContainer(ConfigurableContainerInterface $container): self;

    /**
     * Override the default module dependency sorter.
     */
    public function withModuleDependencySorter(ModuleDependencySorter $sorter): self;

    /**
     * Override the default cache implementation.
     */
    public function withCache(CacheInterface $cache): self;

    /**
     * Override the default module resolver.
     */
    public function withModuleResolver(CanCreatePowerModuleInstance $moduleResolver): self;

    /**
     * Add custom power module setup handlers.
     * 
     * @param PowerModuleSetup ...$setups
     */
    public function withPowerSetup(PowerModuleSetup ...$setups): self;

    /**
     * Register modules to be loaded when the app is built.
     * 
     * @param class-string<PowerModule> ...$modules
     */
    public function withModules(string ...$modules): self;

    /**
     * Build the application instance.
     */
    public function build(): App;
}
```

### ConfigurableContainer

The dependency injection container used by modules.

```php
class ConfigurableContainer implements ConfigurableContainerInterface, ContainerInterface
{
    /**
     * Register a service definition.
     * 
     * @param class-string $id
     * @param mixed $concrete
     * @param class-string<InstanceResolver>|null $resolver
     */
    public function set(string $id, mixed $concrete, ?string $resolver = null): ServiceDefinition;

    /**
     * Add a service definition from another container.
     */
    public function addServiceDefinition(string $id, ServiceDefinition $serviceDefinition): void;

    /**
     * Get a service instance.
     * 
     * @template T
     * @param class-string<T> $id
     * @return T
     */
    public function get(string $id);

    /**
     * Check if a service is registered.
     */
    public function has(string $id): bool;
}
```

### ServiceDefinition

Defines how a service should be instantiated and configured.

```php
class ServiceDefinition
{
    /**
     * Add constructor arguments.
     * 
     * @param array<mixed> $arguments
     */
    public function addArguments(array $arguments): self;

    /**
     * Add a method call after instantiation.
     * 
     * @param array<mixed> $arguments
     */
    public function addMethod(string $method, array $arguments = []): self;
}
```

### ImportItem

Declares a dependency on services from another module.

```php
class ImportItem
{
    /**
     * Create an import declaration.
     * 
     * @param class-string<PowerModule> $moduleName
     * @param string ...$itemsToImport
     */
    public static function create(string $moduleName, string ...$itemsToImport): self;
}
```

## Setup System

### PowerModuleSetup

Interface for setup extensions that run during module loading.

```php
interface PowerModuleSetup
{
    public function setup(PowerModuleSetupDto $powerModuleSetupDto): void;
}
```

### PowerModuleSetupDto

Data passed to setup extensions.

```php
final readonly class PowerModuleSetupDto
{
    public function __construct(
        public SetupPhase $setupPhase,
        public PowerModule $powerModule,
        public ConfigurableContainerInterface $rootContainer,
        public ConfigurableContainerInterface $moduleContainer,
        public Config $modularAppConfig,
    ) {}
}
```

### SetupPhase

Enum defining when setup extensions run.

```php
enum SetupPhase
{
    case Pre;  // Before imports are resolved
    case Post; // After imports are resolved
}
```

## Built-in Setup Extensions

### ExportsComponentsSetup

Automatically registers exported services in the root container.

### ImportsComponentsSetup

Automatically makes imported services available in module containers.

### HasConfigSetup

Loads configuration files for modules implementing `HasConfig`.

### ModularAppConfigInjector

Injects the application configuration into all module containers.

## Configuration System

### Config

Application-level configuration.

```php
class Config
{
    public static function forAppRoot(string $appRoot): self;
    
    public function set(Setting $setting, mixed $value): self;
    
    public function get(Setting $setting): mixed;
}
```

### Setting

Enum of built-in configuration options.

```php
enum Setting
{
    case AppRoot;   // Application root directory
    case CachePath; // Cache directory path
}
```

### PowerModuleConfig

Base class for module-specific configuration.

```php
abstract class PowerModuleConfig
{
    abstract public function getConfigFilename(): string;
    
    public function set(BackedEnum|UnitEnum $setting, mixed $value): self;
    
    public function get(BackedEnum|UnitEnum $setting): mixed;
    
    public function has(BackedEnum|UnitEnum $setting): bool;
    
    /**
     * @return iterable<BackedEnum|UnitEnum,mixed>
     */
    public function getAll(): iterable;
}
```

## Caching

### FilesystemCache

PSR-16 compatible cache implementation.

```php
class FilesystemCache implements CacheInterface
{
    public function __construct(private readonly string $cacheDir) {}
    
    // PSR-16 methods: get, set, delete, clear, has, getMultiple, setMultiple, deleteMultiple
}
```

## Exception Hierarchy

### ContainerException

Thrown when container operations fail.

### CircularDependencyException

Thrown when circular dependencies are detected between modules.

### PowerModuleException

Base exception for module-related errors.

## Instance Resolvers

### RawValueInstanceResolver

Returns the configured value as-is (for scalar values, arrays, etc.).

### InstanceViaContainerResolver

Resolves the service through a different container (used for exports).

### Custom Resolvers

Implement `InstanceResolver` interface:

```php
interface InstanceResolver
{
    public function resolve(string $id, mixed $concrete): mixed;
}
```

## Advanced Interfaces

### ModuleDependencySorter

Interface for custom dependency sorting algorithms.

```php
interface ModuleDependencySorter
{
    /**
     * @param array<class-string<PowerModule>> $powerModuleClassNames
     * @return array<class-string<PowerModule>>
     * @throws CircularDependencyException
     */
    public function sort(array $powerModuleClassNames): array;
}
```

### CanCreatePowerModuleInstance

Interface for custom module instantiation.

```php
interface CanCreatePowerModuleInstance
{
    /**
     * @param class-string<PowerModule> $id
     */
    public function create(string $id): PowerModule;
}
```