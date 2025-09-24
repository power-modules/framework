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

use Modular\Framework\App\App;
use Modular\Framework\App\Config\Config;
use Modular\Framework\App\Config\Setting;
use Modular\Framework\App\ModularAppBuilder;
use Modular\Framework\Test\App\Sample\LibA\LibAExternalService;
use Modular\Framework\Test\App\Sample\LibA\LibAInternalService;
use Modular\Framework\Test\App\Sample\LibA\LibAModule;
use Modular\Framework\Test\App\Sample\LibB\LibBService1;
use Modular\Framework\Test\App\Sample\LibC\LibCModule;
use Modular\Framework\Test\App\Sample\LibC\LibCServiceDependsOnLibBService1;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class AppTest extends TestCase
{
    private App $app;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new ModularAppBuilder(__DIR__)
            ->withConfig(Config::forAppRoot(__DIR__)->set(Setting::CachePath, sys_get_temp_dir()))
            ->build()
        ;
    }

    public function testAppRespectsExportsComponentsInterface(): void
    {
        $this->app->registerModules([
            LibAModule::class,
        ]);

        $this->assertFalse($this->app->has(LibAInternalService::class));
        $this->assertTrue($this->app->has(LibAExternalService::class));

        $this->assertInstanceOf(
            LibAExternalService::class,
            $instance = $this->app->get(LibAExternalService::class),
        );

        $this->assertSame(
            $instance,
            $this->app->get(LibAExternalService::class),
        );
    }

    public function testAppCanInjectExportedComponentsIntoDependentModuleContainer(): void
    {
        $this->app->registerModules([
            \Modular\Framework\Test\App\Sample\LibB\LibBModule::class,
            LibCModule::class,
        ]);

        /**
         * @var ContainerInterface $libCModuleContainer
         */
        $libCModuleContainer = $this->app->get(LibCModule::class);

        /** @var LibCServiceDependsOnLibBService1 $libCServiceDependsOnLibBService1 */
        $libCServiceDependsOnLibBService1 = $libCModuleContainer->get(LibCServiceDependsOnLibBService1::class);

        $this->assertInstanceOf(
            LibCServiceDependsOnLibBService1::class,
            $libCServiceDependsOnLibBService1,
        );

        $this->assertInstanceOf(
            LibBService1::class,
            $libCServiceDependsOnLibBService1->libBService1,
        );
    }
}
