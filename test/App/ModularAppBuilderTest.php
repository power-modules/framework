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

namespace Modular\Framework\Test\App;

use Modular\Framework\App\Config\Config;
use Modular\Framework\App\Config\Setting;
use Modular\Framework\App\ModularAppBuilder;
use Modular\Framework\Container\ConfigurableContainer;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\CanCreatePowerModuleInstance;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\ModuleDependencySorter;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\PowerModuleSetup;
use Modular\Framework\Test\PowerModule\Sample\LibraryA\ConfigurableLibraryAModule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

#[CoversClass(ModularAppBuilder::class)]
class ModularAppBuilderTest extends TestCase
{
    public function testItCanBuildAppWithConfig(): void
    {
        $app = $this->getBuilderWithTempCachePath()->build();
        $config = $app->get(Config::class);

        self::assertSame(__DIR__, $config->get(Setting::AppRoot));
        self::assertSame(sys_get_temp_dir(), $config->get(Setting::CachePath));
    }

    public function testItCanBuildAppWithRootContainer(): void
    {
        $rootContainer = new ConfigurableContainer();
        $rootContainer->set('test', 'value');
        $app = $this->getBuilderWithTempCachePath()->withRootContainer($rootContainer)->build();

        // @phpstan-ignore-next-line
        self::assertSame('value', $app->get('test'));
    }

    public function testItCanBuildAppWithModuleDependencySorter(): void
    {
        $mock = $this->createMock(ModuleDependencySorter::class);
        $mock->expects(self::once())->method('sort')->willReturn([]);

        $this->getBuilderWithTempCachePath()->withModuleDependencySorter($mock)->build();
    }

    public function testItCanBuildAppWithCache(): void
    {
        $mock = $this->createMock(CacheInterface::class);
        $mock->expects(self::once())->method('get')->willReturn(null);
        $mock->expects(self::once())->method('set')->willReturn(true);

        $this->getBuilderWithTempCachePath()->withCache($mock)->build();
    }

    public function testItCanBuildAppWithModuleResolver(): void
    {
        $moduleResolver = new class () implements CanCreatePowerModuleInstance {
            public function create(string $id): PowerModule
            {
                throw new RuntimeException('Custom Module Resolver used');
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Custom Module Resolver used');
        $this->getBuilderWithTempCachePath()->withModuleResolver($moduleResolver)->withModules(
            ConfigurableLibraryAModule::class,
        )->build();
    }

    public function testItCanBuildAppWithPowerSetup(): void
    {
        $mock = $this->createMock(PowerModuleSetup::class);
        $mock->expects(self::exactly(4))->method('setup');
        $this->getBuilderWithTempCachePath()->withPowerSetup($mock)->withModules(
            ConfigurableLibraryAModule::class,
        )->build();
    }

    public function testItCanBuildAppWithModules(): void
    {
        $moduleWithExports = new class () implements PowerModule, ExportsComponents {
            public static function exports(): array
            {
                return ['exported_entity'];
            }

            public function register(ConfigurableContainerInterface $container): void
            {
                $container->set('exported_entity', 'exported_entity_value');
            }
        };

        $app = $this->getBuilderWithTempCachePath()->withModules(
            $moduleWithExports::class,
        )->build();

        self::assertTrue($app->has('exported_entity'));
        // @phpstan-ignore-next-line
        self::assertIsString($app->get('exported_entity'));
        // @phpstan-ignore-next-line
        self::assertSame('exported_entity_value', $app->get('exported_entity'));
    }

    private function getBuilderWithTempCachePath(): ModularAppBuilder
    {
        return new ModularAppBuilder(__DIR__)->withConfig(
            Config::forAppRoot(__DIR__)->set(Setting::CachePath, sys_get_temp_dir()),
        );
    }
}
