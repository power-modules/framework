<?php

declare(strict_types=1);

namespace Modular\Framework\PowerModule\Contract;

use Modular\Framework\Container\ConfigurableContainerInterface;

interface PowerModule
{
    public function register(ConfigurableContainerInterface $container): void;
}
