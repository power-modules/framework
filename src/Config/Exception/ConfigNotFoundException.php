<?php

declare(strict_types=1);

namespace Modular\Framework\Config\Exception;

class ConfigNotFoundException extends \Exception
{
    public function __construct(
        string $path,
    ) {
        parent::__construct(sprintf('Configuration file was not found: %s', $path));
    }
}
