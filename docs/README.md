# Modular Framework Documentation

Welcome to the complete guide for building modular PHP applications. Whether you're new to the framework or looking for advanced patterns, you'll find everything you need here.

## 🚀 Architectural Vision

Before diving into the technical details, it is highly recommended to read our **[Architectural Vision Document](../ARCHITECTURAL_VISION.md)**. It explains the core philosophy behind the framework, its key innovations, and how it compares to other solutions in the PHP ecosystem.

## 🎯 Quick Navigation

| Document | Purpose | Best For |
|----------|---------|----------|
| **[Getting Started](getting-started.md)** | Build your first module in 5 minutes | New users, quick prototypes |
| **[Architecture](architecture.md)** | Deep dive into framework internals | Understanding core concepts |
| **[API Reference](api-reference.md)** | Complete interface documentation | Development reference |
| **[Advanced Patterns](advanced-patterns.md)** | Complex scenarios and optimizations | Production applications |
| **[Migration Guide](migration-guide.md)** | Converting existing applications | Legacy system modernization |
| **[Use Cases](use-cases/README.md)** | Real-world implementation examples | Learning by example |

## 🚀 Start Here

**New to the framework?** Begin with [Getting Started](getting-started.md) to build your first module and understand the core concepts.

**Looking for examples?** Check out our [Use Cases](use-cases/README.md) section for complete implementations including:
- [Web API Development](use-cases/web-api.md)
- [ETL Data Pipelines](use-cases/etl-pipeline.md)

**Ready for production?** Review [Advanced Patterns](advanced-patterns.md) for performance optimization, plugin systems, and complex module compositions.

## 🏗️ Core Concepts Overview

The Modular Framework is built around several key architectural concepts:

### 📦 PowerModules
Self-contained units with isolated dependency injection containers. Each module can:
- Register its own services and dependencies
- Export services for other modules to use
- Import services from other modules
- Maintain complete encapsulation of internal components

### 🔗 Import/Export System
Explicit contracts that make module dependencies visible and controllable:
```php
// Module that exports services
class AuthModule implements PowerModule, ExportsComponents {
    public static function exports(): array {
        return [
            UserService::class,
        ];
    }
}

// Module that imports services
class OrderModule implements PowerModule, ImportsComponents {
    public static function imports(): array {
        return [
            ImportItem::create(AuthModule::class, UserService::class),
        ];
    }
}
```

### ⚡ PowerModuleSetup
A powerful extension mechanism that allows adding capabilities to ALL modules without breaking encapsulation:
```php
$app = new ModularAppBuilder(__DIR__)
    ->withModules(UserModule::class, OrderModule::class)
    ->addPowerModuleSetup(new RoutingSetup())    // Adds HTTP routing to modules implementing HasRoutes interface
    ->addPowerModuleSetup(new EventBusSetup())   // Pulls module events and handlers into a central event bus
    ->build();
```

### 🏗️ Builder Pattern
Applications are created using `ModularAppBuilder` with fluent configuration:
```php
$app = new ModularAppBuilder(__DIR__)
    ->withConfig($config)
    ->withModules(...$modules)
    ->addPowerModuleSetup(...$setups)
    ->build();
```

## 🌟 Key Benefits

- **🔒 True Encapsulation**: Each module has its own isolated DI container
- **📋 Explicit Dependencies**: Import/export contracts make relationships visible
- **🚀 Microservice Ready**: Module boundaries naturally become service boundaries
- **🧪 Better Testing**: Modules can be tested in isolation
- **👥 Team Scalability**: Different teams can own different modules independently
- **🔌 Plugin-Ready**: Third-party modules can extend functionality safely

## 🛠️ Development Workflow

The project includes several tools to streamline development:

```sh
make test         # Run PHPUnit tests
make codestyle    # Check PHP CS Fixer compliance  
make phpstan      # Run static analysis (level 8)
make devcontainer # Build development container
```

## 🔧 Extension Ecosystem

The framework supports a rich ecosystem of extensions through PowerModuleSetup:

- **[power-modules/router](https://github.com/power-modules/router)**: HTTP routing with PSR-15 middleware support
- **power-modules/events**: Event-driven architecture (Coming soon!)
- **Custom extensions**: Build your own authentication, logging, validation, and other cross-cutting concerns

Extensions work across ALL modules automatically while maintaining module isolation and testability.

## 📖 Documentation Standards

Our documentation follows these principles:

- **Example-Driven**: Every concept includes working code examples
- **Practical Focus**: Real-world scenarios over theoretical explanations
- **Progressive Complexity**: Simple examples first, advanced patterns later
- **Copy-Paste Friendly**: Code snippets that work out of the box

## 🎯 Learning Path

We recommend this learning sequence:

1. **[Getting Started](getting-started.md)** - Build your first module (15 minutes)
2. **[Use Cases](use-cases/README.md)** - See complete implementations (30 minutes)
3. **[Architecture](architecture.md)** - Understand the framework deeply (45 minutes)
4. **[Advanced Patterns](advanced-patterns.md)** - Production-ready techniques (60 minutes)
5. **[API Reference](api-reference.md)** - Comprehensive interface documentation (Reference)

## 💡 Getting Help

- **Issues**: Found a bug or have a feature request? [Open an issue](https://github.com/power-modules/framework/issues)
- **Discussions**: Questions about usage? [Start a discussion](https://github.com/power-modules/framework/discussions)
- **Contributing**: Want to contribute? See [CONTRIBUTING.md](../CONTRIBUTING.md)

## 🎉 Ready to Build?

Choose your next step:

- **Quick Start**: Jump into [Getting Started](getting-started.md)
- **Learn by Example**: Explore [Use Cases](use-cases/README.md)
- **Deep Dive**: Read the [Architecture Guide](architecture.md)
- **Reference**: Browse the [API Documentation](api-reference.md)

---

*The Modular Framework is designed to grow with your application - from simple scripts to complex microservice architectures. Start simple, scale naturally.*