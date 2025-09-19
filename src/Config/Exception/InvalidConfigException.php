<?php

declare(strict_types=1);

namespace Modular\Framework\Config\Exception;

use Modular\Framework\Config\Contract\PowerModuleConfig;

class InvalidConfigException extends \Exception
{
    public function __construct(
        string $path,
    ) {
        parent::__construct(
            sprintf('Configuration file "%s" is not instance of %s', $path, PowerModuleConfig::class),
        );
    }
}
