<?php

namespace Modular\Framework\App\Config;

use Modular\Framework\Config\Contract\PowerModuleConfig;

class Config extends PowerModuleConfig
{
    public static function forAppRoot(string $appRoot): self
    {
        $instance = new self();
        $instance->set(Setting::AppRoot, $appRoot);
        $instance->set(Setting::CachePath, $appRoot . '/cache');

        return $instance;
    }

    public function getCachePath(): string
    {
        return $this->get(Setting::CachePath);
    }

    public function getConfigFilename(): string
    {
        return 'modular_app';
    }
}
