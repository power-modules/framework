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
use Modular\Framework\Container\ConfigurableContainer;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\Container\InstanceResolver\InstanceViaContainerResolver;
use Modular\Framework\Container\ServiceDefinition;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Setup\Exception\ExportCollisionException;
use Modular\Framework\PowerModule\Setup\ExportsComponentsSetup;
use Modular\Framework\PowerModule\Setup\PowerModuleSetupDto;
use Modular\Framework\PowerModule\Setup\SetupPhase;
use Modular\Framework\Test\PowerModule\Sample\Collision\FirstTestModule;
use Modular\Framework\Test\PowerModule\Sample\Collision\SecondTestModule;
use PHPUnit\Framework\TestCase;

class ExportsComponentsSetupTest extends TestCase
{
    public function testSetupExportsAllItemsToRootContainer(): void
    {
        $module = new FirstTestModule();
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $moduleContainer = new ConfigurableContainer();

        $calls = [];
        $rootContainer->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($id, $value, $resolver) use (&$calls) {
                $calls[] = [$id, $value, $resolver];

                return $this->createMock(ServiceDefinition::class);
            });

        $dto = new PowerModuleSetupDto(
            setupPhase: SetupPhase::Pre,
            powerModule: $module,
            rootContainer: $rootContainer,
            moduleContainer: $moduleContainer,
            modularAppConfig: Config::create(),
        );

        new ExportsComponentsSetup()->setup($dto);

        $expected = [
            ['shared-service', $moduleContainer, InstanceViaContainerResolver::class],
            ['first-only-service', $moduleContainer, InstanceViaContainerResolver::class],
        ];
        $this->assertSame($expected, $calls);
    }

    public function testSetupThrowsExceptionOnExportCollision(): void
    {
        $setup = new ExportsComponentsSetup();
        // First module exports 'shared-service'
        $firstModule = new FirstTestModule();
        $rootContainer = new ConfigurableContainer();
        $firstModuleContainer = new ConfigurableContainer();
        $appConfig = Config::create();

        $firstDto = new PowerModuleSetupDto(
            setupPhase: SetupPhase::Pre,
            powerModule: $firstModule,
            rootContainer: $rootContainer,
            moduleContainer: $firstModuleContainer,
            modularAppConfig: $appConfig,
        );

        // First export should succeed
        $setup->setup($firstDto);

        // Second module tries to export the same service
        $secondModule = new SecondTestModule();
        $secondModuleContainer = $this->createMock(ConfigurableContainerInterface::class);

        $secondDto = new PowerModuleSetupDto(
            setupPhase: SetupPhase::Pre,
            powerModule: $secondModule,
            rootContainer: $rootContainer,
            moduleContainer: $secondModuleContainer,
            modularAppConfig: $appConfig,
        );

        // Second export should throw exception
        $this->expectException(ExportCollisionException::class);
        $this->expectExceptionMessage('Export collision detected: Component "shared-service" is already exported');

        $setup->setup($secondDto);
    }

    public function testSetupAllowsSameModuleToBeProcessedMultipleTimes(): void
    {
        $setup = new ExportsComponentsSetup();
        $module = new FirstTestModule();
        $rootContainer = new ConfigurableContainer();
        $moduleContainer = new ConfigurableContainer();

        $dto = new PowerModuleSetupDto(
            setupPhase: SetupPhase::Pre,
            powerModule: $module,
            rootContainer: $rootContainer,
            moduleContainer: $moduleContainer,
            modularAppConfig: Config::create(),
        );

        // First setup should succeed
        $setup->setup($dto);

        // Verify the service was registered
        $this->assertTrue($rootContainer->has('shared-service'));
        $this->assertTrue($rootContainer->has('first-only-service'));

        // Second setup with the same module should also succeed without exceptions
        $setup->setup($dto);
    }

    public function testSetupDoesNothingIfNotPrePhase(): void
    {
        $module = new FirstTestModule();
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $rootContainer->expects($this->never())->method('set');
        $rootContainer->expects($this->never())->method('has');
        $dto = new PowerModuleSetupDto(
            setupPhase: SetupPhase::Post,
            powerModule: $module,
            rootContainer: $rootContainer,
            moduleContainer: new ConfigurableContainer(),
            modularAppConfig: Config::create(),
        );
        new ExportsComponentsSetup()->setup($dto);
    }

    public function testSetupDoesNothingIfModuleDoesNotExportComponents(): void
    {
        $module = new class () implements PowerModule {
            public function register(ConfigurableContainerInterface $container): void
            {
            }
        };
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $rootContainer->expects($this->never())->method('set');
        $rootContainer->expects($this->never())->method('has');

        $dto = new PowerModuleSetupDto(
            setupPhase: SetupPhase::Pre,
            powerModule: $module,
            rootContainer: $rootContainer,
            moduleContainer: new ConfigurableContainer(),
            modularAppConfig: Config::create(),
        );
        new ExportsComponentsSetup()->setup($dto);
    }
}
