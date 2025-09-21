# Hello Module Example

> **📚 This page has been superseded by the new [Getting Started Guide](getting-started.md)** which provides a more comprehensive introduction to building modules.

For a quick hello world example, see the "Your First Module" section in the [Getting Started Guide](getting-started.md).

```php
<?php

declare(strict_types=1);

use Modular\Framework\App\ModularAppBuilder;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\Container\ConfigurableContainerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

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

final readonly class Greeter
{
    public function greet(string $name): string
    {
        return "Hello, {$name}!";
    }
}

// Create app from project root or pass modules directly
$app = new ModularAppBuilder(__DIR__ . '/../')->build();
$app->registerModules([
    HelloModule::class,
]);

// Anywhere in your code (root container):
$greeter = $app->get(Greeter::class);
echo $greeter->greet('World') . PHP_EOL; // Hello, World!
```