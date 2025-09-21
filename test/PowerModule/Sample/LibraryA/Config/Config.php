<?php

namespace Modular\Framework\Test\PowerModule\Sample\LibraryA\Config;

use Modular\Framework\Config\Contract\PowerModuleConfig;

class Config extends PowerModuleConfig
{
    public function getConfigFilename(): string
    {
        return 'library_a';
    }

    public static function create(): static
    {
        return new static()
            ->set(Setting::SettingA, 'value1')
            ->set(Setting::SettingB, 'value2')
        ;
    }
}
