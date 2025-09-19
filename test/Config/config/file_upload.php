<?php

declare(strict_types=1);

use Modular\Framework\Test\Config\Sample\FileUpload\Config\Config;
use Modular\Framework\Test\Config\Sample\FileUpload\Config\Setting;

return Config::create()
    ->set(Setting::LibStoragePath, '/custom/storage/path/from/config/file')
;
