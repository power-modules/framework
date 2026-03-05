# Modular Framework

[![CI](https://github.com/power-modules/framework/actions/workflows/php.yml/badge.svg)](https://github.com/power-modules/framework/actions/workflows/php.yml)
[![Packagist Version](https://img.shields.io/packagist/v/power-modules/framework)](https://packagist.org/packages/power-modules/framework)
[![PHP Version](https://img.shields.io/packagist/php-v/power-modules/framework)](https://packagist.org/packages/power-modules/framework)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-blue)](#)

A **general-purpose modular architecture framework** for PHP. Build applications where each module has its own dependency injection container, with carefully controlled sharing through explicit import/export contracts.

> **💡 Versatile:** Works well for CLI tools, data pipelines, background processors, APIs, and complex PHP applications that benefit from clear module boundaries.

## ✨ Why Modular Framework?

- **🔒 True Encapsulation**: Each module has its own isolated DI container
- **⚡ PowerModuleSetup**: Extend module functionality without breaking encapsulation
- **🚀 Microservice Ready**: Isolated modules can easily become independent services
- **📋 Explicit Dependencies**: Import/export contracts make relationships visible  
- **🧪 Better Testing**: Test modules in isolation with their own containers
- **👥 Team Scalability**: Different teams can own different modules
- **🔌 Plugin-Ready**: Third-party modules extend functionality safely

## 🚀 Architectural Vision

This framework is more than just another library — it introduces a new architectural paradigm to the PHP ecosystem, built on **runtime-enforced encapsulation** and **true modularity**, inspired by mature systems like OSGi.

To understand the core innovations and how this framework differs from established solutions like Symfony and Laravel, please read our **[Architectural Vision Document](ARCHITECTURAL_VISION.md)**.

## Quick Start

```bash
composer require power-modules/framework
```

```php
use Modular\Framework\App\ModularAppBuilder;

class OrdersModule implements PowerModule, ExportsComponents {
    public static function exports(): array {
        return [
            OrderService::class,
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(OrderRepository::class, OrderRepository::class)
            ->addArguments([DatabaseConnection::class]);
        $container->set(OrderService::class, OrderService::class)
            ->addArguments([OrderRepository::class]);
    }
}

$app = new ModularAppBuilder(__DIR__)
    ->withModules(
        \MyApp\Auth\AuthModule::class,
        \MyApp\Orders\OrdersModule::class,
    )
    ->build();

// Get any exported service
$orderService = $app->get(\MyApp\Orders\OrderService::class);
// Fully initialized, with all dependencies resolved within the module's own container
```

## ⚡ PowerModuleSetup Extension System

The framework's most powerful feature - **PowerModuleSetup** allows extending module functionality without breaking encapsulation:

```php
$app = new ModularAppBuilder(__DIR__)
    ->withModules(UserModule::class, OrderModule::class)
    ->withPowerSetup(
        new RoutingSetup(),  // Adds HTTP routing to modules implementing HasRoutes interface
        new EventBusSetup(), // Pulls module events and handlers into a central event bus
    )
    ->build();
```

**Available extensions:**
- [**power-modules/router**](https://github.com/power-modules/router) - HTTP routing with PSR-15 middleware
- [**power-modules/plugin**](https://github.com/power-modules/plugin) - Plugin architecture for third-party modules
- [**power-modules/dependency-graph**](https://github.com/power-modules/dependency-graph) - Visualize module dependencies
- [**power-modules/dependency-graph-mermaid**](https://github.com/power-modules/dependency-graph-mermaid) - Mermaid plugin for dependency graph rendering
- [**power-modules/console**](https://github.com/power-modules/console) - PowerModuleSetup extension that auto-discovers and registers Symfony Console commands from Power Modules

Coming soon:
- **power-modules/events** - Event-driven architecture
- **Your own!** - Create custom PowerModuleSetup implementations for your needs

## 🚀 Microservice Evolution Path

Start with a modular monolith, evolve to microservices naturally:

*Today: Modular monolith*
```php
class UserModule implements PowerModule, ExportsComponents {
    public static function exports(): array {
        return [
            // Expose the service directly for in-process use
            UserRepositoryInterface::class,
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(UserRepositoryInterface::class, UserService::class);
    }
}

class OrderModule implements PowerModule, ImportsComponents {
    public static function imports(): array {
        return [
            // Import the interface from UserModule for in-process communication
            ImportItem::create(UserModule::class, UserRepositoryInterface::class),
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(OrderService::class, OrderService::class)
            ->addArguments([UserRepositoryInterface::class]);
    }
}
```

*Later: Independent microservices*
```php
class UserModule implements PowerModule, ExportsComponents, HasRoutes {
    public static function exports(): array {
        return [
            // Still export the same interface — now resolved to an HTTP client rather than an in-process service
            UserRepositoryInterface::class,
        ];
    }

    public function getRoutes(): array
    {
        return [
            // Define HTTP routes for the User API
            Route::get('/', UserController::class, 'list'),
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Implementation details remain private; OrderModule doesn't need to know anything changed
        $container->set(UserApiService::class, UserApiService::class)
            ->addArguments([Psr\Http\ClientInterface::class]);
        $container->set(UserRepositoryInterface::class, UserApiClient::class);

        $container->set(UserService::class, UserService::class);
        $container->set(UserController::class, UserController::class)
            ->addArguments([UserService::class]);
    }
}

class OrderModule implements PowerModule, ImportsComponents {
    public static function imports(): array {
        return [
            // The same import as before — now backed by an HTTP client instead of an in-process service.
            // You can also drop this import and implement your own HTTP client directly in OrderModule.
            ImportItem::create(UserModule::class, UserRepositoryInterface::class),
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(OrderService::class, OrderService::class)
            ->addArguments([UserRepositoryInterface::class]);
    }
}
```

Because modules are designed with clear boundaries from the start, splitting them into independent services is a natural next step when you're ready to scale.

## 📚 Documentation

**📖 [Complete Documentation Hub](docs/README.md)** - Comprehensive guides, examples, and API reference

**Quick Links:**
- [Getting Started](docs/getting-started.md) - Build your first module in 5 minutes
- [Use Cases](docs/use-cases/README.md) - Real-world examples (web APIs, ETL, etc.)
- [Architecture](docs/architecture.md) - Deep dive into framework internals

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for development setup and guidelines.

## License

MIT License. See [LICENSE](LICENSE) for details.