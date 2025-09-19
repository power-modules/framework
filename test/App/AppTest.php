<?php

declare(strict_types=1);

namespace Modular\Framework\Test\App;

use Modular\Framework\App\App;
use Modular\Framework\App\ModularAppFactory;
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
        $this->app = ModularAppFactory::forAppRoot(__DIR__);
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

    protected function tearDown(): void
    {
        parent::tearDown();

        $cacheDir = __DIR__ . '/cache';
        if (!is_dir($cacheDir)) {
            return;
        }

        $files = glob($cacheDir . '/*');
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        rmdir($cacheDir);
    }
}
