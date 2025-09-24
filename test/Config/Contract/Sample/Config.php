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

namespace Modular\Framework\Test\Config\Contract\Sample;

use Modular\Framework\Config\Contract\PowerModuleConfig;

class Config extends PowerModuleConfig
{
    public const DEFAULT_STORAGE_PATH = '/tmp/storage/';
    public const DEFAULT_ACCEPTED_FILE_EXTENSIONS = ['jpg', 'png'];

    public function getConfigFilename(): string
    {
        return '';
    }

    public static function create(): static
    {
        return new static()
            ->set(Setting::StoragePath, self::DEFAULT_STORAGE_PATH)
            ->set(Setting::AcceptedFileExtensions, self::DEFAULT_ACCEPTED_FILE_EXTENSIONS)
        ;
    }
}
