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
