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

namespace Modular\Framework\Test\Config\Contract;

use Modular\Framework\Test\Config\Contract\Sample\Config;
use Modular\Framework\Test\Config\Contract\Sample\Setting;
use PHPUnit\Framework\TestCase;

class PowerModuleConfigTest extends TestCase
{
    protected const CUSTOM_STORAGE_PATH = 'nfs://srv/storage/';

    public function testDefaultProperties(): void
    {
        $someModuleConfig = Config::create();

        $this->assertSame(
            Config::DEFAULT_STORAGE_PATH,
            $someModuleConfig->get(Setting::StoragePath),
        );

        $this->assertSame(
            Config::DEFAULT_ACCEPTED_FILE_EXTENSIONS,
            $someModuleConfig->get(Setting::AcceptedFileExtensions),
        );
    }

    public function testCustomProperties(): void
    {
        $customConfig = Config::create()
            ->set(Setting::StoragePath, self::CUSTOM_STORAGE_PATH)
        ;

        $this->assertSame(
            Config::DEFAULT_ACCEPTED_FILE_EXTENSIONS,
            $customConfig->get(Setting::AcceptedFileExtensions),
        );

        $this->assertSame(
            self::CUSTOM_STORAGE_PATH,
            $customConfig->get(Setting::StoragePath),
        );
    }

    public function testCanIterateOverConfigProperties(): void
    {
        $customConfig = Config::create()
            ->set(Setting::StoragePath, self::CUSTOM_STORAGE_PATH)
        ;

        foreach ($customConfig->getAll() as $configKey => $value) {
            if ($configKey === Setting::AcceptedFileExtensions) {
                $this->assertSame(
                    Config::DEFAULT_ACCEPTED_FILE_EXTENSIONS,
                    $value,
                );
            } elseif ($configKey === Setting::StoragePath) {
                $this->assertSame(
                    $customConfig->get(Setting::StoragePath),
                    self::CUSTOM_STORAGE_PATH,
                );
            } else {
                throw new \LogicException();
            }
        }
    }
}
