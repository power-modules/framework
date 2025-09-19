<?php

namespace Modular\Framework\Test\PowerModule\Sample\LibraryA;

use Modular\Framework\Config\Contract\HasConfig;
use Modular\Framework\Config\Contract\HasConfigTrait;
use Modular\Framework\Config\Contract\PowerModuleConfig;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\Test\PowerModule\Sample\LibraryA\Config\Config;

class ConfigurableLibraryAModule implements PowerModule, HasConfig
{
    use HasConfigTrait;

    public function __construct(
    ) {
        $this->powerModuleConfig = Config::create();
    }

    public function register(ConfigurableContainerInterface $container): void
    {
    }

    public function getConfig(): PowerModuleConfig
    {
        return $this->powerModuleConfig;
    }

    public function setConfig(PowerModuleConfig $powerModuleConfig): void
    {
        $this->powerModuleConfig = $powerModuleConfig;
    }
}
