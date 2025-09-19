<?php

namespace Modular\Framework\Test\Config;

use Modular\Framework\Config\Loader;
use Modular\Framework\Test\Config\Sample\FileUpload\Config\Setting;
use Modular\Framework\Test\Config\Sample\FileUpload\FileUploadModule;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    public function test(): void
    {
        $configLoader = $this->getConfigLoader();
        $moduleConfig = $configLoader->getConfig(new FileUploadModule());

        $this->assertSame(
            '/custom/storage/path/from/config/file',
            $moduleConfig->get(Setting::LibStoragePath),
        );
    }

    private function getConfigLoader(): Loader
    {
        return new Loader(__DIR__.'/config/');
    }
}
