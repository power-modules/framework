# Use Cases & Examples

The Modular Framework's flexible architecture supports a wide range of backend systems. Here are real-world examples across different domains.

## 📂 Examples by Domain

### **Complete Examples**
- [Web API with Authentication](web-api.md) - REST API with JWT auth, router integration, PSR-15 middleware
- [ETL Pipeline](etl-pipeline.md) - Extract, Transform, Load with clear modular boundaries

### **Additional Use Case Ideas**

**Web Applications**: Multi-tenant systems with tenant isolation • API gateways with service routing • Real-time dashboards with WebSocket modules

**Data Processing**: Analytics engines with pluggable analyzers • Report generators with dynamic templates • Data synchronization between systems

**CLI Applications**: Database migration tools • File batch processors • Multi-environment deployment utilities

**Background Processing**: Message queue consumers • Event stream processors • Scheduled job managers

**System Integration**: Service monitoring and alerting • Data pipeline orchestration • Legacy system adapters

## 🎯 Common Patterns

### **Layered Architecture**
```php
// Data Layer
class DatabaseModule implements PowerModule, ExportsComponents
{
    public static function exports(): array {
        return [UserRepository::class, OrderRepository::class];
    }
}

// Business Layer
class BusinessModule implements PowerModule, ImportsComponents, ExportsComponents
{
    public static function imports(): array {
        return [ImportItem::create(DatabaseModule::class, UserRepository::class)];
    }
    
    public static function exports(): array {
        return [UserService::class, OrderService::class];
    }
}

// Presentation Layer
class ApiModule implements PowerModule, ImportsComponents
{
    public static function imports(): array {
        return [ImportItem::create(BusinessModule::class, UserService::class)];
    }
}
```

### **Plugin Architecture**
```php
// Core system
class CoreModule implements PowerModule, ExportsComponents
{
    public static function exports(): array {
        return [PluginManager::class, EventBus::class];
    }
}

// Pluggable features
class EmailPluginModule implements PowerModule, ImportsComponents
{
    public static function imports(): array {
        return [ImportItem::create(CoreModule::class, PluginManager::class)];
    }
}

class NotificationPluginModule implements PowerModule, ImportsComponents
{
    public static function imports(): array {
        return [ImportItem::create(CoreModule::class, EventBus::class)];
    }
}
```

### **Microservice Boundaries**
```php
// User Domain. Could become User HTTP API (by implementing HasRoutes interface)
class UserModule implements PowerModule, ExportsComponents
{
    public static function exports(): array {
        return [UserService::class];
    }
}

// Order Domain. Could become Order HTTP API, calling User HTTP API
class OrderModule implements PowerModule, ImportsComponents
{
    public static function imports(): array {
        return [ImportItem::create(UserModule::class, UserService::class)];
    }
}
```

## 🔧 Development Patterns

### **Feature Flags**
```php
class FeatureModule implements PowerModule, HasConfig
{
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(FeatureToggle::class, FeatureToggle::class)
            ->addArguments([
                $this->getConfigValue(Setting::EnabledFeatures)
            ]);
    }
}
```

### **Environment-Specific Modules**
```php
// Load different modules based on environment
$modules = [CoreModule::class];

if ($env === 'development') {
    $modules[] = DebugModule::class;
    $modules[] = MockExternalServicesModule::class;
} else {
    $modules[] = ProductionLoggingModule::class;
    $modules[] = RealExternalServicesModule::class;
}

$app = new ModularAppBuilder(__DIR__)
    ->withModules(...$modules)
    ->build();
```

### **Testing Strategies**
```php
// Unit tests - test modules in isolation
class UserModuleTest extends TestCase 
{
    public function testUserCreation()
    {
        $app = new ModularAppBuilder(__DIR__)
            ->withModules(
                UserModule::class,
                MockDatabaseModule::class, // Mock dependencies
            )
            ->build();
        
        $userService = $app->get(UserService::class);
        // Test business logic
    }
}

// Integration tests - test module interactions
class UserOrderIntegrationTest extends TestCase
{
    public function testUserCanPlaceOrder()
    {
        $app = new ModularAppBuilder(__DIR__)
            ->withModules(
                UserModule::class,
                OrderModule::class,
                DatabaseModule::class, // Real database for integration
            )
            ->build();
        
        // Test cross-module interactions
    }
}
```

## 📚 Next Steps

Choose an example that matches your use case:
- **Building APIs?** Start with [Web API example](web-api.md)
- **Processing data?** Check out [ETL Pipeline](etl-pipeline.md)  
- **Need more patterns?** Explore [Advanced Patterns](advanced-patterns.md)