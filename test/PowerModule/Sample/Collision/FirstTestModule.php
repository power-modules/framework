<?php

declare(strict_types=1);

namespace Modular\Framework\Test\PowerModule\Sample\Collision;

use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;

class FirstTestModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return ['shared-service', 'first-only-service'];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
    }
}
