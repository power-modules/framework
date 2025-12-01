<?php

declare(strict_types=1);

namespace Modular\Framework\Test\App;

use Modular\Framework\App\Config\Config;
use Modular\Framework\App\Config\Setting;
use Modular\Framework\App\ModularAppBuilder;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\Contract\PowerModuleSetup;
use Modular\Framework\PowerModule\Setup\PowerModuleSetupDto;
use Modular\Framework\PowerModule\Setup\SetupPhase;
use PHPUnit\Framework\TestCase;

class PowerModuleSetupOrderTest extends TestCase
{
    public function testPostSetupPhaseIteratesSetupsThenModules(): void
    {
        $collector = new CollectorSetup();
        $verifier = new VerifierSetup($collector);

        $app = new ModularAppBuilder(__DIR__)
            ->withConfig(Config::forAppRoot(__DIR__)->set(Setting::CachePath, sys_get_temp_dir()))
            ->withPowerSetup($collector)
            ->withPowerSetup($verifier)
            ->withModules(ModuleA::class, ModuleB::class)
            ->build();

        // We expect Verifier to run AFTER Collector has run for ALL modules.
        // If Verifier runs for Module A immediately after Collector runs for Module A,
        // then Collector hasn't seen Module B yet.

        $this->expectNotToPerformAssertions();
    }
}

class ModuleA implements PowerModule
{
    public function register(ConfigurableContainerInterface $container): void
    {
    }
}

class ModuleB implements PowerModule
{
    public function register(ConfigurableContainerInterface $container): void
    {
    }
}

class CollectorSetup implements PowerModuleSetup
{
    /**
     * @var array<class-string<PowerModule>>
     */
    public array $collectedModules = [];

    public function setup(PowerModuleSetupDto $powerModuleSetupDto): void
    {
        if ($powerModuleSetupDto->setupPhase === SetupPhase::Post) {
            $this->collectedModules[] = $powerModuleSetupDto->powerModule::class;
        }
    }
}

class VerifierSetup implements PowerModuleSetup
{
    public function __construct(private CollectorSetup $collector)
    {
    }

    public function setup(PowerModuleSetupDto $powerModuleSetupDto): void
    {
        if ($powerModuleSetupDto->setupPhase === SetupPhase::Post) {
            // We expect 2 modules to be collected by the time Verifier runs for ANY module
            if (count($this->collector->collectedModules) < 2) {
                throw new \RuntimeException(sprintf(
                    'Verifier ran too early! Collected modules: %s. Current module: %s',
                    implode(', ', $this->collector->collectedModules),
                    $powerModuleSetupDto->powerModule::class,
                ));
            }
        }
    }
}
