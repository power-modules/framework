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

use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\Container\Exception\ContainerException;
use Modular\Framework\Container\ServiceDefinition;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\ImportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\ImportItem;
use Modular\Framework\PowerModule\Setup\ImportsComponentsSetup;
use Modular\Framework\PowerModule\Setup\PowerModuleSetupDto;
use Modular\Framework\PowerModule\Setup\SetupPhase;
use PHPUnit\Framework\TestCase;

class ImportsComponentsSetupTest extends TestCase
{
    public function testSetupImportsItemsToModuleContainer(): void
    {
        $module = new class () implements ImportsComponents, PowerModule {
            public static function imports(): array
            {
                return [ImportItem::create(TestExportsModule::class, 'foo', 'bar')];
            }
            public function register(ConfigurableContainerInterface $container): void
            {
            }
        };
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $moduleContainer = $this->createMock(ConfigurableContainerInterface::class);
        $rootContainer->method('has')->willReturn(true);
        $rootContainer->method('getServiceDefinition')->willReturn($this->createMock(ServiceDefinition::class));
        $moduleContainer->expects($this->exactly(2))
            ->method('addServiceDefinition');
        $dto = new PowerModuleSetupDto(
            SetupPhase::Post,
            $module,
            $rootContainer,
            $moduleContainer,
            $this->createMock(\Modular\Framework\App\Config\Config::class),
        );
        new ImportsComponentsSetup()->setup($dto);
    }

    public function testSetupThrowsIfItemNotFound(): void
    {
        $module = new class () implements ImportsComponents, PowerModule {
            public static function imports(): array
            {
                return [ImportItem::create(TestExportsModule::class, 'foo')];
            }
            public function register(ConfigurableContainerInterface $container): void
            {
            }
        };
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $rootContainer->method('has')->willReturn(false);
        $moduleContainer = $this->createMock(ConfigurableContainerInterface::class);
        $dto = new PowerModuleSetupDto(
            SetupPhase::Post,
            $module,
            $rootContainer,
            $moduleContainer,
            $this->createMock(\Modular\Framework\App\Config\Config::class),
        );
        $this->expectException(ContainerException::class);
        new ImportsComponentsSetup()->setup($dto);
    }

    public function testSetupDoesNothingIfNotPostPhase(): void
    {
        $module = new class () implements ImportsComponents, PowerModule {
            public static function imports(): array
            {
                return [ImportItem::create(TestExportsModule::class, 'foo')];
            }
            public function register(ConfigurableContainerInterface $container): void
            {
            }
        };
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $moduleContainer = $this->createMock(ConfigurableContainerInterface::class);
        $moduleContainer->expects($this->never())->method('addServiceDefinition');
        $dto = new PowerModuleSetupDto(
            SetupPhase::Pre,
            $module,
            $rootContainer,
            $moduleContainer,
            $this->createMock(\Modular\Framework\App\Config\Config::class),
        );
        new ImportsComponentsSetup()->setup($dto);
    }

    public function testSetupDoesNothingIfModuleDoesNotImportComponents(): void
    {
        $module = new class () implements PowerModule {
            public function register(ConfigurableContainerInterface $container): void
            {
            }
        };
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $moduleContainer = $this->createMock(ConfigurableContainerInterface::class);
        $moduleContainer->expects($this->never())->method('addServiceDefinition');
        $dto = new PowerModuleSetupDto(
            SetupPhase::Post,
            $module,
            $rootContainer,
            $moduleContainer,
            $this->createMock(\Modular\Framework\App\Config\Config::class),
        );
        new ImportsComponentsSetup()->setup($dto);
    }
}

class TestExportsModule implements ExportsComponents
{
    public static function exports(): array
    {
        return ['foo', 'bar'];
    }
}
if (!class_exists(__NAMESPACE__ . '\\TestExportsModule', false)) {
    class TestExportsModule implements ExportsComponents
    {
        public static function exports(): array
        {
            return ['foo', 'bar'];
        }
    }
}
