<?php

/**
 * This file is part of the Modular Framework package.
 *
 * (c) 2025 Evgenii Teterin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
