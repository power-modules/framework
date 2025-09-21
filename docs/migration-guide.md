# Migration Guide

This guide helps you migrate existing applications to use the Modular Framework.

## Migrating from Traditional DI Containers

### Before: Single Container
```php
// Traditional approach
$container = new Container();
$container->set(UserRepository::class, UserRepository::class);
$container->set(OrderRepository::class, OrderRepository::class);
$container->set(UserService::class, UserService::class)
    ->addArgument(UserRepository::class);
$container->set(OrderService::class, OrderService::class)
    ->addArguments([OrderRepository::class, UserService::class]);
```

### After: Modular Containers
```php
// Users module
class UsersModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [UserService::class];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(UserRepository::class, UserRepository::class);
        $container->set(UserService::class, UserService::class)
            ->addArguments([UserRepository::class]);
    }
}

// Orders module
class OrdersModule implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(UsersModule::class, UserService::class)];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(OrderRepository::class, OrderRepository::class);
        $container->set(OrderService::class, OrderService::class)
            ->addArguments([OrderRepository::class, UserService::class]);
    }
}
```

## Migrating from Framework-Specific Patterns

### Symfony Service Configuration
Replace Symfony's service.yaml with modules:

```yaml
# Before: services.yaml
services:
  App\Service\UserService:
    arguments: ['@App\Repository\UserRepository']
    
  App\Service\OrderService:
    arguments: 
      - '@App\Repository\OrderRepository'
      - '@App\Service\UserService'
```

```php
// After: Module classes
class AppModule implements PowerModule
{
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(UserRepository::class, UserRepository::class);
        $container->set(UserService::class, UserService::class)
            ->addArguments([UserRepository::class]);
            
        $container->set(OrderRepository::class, OrderRepository::class);
        $container->set(OrderService::class, OrderService::class)
            ->addArguments([OrderRepository::class, UserService::class]);
    }
}
```

### Laravel Service Providers
Convert Laravel service providers to modules:

```php
// Before: Laravel Service Provider
class UserServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserService::class, function ($app) {
            return new UserService($app->make(UserRepositoryInterface::class));
        });
    }
}
```

```php
// After: Modular Framework Module
class UserModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [UserService::class];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(UserRepositoryInterface::class, UserRepository::class);
        $container->set(UserService::class, UserService::class)
            ->addArguments([UserRepositoryInterface::class]);
    }
}
```

## Migration Strategy

### 1. Identify Boundaries
Look for natural boundaries in your application:
- Domain boundaries (Users, Orders, Products)
- Layer boundaries (Infrastructure, Application, Domain)
- Feature boundaries (Authentication, Reporting, API)

### 2. Start Small
Begin with a single, self-contained module:

```php
// Start with something isolated
class LoggingModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [LoggerInterface::class];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(LoggerInterface::class, FileLogger::class);
    }
}
```

### 3. Extract Dependencies Gradually
Move from tightly coupled to explicitly imported:

```php
// Phase 1: Extract but keep everything public
class UserModule implements PowerModule
{
    public function register(ConfigurableContainerInterface $container): void
    {
        // All services in module container
        $container->set(UserRepository::class, UserRepository::class);
        $container->set(UserService::class, UserService::class);
        $container->set(UserValidator::class, UserValidator::class);
    }
}

// Phase 2: Make exports explicit
class UserModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [UserService::class]; // Only public API
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(UserRepository::class, UserRepository::class);
        $container->set(UserValidator::class, UserValidator::class);
        $container->set(UserService::class, UserService::class)
            ->addArguments([UserRepository::class, UserValidator::class]);
    }
}
```

### 4. Convert Consumers to Import
Update dependent code to use imports:

```php
// Before: Direct container access
$userService = $container->get(UserService::class);

// After: Explicit import
class OrderModule implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(UserModule::class, UserService::class)];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(OrderService::class, OrderService::class)
            ->addArguments([UserService::class]); // Available via import
    }
}
```

## Common Pitfalls

### 1. Over-Modularization
Don't create modules for every class:

```php
// ❌ Too granular
class UserRepositoryModule implements PowerModule { /* ... */ }
class UserServiceModule implements PowerModule { /* ... */ }
class UserValidatorModule implements PowerModule { /* ... */ }

// ✅ Cohesive module
class UserModule implements PowerModule 
{
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(UserRepository::class, UserRepository::class);
        $container->set(UserValidator::class, UserValidator::class);
        $container->set(UserService::class, UserService::class)
            ->addArguments([UserRepository::class, UserValidator::class]);
    }
}
```

### 2. Leaky Abstractions
Don't export internal implementation details:

```php
// ❌ Exposing internals
class UserModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [
            UserService::class,
            UserRepository::class,  // Internal detail
            UserValidator::class,   // Internal detail
        ];
    }
}

// ✅ Clean interface
class UserModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [UserService::class]; // Public API only
    }
}
```

### 3. Circular Dependencies
Avoid modules that depend on each other:

```php
// ❌ Circular dependency
class UserModule implements ImportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(OrderModule::class, OrderService::class)];
    }
}

class OrderModule implements ImportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(UserModule::class, UserService::class)];
    }
}

// ✅ Introduce shared module
class SharedModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [SharedService::class];
    }
}

class UserModule implements ImportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(SharedModule::class, SharedService::class)];
    }
}

class OrderModule implements ImportsComponents
{
    public static function imports(): array
    {
        return [
            ImportItem::create(SharedModule::class, SharedService::class),
            ImportItem::create(UserModule::class, UserService::class),
        ];
    }
}
```

## Testing Your Migration

### 1. Verify Encapsulation
Ensure internal services are not accessible:

```php
public function testPrivateServicesAreNotAccessible(): void
{
    $app = $this->createApp([UserModule::class]);
    
    $this->assertTrue($app->has(UserService::class)); // Exported
    $this->assertFalse($app->has(UserRepository::class)); // Internal
}
```

### 2. Test Module Independence
Verify modules work in isolation:

```php
public function testUserModuleStandsAlone(): void
{
    $app = $this->createApp([UserModule::class]); // Only this module
    
    $userService = $app->get(UserService::class);
    $this->assertInstanceOf(UserService::class, $userService);
}
```

### 3. Validate Import Resolution
Check that imports work correctly:

```php
public function testOrderModuleImportsUser(): void
{
    $app = $this->createApp([UserModule::class, OrderModule::class]);
    
    $orderService = $app->get(OrderService::class);
    $this->assertInstanceOf(OrderService::class, $orderService);
    
    // Verify the imported dependency was injected
    $reflection = new ReflectionClass($orderService);
    $userService = $reflection->getProperty('userService')->getValue($orderService);
    $this->assertInstanceOf(UserService::class, $userService);
}
```