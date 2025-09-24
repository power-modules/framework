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

namespace Modular\Framework\Test\PowerModule\Setup;

use Modular\Framework\App\Config\Config;
use Modular\Framework\App\Config\Setting;
use Modular\Framework\Config\Contract\HasConfig;
use Modular\Framework\Config\Contract\PowerModuleConfig;
use Modular\Framework\Config\Loader;
use Modular\Framework\Container\ConfigurableContainer;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Setup\HasConfigSetup;
use Modular\Framework\PowerModule\Setup\PowerModuleSetupDto;
use Modular\Framework\PowerModule\Setup\SetupPhase;
use PHPUnit\Framework\TestCase;

class HasConfigSetupTest extends TestCase
{
    public function testSetupSetsConfigAndAppRoot(): void
    {
        $powerModule = new \Modular\Framework\Test\PowerModule\Sample\LibraryA\ConfigurableLibraryAModule();
        $rootContainer = new ConfigurableContainer();
        $rootContainer->set(Loader::class, new Loader(__DIR__ . '/../../Test/PowerModule/Sample'));

        $dto = new PowerModuleSetupDto(
            SetupPhase::Pre,
            $powerModule,
            $rootContainer,
            $this->createMock(ConfigurableContainerInterface::class),
            Config::create()->set(Setting::AppRoot, 'root-dir')->set(Setting::CachePath, 'root-dir/cache'),
        );
        $hasConfigSetup = new HasConfigSetup();
        $hasConfigSetup->setup($dto);
        self::assertSame(
            'root-dir',
            $powerModule->getConfig()->get(Setting::AppRoot),
        );
        self::assertSame(
            'root-dir/cache',
            $powerModule->getConfig()->get(Setting::CachePath),
        );
    }

    public function testSetupDoesNothingIfNotPrePhase(): void
    {
        $mockLoader = $this->createMock(Loader::class);
        $mockLoader->expects($this->never())->method('getConfig');

        $rootContainer = new ConfigurableContainer();
        $rootContainer->set(Loader::class, $mockLoader);

        $mockHasConfigPowerModule = new class ($this->createMock(PowerModuleConfig::class)) implements HasConfig, PowerModule {
            private PowerModuleConfig $config;
            public function __construct(PowerModuleConfig $config)
            {
                $this->config = $config;
            }
            public function getConfig(): PowerModuleConfig
            {
                return $this->config;
            }
            public function setConfig(PowerModuleConfig $powerModuleConfig): void
            {
                $this->config = $powerModuleConfig;
            }
            public function register(ConfigurableContainerInterface $container): void
            {
            }
        };
        $dto = new PowerModuleSetupDto(
            SetupPhase::Post,
            $mockHasConfigPowerModule,
            $rootContainer,
            $this->createMock(ConfigurableContainerInterface::class),
            $this->createMock(Config::class),
        );
        new HasConfigSetup()->setup($dto);
    }

    public function testSetupDoesNothingIfModuleDoesNotHaveConfig(): void
    {
        $mockLoader = $this->createMock(Loader::class);
        $mockLoader->expects($this->never())->method('getConfig');

        $rootContainer = new ConfigurableContainer();
        $rootContainer->set(Loader::class, $mockLoader);

        $mockPowerModule = $this->createMock(PowerModule::class);
        $dto = new PowerModuleSetupDto(
            SetupPhase::Pre,
            $mockPowerModule,
            $rootContainer,
            $this->createMock(ConfigurableContainerInterface::class),
            $this->createMock(Config::class),
        );
        new HasConfigSetup()->setup($dto);
    }
}
