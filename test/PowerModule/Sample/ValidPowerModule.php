<?php

declare(strict_types=1);

namespace Modular\Framework\Test\PowerModule\Sample;

use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;

class ValidPowerModule implements PowerModule
{
    public function register(ConfigurableContainerInterface $container): void
    {
    }
}
