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
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Setup\ModularAppConfigInjector;
use Modular\Framework\PowerModule\Setup\PowerModuleSetupDto;
use Modular\Framework\PowerModule\Setup\SetupPhase;
use PHPUnit\Framework\TestCase;

class ModularAppConfigInjectorTest extends TestCase
{
    public function testSetupSetsConfigIfNotPresent(): void
    {
        $module = $this->createMock(PowerModule::class);
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $moduleContainer = $this->createMock(ConfigurableContainerInterface::class);
        $config = $this->createMock(Config::class);

        $moduleContainer->expects($this->once())
            ->method('has')
            ->with(Config::class)
            ->willReturn(false);
        $moduleContainer->expects($this->once())
            ->method('set')
            ->with(Config::class, $config);

        $dto = new PowerModuleSetupDto(
            SetupPhase::Pre,
            $module,
            $rootContainer,
            $moduleContainer,
            $config,
        );
        new ModularAppConfigInjector()->setup($dto);
    }

    public function testSetupDoesNothingIfConfigAlreadyPresent(): void
    {
        $module = $this->createMock(PowerModule::class);
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $moduleContainer = $this->createMock(ConfigurableContainerInterface::class);
        $config = $this->createMock(Config::class);

        $moduleContainer->expects($this->once())
            ->method('has')
            ->with(Config::class)
            ->willReturn(true);
        $moduleContainer->expects($this->never())->method('set');

        $dto = new PowerModuleSetupDto(
            SetupPhase::Pre,
            $module,
            $rootContainer,
            $moduleContainer,
            $config,
        );
        new ModularAppConfigInjector()->setup($dto);
    }
}
