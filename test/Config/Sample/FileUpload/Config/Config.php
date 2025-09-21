<?php

namespace Modular\Framework\Test\Config\Sample\FileUpload\Config;

use Modular\Framework\Config\Contract\PowerModuleConfig;

class Config extends PowerModuleConfig
{
    public const DEFAULT_STORAGE_PATH = '/tmp/storage/';
    public const DEFAULT_ACCEPT_EXTENSIONS = ['jpg', 'png'];

    public function getConfigFilename(): string
    {
        return 'file_upload';
    }

    public static function create(): static
    {
        return new static()
            ->set(Setting::LibStoragePath, self::DEFAULT_STORAGE_PATH)
            ->set(Setting::LibAcceptExtensions, self::DEFAULT_ACCEPT_EXTENSIONS)
        ;
    }
}
