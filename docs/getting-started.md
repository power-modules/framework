# Getting Started

Get up and running with the Modular Framework in minutes.

## Installation

Install via Composer:

```sh
composer require power-modules/framework
```

## Your First Module

The simplest way to understand the framework is to build a basic module:

```php
<?php

declare(strict_types=1);

use Modular\Framework\App\ModularAppBuilder;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\Container\ConfigurableContainerInterface;

require_once __DIR__ . '/vendor/autoload.php';

// Define the service
final readonly class Greeter
{
    public function greet(string $name): string
    {
        return "Hello, {$name}!";
    }
}

// Define a module that exports a service
final readonly class HelloModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [Greeter::class];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(Greeter::class, Greeter::class);
    }
}

// Build and use the application
$app = new ModularAppBuilder(__DIR__)->build();
$app->registerModules([HelloModule::class]);

// Get the exported service
$greeter = $app->get(Greeter::class);
echo $greeter->greet('World') . PHP_EOL; // Hello, World!
```

## Core Concepts

### **PowerModule**
Every module implements the `PowerModule` interface with a single `register()` method where you define your services.

### **Exports & Imports**
- **ExportsComponents**: Declares which services this module makes available to others
- **ImportsComponents**: Declares which services this module needs from other modules

### **Module Encapsulation**
Each module has its own DI container. Services are private by default - only exported services are accessible from outside the module.

## Next Steps

- **[Architecture Guide](architecture.md)** - Deep dive into the import/export system and module lifecycle
- **[Use Cases](use-cases/README.md)** - Real-world examples across different domains
- **[API Reference](api-reference.md)** - Complete interface documentation