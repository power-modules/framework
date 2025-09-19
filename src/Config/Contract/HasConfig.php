<?php

namespace Modular\Framework\Config\Contract;

interface HasConfig
{
    public function getConfig(): PowerModuleConfig;
    public function setConfig(PowerModuleConfig $powerModuleConfig): void;
}
