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

This framework is not just another option; it introduces a new architectural paradigm to the PHP ecosystem. It is built on a foundation of **runtime-enforced encapsulation** and **true modularity**, inspired by the principles of mature systems like OSGi.

To understand the core innovations and how this framework differs from established solutions like Symfony and Laravel, please read our **[Architectural Vision Document](ARCHITECTURAL_VISION.md)**.

## Quick Start

```bash
composer require power-modules/framework
```

```php
use Modular\Framework\App\ModularAppBuilder;

$app = new ModularAppBuilder(__DIR__)
    ->withModules(
        \MyApp\Auth\AuthModule::class,
        \MyApp\Orders\OrdersModule::class,
    )
    ->build();

// Get any exported service
$orderService = $app->get(\MyApp\Orders\OrderService::class);
```

## ⚡ PowerModuleSetup Extension System

The framework's most powerful feature - **PowerModuleSetup** allows extending module functionality without breaking encapsulation:

```php
$app = new ModularAppBuilder(__DIR__)
    ->withModules(UserModule::class, OrderModule::class)
    ->addPowerModuleSetup(new RoutingSetup())    // Adds HTTP routing to modules implementing HasRoutes interface
    ->addPowerModuleSetup(new EventBusSetup())   // Pulls module events and handlers into a central event bus
    ->build();
```

**Available extensions:**
- [**power-modules/router**](https://github.com/power-modules/router) - HTTP routing with PSR-15 middleware
- [**power-modules/plugin**](https://github.com/power-modules/plugin) - Plugin architecture for third-party modules
- [**power-modules/dependency-graph**](https://github.com/power-modules/dependency-graph) - Visualize module dependencies
- [**power-modules/dependency-graph-mermaid**](https://github.com/power-modules/dependency-graph-mermaid) - Mermaid plugin for dependency graph rendering

Coming soon:
- **power-modules/events** - Event-driven architecture
- **power-modules/cli** - Build CLI applications with modular commands
- **Your own!** - Create custom PowerModuleSetup implementations for your needs

## 🚀 Microservice Evolution Path

Start with a modular monolith, evolve to microservices naturally:

*Today: Modular monolith*
```php
class UserModule implements PowerModule, ExportsComponents {
    public static function exports(): array {
        return [
            UserService::class,
        ];
    }
}

class OrderModule implements PowerModule, ImportsComponents {
    public static function imports(): array {
        return [
            ImportItem::create(UserModule::class, UserService::class),
        ];
    }
}
```

*Later: Independent microservices*
```php
class UserModule implements PowerModule, HasRoutes {
    public function getRoutes(): array
    {
        return [
            Route::get('/', UserController::class, 'list'),
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(UserController::class, UserController::class)
            ->addArguments([UserService::class]);
    }
}

class OrderModule implements PowerModule, ImportsComponents {
    // Uses User HTTP API instead of direct service import
}
```

Your modules are designed with clear boundaries. When you're ready to scale, the module structure supports splitting them into separate services.

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