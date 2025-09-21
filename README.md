# Modular Framework

[![CI](https://github.com/power-modules/framework/actions/workflows/php.yml/badge.svg)](https://github.com/power-modules/framework/actions/workflows/php.yml)
[![Packagist Version](https://img.shields.io/packagist/v/power-modules/framework)](https://packagist.org/packages/power-modules/framework)
[![PHP Version](https://img.shields.io/packagist/php-v/power-modules/framework)](https://packagist.org/packages/power-modules/framework)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-blue)](#)

A **general-purpose modular architecture framework** for PHP. Build applications where each module has its own dependency injection container, with carefully controlled sharing through explicit import/export contracts.

> **💡 Not just for web!** Perfect for CLI tools, data pipelines, background processors, APIs, and any complex PHP system that benefits from clear module boundaries.

## ✨ Why Modular Framework?

- **🔒 True Encapsulation**: Each module has its own isolated DI container
- **📋 Explicit Dependencies**: Import/export contracts make relationships visible  
- **🧪 Better Testing**: Test modules in isolation with their own containers
- **👥 Team Scalability**: Different teams can own different modules
- **🔌 Plugin-Ready**: Third-party modules extend functionality safely

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

## 📚 Documentation

| Guide | Description |
|-------|-------------|
| **[Getting Started](docs/getting-started.md)** | Build your first module in 5 minutes |
| **[Architecture](docs/architecture.md)** | Deep dive into module system, containers, and lifecycle |
| **[Use Cases](docs/use-cases/README.md)** | Examples for web apps, CLI tools, ETL pipelines, and more |
| **[API Reference](docs/api-reference.md)** | Complete interface and class documentation |
| **[Advanced Patterns](docs/advanced-patterns.md)** | Plugin systems, composition patterns, performance tips |
| **[Migration Guide](docs/migration-guide.md)** | Convert existing applications to use the framework |

## Real-World Examples

### Simple Module
```php
class OrdersModule implements PowerModule
{
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(OrderService::class, OrderService::class)
            ->addArguments([OrderRepository::class]);
    }
}
```

### Module with Exports
```php
class AuthModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [UserService::class];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(UserService::class, UserService::class);
        // Internal services stay private by default
        $container->set(PasswordHasher::class, PasswordHasher::class);
    }
}
```

### Module with Imports
```php
class OrdersModule implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [ImportItem::create(AuthModule::class, UserService::class)];
    }
    
    public function register(ConfigurableContainerInterface $container): void
    {
        // UserService is now available for injection
        $container->set(OrderService::class, OrderService::class)
            ->addArguments([UserService::class]);
    }
}
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for development setup and guidelines.

## License

MIT License. See [LICENSE](LICENSE) for details.