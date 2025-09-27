<?php

declare(strict_types=1);

namespace Modular\Framework\Test\PowerModule\Sample\Collision;

use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;

class SecondTestModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return ['shared-service'];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
    }
}
