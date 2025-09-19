<?php

namespace Modular\Framework\Config\Contract;

// @phpstan-ignore-next-line
trait HasConfigTrait
{
    protected PowerModuleConfig $powerModuleConfig;

    public function setConfig(PowerModuleConfig $powerModuleConfig): void
    {
        $this->powerModuleConfig = $powerModuleConfig;
    }

    public function getConfig(): PowerModuleConfig
    {
        return $this->powerModuleConfig;
    }

    public function getConfigValue(\BackedEnum|\UnitEnum $configPropertyEnum): mixed
    {
        return $this->powerModuleConfig->get($configPropertyEnum);
    }
}
