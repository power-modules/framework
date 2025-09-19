<?php

declare(strict_types=1);

namespace Modular\Framework\Test\PowerModule\Setup;

use Modular\Framework\App\Config\Config;
use Modular\Framework\App\Config\Setting;
use Modular\Framework\Config\Contract\HasConfig;
use Modular\Framework\Config\Contract\PowerModuleConfig;
use Modular\Framework\Config\Loader;
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
        $mockConfig = $this->createMock(PowerModuleConfig::class);
        $mockConfig->expects($this->once())
            ->method('set')
            ->with(Setting::AppRoot, 'root-dir');

        $mockLoader = $this->createMock(Loader::class);
        $mockAppConfig = $this->createMock(Config::class);
        $mockAppConfig->expects($this->once())
            ->method('get')
            ->with(Setting::AppRoot)
            ->willReturn('root-dir');

        $mockHasConfigPowerModule = new class ($mockConfig) implements HasConfig, PowerModule {
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

        $mockLoader->expects($this->once())
            ->method('getConfig')
            ->with($mockHasConfigPowerModule)
            ->willReturn($mockConfig);

        $dto = new PowerModuleSetupDto(
            SetupPhase::Pre,
            $mockHasConfigPowerModule,
            $this->createMock(ConfigurableContainerInterface::class),
            $this->createMock(ConfigurableContainerInterface::class),
            $mockAppConfig,
        );

        (new HasConfigSetup($mockLoader))->setup($dto);
    }

    public function testSetupDoesNothingIfNotPrePhase(): void
    {
        $mockLoader = $this->createMock(Loader::class);
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
            $this->createMock(ConfigurableContainerInterface::class),
            $this->createMock(ConfigurableContainerInterface::class),
            $this->createMock(Config::class),
        );
        $mockLoader->expects($this->never())->method('getConfig');
        (new HasConfigSetup($mockLoader))->setup($dto);
    }

    public function testSetupDoesNothingIfModuleDoesNotHaveConfig(): void
    {
        $mockLoader = $this->createMock(Loader::class);
        $mockPowerModule = $this->createMock(PowerModule::class);
        $dto = new PowerModuleSetupDto(
            SetupPhase::Pre,
            $mockPowerModule,
            $this->createMock(ConfigurableContainerInterface::class),
            $this->createMock(ConfigurableContainerInterface::class),
            $this->createMock(Config::class),
        );
        $mockLoader->expects($this->never())->method('getConfig');
        (new HasConfigSetup($mockLoader))->setup($dto);
    }
}
