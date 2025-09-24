# Getting Started

Get up and running with the Modular Framework in minutes. This guide walks you through three progressively more complex examples to build your understanding.

## Installation

Install via Composer:

```sh
composer require power-modules/framework
```

## Example 1: Your First Module

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
readonly class Greeter
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
        return [
            Greeter::class,
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(Greeter::class, Greeter::class);
    }
}

// Build and use the application
$app = new ModularAppBuilder(__DIR__)
    ->withModules(HelloModule::class)
    ->build();

// Get the exported service
$greeter = $app->get(Greeter::class);
echo $greeter->greet('World') . PHP_EOL; // Hello, World!
```

## Example 2: Dependencies Within a Module

Now let's add constructor dependencies to see how services depend on each other:

```php
<?php

declare(strict_types=1);

use Modular\Framework\App\ModularAppBuilder;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\Container\ConfigurableContainerInterface;

require_once __DIR__ . '/vendor/autoload.php';

// A service that our Greeter will depend on
readonly class GreetingFormatter
{
    public function format(string $message): string
    {
        return "🎉 " . strtoupper($message) . " 🎉";
    }
}

// Enhanced Greeter with a dependency
readonly class Greeter
{
    public function __construct(
        private GreetingFormatter $formatter,
    ) {}

    public function greet(string $name): string
    {
        $message = "Hello, {$name}!";
        return $this->formatter->format($message);
    }
}

// Module with internal dependencies
final readonly class HelloModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [
            Greeter::class, // Only Greeter is exported
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // GreetingFormatter is private to this module
        $container->set(GreetingFormatter::class, GreetingFormatter::class);
        
        // Greeter depends on GreetingFormatter via constructor injection
        $container->set(Greeter::class, Greeter::class)
            ->addArguments([GreetingFormatter::class]);
    }
}

// Build and use the application
$app = new ModularAppBuilder(__DIR__)
    ->withModules(HelloModule::class)
    ->build();

$greeter = $app->get(Greeter::class);
echo $greeter->greet('World') . PHP_EOL; // 🎉 HELLO, WORLD! 🎉

// GreetingFormatter is NOT available outside the module
var_dump($app->has(GreetingFormatter::class)); // false
```

## Example 3: Importing Dependencies from Another Module

The most powerful feature - using services from other modules through explicit imports:

```php
<?php

declare(strict_types=1);

use Modular\Framework\App\ModularAppBuilder;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\ImportsComponents;
use Modular\Framework\PowerModule\ImportItem;
use Modular\Framework\Container\ConfigurableContainerInterface;

require_once __DIR__ . '/vendor/autoload.php';

// ===== Logger Module =====
// Note: In production, consider using psr/log
interface LoggerInterface
{
    public function log(string $message): void;
}

readonly class Logger implements LoggerInterface
{
    public function log(string $message): void
    {
        echo "[" . date('Y-m-d H:i:s') . "] {$message}" . PHP_EOL;
    }
}

final readonly class LoggerModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [
            LoggerInterface::class, // Best practice: Export interfaces, not concrete classes
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // Wire interface to implementation
        $container->set(LoggerInterface::class, Logger::class);
    }
}

// ===== Greeting Module (imports Logger) =====
readonly class GreetingFormatter
{
    public function __construct(
        private LoggerInterface $logger, // Depend on interface, not implementation
    ) {}

    public function format(string $message): string
    {
        $formatted = "🎉 " . strtoupper($message) . " 🎉";
        $this->logger->log("Formatted greeting: {$formatted}");
        return $formatted;
    }
}

readonly class Greeter
{
    public function __construct(
        private GreetingFormatter $formatter,
    ) {}

    public function greet(string $name): string
    {
        $message = "Hello, {$name}!";
        return $this->formatter->format($message);
    }
}

final readonly class GreetingModule implements PowerModule, ImportsComponents, ExportsComponents
{
    public static function imports(): array
    {
        return [
            // Import the interface, not the concrete class
            ImportItem::create(LoggerModule::class, LoggerInterface::class),
        ];
    }

    public static function exports(): array
    {
        return [
            Greeter::class,
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        // LoggerInterface is available via import
        $container->set(GreetingFormatter::class, GreetingFormatter::class)
            ->addArguments([LoggerInterface::class]);
        
        $container->set(Greeter::class, Greeter::class)
            ->addArguments([GreetingFormatter::class]);
    }
}

// Build the application with both modules
$app = new ModularAppBuilder(__DIR__)
    ->withModules(
        LoggerModule::class,
        GreetingModule::class,
    )
    ->build();

$greeter = $app->get(Greeter::class);
echo $greeter->greet('World') . PHP_EOL;

// Output:
// [2025-09-23 21:48:45] Formatted greeting: 🎉 HELLO, WORLD! 🎉
// 🎉 HELLO, WORLD! 🎉
```

## Core Concepts

### **PowerModule**
Every module implements the `PowerModule` interface with a single `register()` method where you define your services.

### **Exports & Imports**
- **ExportsComponents**: Declares which services this module makes available to others
- **ImportsComponents**: Declares which services this module needs from other modules

### **Module Encapsulation**
Each module has its own DI container. Services are private by default - only exported services are accessible from outside the module.

### **Constructor Injection**
Use `->addArguments([ServiceClass::class])` to inject dependencies into constructors. The framework automatically resolves services by their class names.

## Next Steps

- **[Architecture Guide](architecture.md)** - Deep dive into the import/export system and module lifecycle
- **[Use Cases](use-cases/README.md)** - Real-world examples across different domains
- **[API Reference](api-reference.md)** - Complete interface documentation