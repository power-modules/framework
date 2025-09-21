# Advanced Patterns

This section covers advanced patterns and techniques for building sophisticated applications with the Modular Framework.

## Plugin Systems

Create extensible applications where third-party code can add functionality through modules:

```php
// Core app module
class CoreAppModule implements PowerModule, ExportsComponents 
{
    public static function exports(): array
    {
        return [PluginRegistry::class, EventBus::class];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(PluginRegistry::class, PluginRegistry::class);
        $container->set(EventBus::class, EventBus::class);
    }
}

// Third-party plugin module
class ThirdPartyPlugin implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [
            ImportItem::create(CoreAppModule::class, PluginRegistry::class, EventBus::class),
        ];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(MyPluginService::class, MyPluginService::class)
            ->addArguments([EventBus::class])
            ->addMethod('registerWith', [PluginRegistry::class]);
    }
}
```

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
            ->build();
            
        $app->registerModules([
            AuthModule::class,
            OrdersModule::class,
        ]);
        
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

$app->registerModules($modules);
```

## Best Practices

1. **Keep modules focused**: Each module should have a single responsibility
2. **Minimize exports**: Only export what other modules truly need
3. **Use interfaces**: Export interfaces, not concrete classes when possible
4. **Document dependencies**: Make import/export relationships clear
5. **Test boundaries**: Verify that internal services stay private
6. **Consider extraction**: Design modules that could become separate packages