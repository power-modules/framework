<?php

declare(strict_types=1);

namespace Modular\Framework\Test\PowerModule\Setup;

use Modular\Framework\App\Config\Config;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\Container\InstanceResolver\InstanceViaContainerResolver;
use Modular\Framework\Container\ServiceDefinition;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Setup\ExportsComponentsSetup;
use Modular\Framework\PowerModule\Setup\PowerModuleSetupDto;
use Modular\Framework\PowerModule\Setup\SetupPhase;
use PHPUnit\Framework\TestCase;

class ExportsComponentsSetupTest extends TestCase
{
    public function testSetupExportsAllItemsToRootContainer(): void
    {
        $module = $this->makeExportsPowerModule(['foo', 'bar']);
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $moduleContainer = $this->createMock(ConfigurableContainerInterface::class);
        $config = $this->createMock(Config::class);

        $calls = [];
        $rootContainer->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($id, $value, $resolver) use (&$calls) {
                $calls[] = [$id, $value, $resolver];

                return $this->createMock(ServiceDefinition::class);
            });

        $dto = new PowerModuleSetupDto(
            SetupPhase::Pre,
            $module,
            $rootContainer,
            $moduleContainer,
            $config,
        );

        new ExportsComponentsSetup()->setup($dto);

        $expected = [
            ['foo', $moduleContainer, InstanceViaContainerResolver::class],
            ['bar', $moduleContainer, InstanceViaContainerResolver::class],
        ];
        $this->assertSame($expected, $calls);
    }

    public function testSetupDoesNothingIfNotPrePhase(): void
    {
        $module = $this->makeExportsPowerModule(['foo']);
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $rootContainer->expects($this->never())->method('set');
        $dto = new PowerModuleSetupDto(
            SetupPhase::Post,
            $module,
            $rootContainer,
            $this->createMock(ConfigurableContainerInterface::class),
            $this->createMock(Config::class),
        );
        new ExportsComponentsSetup()->setup($dto);
    }

    public function testSetupDoesNothingIfModuleDoesNotExportComponents(): void
    {
        $module = $this->makePowerModule();
        $rootContainer = $this->createMock(ConfigurableContainerInterface::class);
        $rootContainer->expects($this->never())->method('set');
        $dto = new PowerModuleSetupDto(
            SetupPhase::Pre,
            $module,
            $rootContainer,
            $this->createMock(ConfigurableContainerInterface::class),
            $this->createMock(Config::class),
        );
        new ExportsComponentsSetup()->setup($dto);
    }

    /**
     * @param array<string> $exports
     */
    private function makeExportsPowerModule(array $exports): PowerModule
    {
        return new class () implements ExportsComponents, PowerModule {
            public static function exports(): array
            {
                return ['foo', 'bar'];
            }
            public function register(ConfigurableContainerInterface $container): void
            {
            }
        };
    }

    private function makePowerModule(): PowerModule
    {
        return new class () implements PowerModule {
            public function register(ConfigurableContainerInterface $container): void
            {
            }
        };
    }
}
