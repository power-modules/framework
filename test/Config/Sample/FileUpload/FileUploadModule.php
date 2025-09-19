<?php

declare(strict_types=1);

namespace Modular\Framework\Test\Config\Sample\FileUpload;

use Modular\Framework\Config\Contract\HasConfig;
use Modular\Framework\Config\Contract\HasConfigTrait;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\Test\Config\Sample\FileUpload\Config\Config;

class FileUploadModule implements PowerModule, HasConfig
{
    use HasConfigTrait;

    public function __construct()
    {
        $this->powerModuleConfig = Config::create();
    }

    public function register(ConfigurableContainerInterface $container): void
    {
    }
}
