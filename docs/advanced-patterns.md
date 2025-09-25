# Advanced Patterns

This section covers advanced patterns and techniques for building sophisticated applications with the Modular Framework.

## Plugin Ecosystems

The Modular Framework enables building **entire ecosystems** of interoperable packages. Using PowerModuleSetup, you can create domain-specific plugin systems that third parties can extend.

### The Ecosystem Architecture

**Three-layer architecture for maximum extensibility:**

```
Layer 1: Core Framework
├── power-modules/framework    # The modular foundation
├── power-modules/plugin       # Generic plugin interfaces

Layer 2: Domain Plugin Systems  
├── power-cms/core            # CMS plugin system
├── power-gateway/core        # API Gateway plugin system  
├── power-etl/core            # ETL pipeline plugin system
└── power-cli/core            # CLI tools plugin system

Layer 3: Domain-Specific Plugins
├── power-cms/blog           # Blog functionality
├── power-cms/ecommerce      # E-commerce features
├── power-gateway/auth       # Authentication middleware
├── power-gateway/ratelimit  # Rate limiting
├── power-etl/csv            # CSV processing
└── power-cli/docker         # Docker management
```

### Generic Plugin Foundation

```php
// power-modules/plugin - Generic plugin interfaces
interface PluginInterface
{
    public function getName(): string;
    public function getVersion(): string;
    public function initialize(): void;
}

/**
 * @template T
 */
interface PluginRegistryInterface
{
    /** @param T $plugin */
    public function register(object $plugin): void;
    
    /** @return array<string, T> */
    public function getAll(): array;
    
    public function get(string $name): object;
}

// Universal plugin discovery via PowerModuleSetup
class PluginSetup implements PowerModuleSetup
{
    public function setup(PowerModuleSetupDto $dto): void
    {
        // Works with ANY plugin interface that extends PluginInterface
        if (!$dto->powerModule instanceof PluginInterface) {
            return;
        }

        // Auto-register with domain-specific registry
        $registry = $dto->rootContainer->get(PluginRegistryInterface::class);
        $registry->register($dto->powerModule);
        
        // Initialize the plugin
        $dto->powerModule->initialize();
    }
}
```

### Domain-Specific Ecosystems

Each ecosystem extends the base pattern for their specific needs:

#### **CMS Ecosystem**
```php
// power-cms/core - CMS-specific plugin system
interface CmsPluginInterface extends PluginInterface
{
    public function getContentTypes(): array;
    public function getAdminRoutes(): array;
}

class CmsPluginRegistry implements PluginRegistryInterface
{
    private array $plugins = [];
    private array $contentTypes = [];

    public function register(object $plugin): void
    {
        if (!$plugin instanceof CmsPluginInterface) {
            throw new InvalidArgumentException('Must implement CmsPluginInterface');
        }
        
        $this->plugins[$plugin->getName()] = $plugin;
        
        // Register CMS-specific features
        foreach ($plugin->getContentTypes() as $contentType) {
            $this->contentTypes[$contentType->getTypeName()] = $contentType;
        }
    }
    
    public function getContentTypes(): array
    {
        return $this->contentTypes;
    }
}

// Third-party CMS plugins
class BlogPlugin implements PowerModule, CmsPluginInterface
{
    public function getName(): string { return 'Blog Plugin'; }
    public function getVersion(): string { return '1.0.0'; }
    
    public function getContentTypes(): array
    {
        return [new BlogContentType()];
    }
    
    public function initialize(): void
    {
        // Plugin initialization
    }
}
```

#### **API Gateway Ecosystem**
```php
// power-gateway/core - Gateway-specific plugin system  
interface GatewayPluginInterface extends PluginInterface
{
    public function getMiddleware(): array;
    public function getRoutes(): array;
}

class GatewayPluginRegistry implements PluginRegistryInterface
{
    private array $plugins = [];
    private array $middleware = [];

    public function register(object $plugin): void
    {
        if (!$plugin instanceof GatewayPluginInterface) {
            throw new InvalidArgumentException('Must implement GatewayPluginInterface');
        }
        
        $this->plugins[$plugin->getName()] = $plugin;
        
        // Register gateway-specific features
        foreach ($plugin->getMiddleware() as $middleware) {
            $this->middleware[] = $middleware;
        }
    }
    
    public function getAllMiddleware(): array
    {
        return $this->middleware;
    }
}

// Third-party gateway plugins
class AuthPlugin implements PowerModule, GatewayPluginInterface
{
    public function getName(): string { return 'JWT Auth Plugin'; }
    public function getVersion(): string { return '2.1.0'; }
    
    public function getMiddleware(): array
    {
        return [new JwtAuthMiddleware()];
    }
    
    public function initialize(): void
    {
        // Auth plugin initialization
    }
}
```

#### **ETL Processing Ecosystem**
```php
// power-etl/core - ETL-specific plugin system
interface EtlPluginInterface extends PluginInterface
{
    public function getProcessors(): array;
    public function getConnectors(): array;
}

class EtlPluginRegistry implements PluginRegistryInterface
{
    private array $plugins = [];
    private array $processors = [];

    public function register(object $plugin): void
    {
        if (!$plugin instanceof EtlPluginInterface) {
            throw new InvalidArgumentException('Must implement EtlPluginInterface');
        }
        
        $this->plugins[$plugin->getName()] = $plugin;
        
        // Register ETL-specific components
        foreach ($plugin->getProcessors() as $processor) {
            $this->processors[$processor->getName()] = $processor;
        }
    }
    
    public function getProcessors(): array
    {
        return $this->processors;
    }
}

// Third-party ETL plugins
class CsvPlugin implements PowerModule, EtlPluginInterface
{
    public function getName(): string { return 'CSV Processor'; }
    public function getVersion(): string { return '1.5.2'; }
    
    public function getProcessors(): array
    {
        return [new CsvReader(), new CsvWriter()];
    }
    
    public function initialize(): void
    {
        // CSV plugin initialization
    }
}
```

### Universal Application Pattern

**Every ecosystem uses the same application pattern:**

```php
// CMS Application
$cmsApp = new ModularAppBuilder(__DIR__)
    ->addPowerModuleSetup(new PluginSetup())  // Universal plugin discovery
    ->withModules(
        CmsCoreModule::class,        // Domain core
        BlogPlugin::class,           // Third-party plugins
        EcommercePlugin::class,
        GalleryPlugin::class,
    )
    ->build();

// API Gateway Application
$gatewayApp = new ModularAppBuilder(__DIR__)
    ->addPowerModuleSetup(new PluginSetup())  // Same setup!
    ->withModules(
        GatewayCoreModule::class,    // Domain core
        AuthPlugin::class,           // Third-party plugins
        RateLimitPlugin::class,
        LoggingPlugin::class,
    )
    ->build();

// ETL Application  
$etlApp = new ModularAppBuilder(__DIR__)
    ->addPowerModuleSetup(new PluginSetup())  // Same setup!
    ->withModules(
        EtlCoreModule::class,        // Domain core
        CsvPlugin::class,            // Third-party plugins
        DatabasePlugin::class,
        TransformPlugin::class,
    )
    ->build();
```

### Ecosystem Benefits

**For Framework Users:**
- **Consistent patterns** across all domains
- **Universal plugin discovery** via PowerModuleSetup
- **Type-safe extensibility** through domain interfaces
- **Zero-config plugin loading** - just add modules

**For Ecosystem Builders:**
- **Proven architecture** to build upon
- **PowerModuleSetup integration** for automatic discovery  
- **Domain-specific customization** while maintaining compatibility
- **Interoperable ecosystems** all built on the same foundation

**For Plugin Developers:**
- **Familiar patterns** across different ecosystems
- **Automatic registration** via the universal setup
- **Clear contracts** through domain-specific interfaces
- **Isolated development** - plugins don't interfere with each other

This architecture enables building **frameworks for frameworks** - where the Modular Framework provides the foundation for entire ecosystems of interoperable packages!

## Multi-Environment Configuration

Organize modules by environment and feature flags:

```php
class DatabaseModule implements PowerModule, ExportsComponents, HasConfig
{
    use HasConfigTrait;

    public function __construct()
    {
        $this->powerModuleConfig = Config::create();
    }
    
    public static function exports(): array
    {
        return [ConnectionInterface::class];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        if ($this->getConfigValue(Setting::DatabaseType) === 'sqlite') {
            $container->set(ConnectionInterface::class, SqliteConnection::class);
        } else {
            $container->set(ConnectionInterface::class, MysqlConnection::class);
        }
    }
}
```

## Module Composition Patterns

### Facade Modules
Create convenience modules that group related functionality:

```php
class EcommerceModule implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [
            ImportItem::create(ProductsModule::class, ProductService::class),
            ImportItem::create(OrdersModule::class, OrderService::class),
            ImportItem::create(PaymentModule::class, PaymentProcessor::class),
        ];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(EcommerceFacade::class, EcommerceFacade::class)
            ->addArguments([
                ProductService::class,
                OrderService::class, 
                PaymentProcessor::class
            ]);
    }
}
```

### Abstract Module Hierarchies

```php
abstract class AbstractDatabaseModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [RepositoryInterface::class];
    }
    
    abstract protected function getConnectionClass(): string;
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(RepositoryInterface::class, $this->getConnectionClass());
    }
}

class PostgresModule extends AbstractDatabaseModule
{
    protected function getConnectionClass(): string
    {
        return PostgresRepository::class;
    }
}
```

## Testing Strategies

### Module Unit Testing
Test modules in complete isolation:

```php
class OrdersModuleTest extends TestCase
{
    public function testModuleRegistration(): void
    {
        $container = new ConfigurableContainer();
        $module = new OrdersModule();
        
        $module->register($container);
        
        $this->assertTrue($container->has(OrderService::class));
        $this->assertInstanceOf(OrderService::class, $container->get(OrderService::class));
    }
}
```

### Integration Testing with Imports
Test module interaction through the app:

```php
class OrdersIntegrationTest extends TestCase
{
    public function testOrderCreationWithAuth(): void
    {
        $app = new ModularAppBuilder(__DIR__)
            ->withConfig(Config::forAppRoot(__DIR__)->set(Setting::CachePath, sys_get_temp_dir()))
            ->withModules(
                AuthModule::class,
                OrdersModule::class,
            )
            ->build();
        
        $orderService = $app->get(OrderService::class);
        $this->assertInstanceOf(OrderService::class, $orderService);
        
        // Test the integrated behavior
        $order = $orderService->createOrder($userId, $items);
        $this->assertNotNull($order->getId());
    }
}
```

## Performance Optimization

### Lazy Loading
Use factories for expensive services:

```php
$container->set(ExpensiveServiceBuilder::class, ExpensiveServiceBuilder::class);
$container->set(
    ExpensiveService::class,
    fn (ExpensiveServiceBuilder $builder): ExpensiveService => $builder->withHeavyInitialization()->build(),
);
```

### Caching Module Dependencies
The framework automatically caches dependency resolution:

```php
$app = new ModularAppBuilder(__DIR__)
    ->withConfig(Config::forAppRoot(__DIR__)->set(Setting::CachePath, '/path/to/cache'))
    ->build();
```

### Conditional Module Loading
Load modules based on runtime conditions:

```php
$modules = [CoreModule::class];
$featureConfig = Feature\Config\Config::create();

if ($featureConfig->get(Setting::EnableAnalytics) === true) {
    $modules[] = AnalyticsModule::class;
}

if ($environment === 'development') {
    $modules[] = DebugModule::class;
}

$app = new ModularAppBuilder(__DIR__)
    ->withModules(...$modules)
    ->build();
```

## Best Practices

1. **Keep modules focused**: Each module should have a single responsibility
2. **Minimize exports**: Only export what other modules truly need
3. **Use interfaces**: Export interfaces, not concrete classes when possible
4. **Document dependencies**: Make import/export relationships clear
5. **Test boundaries**: Verify that internal services stay private
6. **Consider extraction**: Design modules that could become separate packages