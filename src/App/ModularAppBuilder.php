<?php

declare(strict_types=1);

namespace Modular\Framework\App;

use Modular\Framework\App\Config\Config;
use Modular\Framework\App\Config\Setting;
use Modular\Framework\Cache\FilesystemCache;
use Modular\Framework\Config\ConfigModule;
use Modular\Framework\Config\Loader;
use Modular\Framework\Container\ConfigurableContainer;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\CachingModuleDependencySorter;
use Modular\Framework\PowerModule\Contract\ModuleDependencySorter;
use Modular\Framework\PowerModule\IterativeModuleDependencySorter;
use Modular\Framework\PowerModule\Setup\ExportsComponentsSetup;
use Modular\Framework\PowerModule\Setup\HasConfigSetup;
use Modular\Framework\PowerModule\Setup\ImportsComponentsSetup;
use Modular\Framework\PowerModule\Setup\ModularAppConfigInjector;
use Psr\SimpleCache\CacheInterface;

class ModularAppBuilder
{
    private ?Config $config = null;
    private ?ConfigurableContainerInterface $rootContainer = null;
    private ?ModuleDependencySorter $moduleDependencySorter = null;

    public function __construct(
        private readonly string $appRoot,
    ) {
    }

    public function withConfig(Config $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function withRootContainer(ConfigurableContainerInterface $container): self
    {
        $this->rootContainer = $container;

        return $this;
    }

    public function withModuleDependencySorter(ModuleDependencySorter $sorter): self
    {
        $this->moduleDependencySorter = $sorter;

        return $this;
    }

    public function withCache(CacheInterface $cache): self
    {
        $this->moduleDependencySorter = new CachingModuleDependencySorter(
            new IterativeModuleDependencySorter(),
            $cache,
        );

        return $this;
    }

    public function build(): App
    {
        $config = $this->config ?? Config::forAppRoot($this->appRoot);
        $rootContainer = $this->rootContainer ?? new ConfigurableContainer();
        $dependencySorter = $this->moduleDependencySorter ?? new CachingModuleDependencySorter(
            new IterativeModuleDependencySorter(),
            new FilesystemCache($config->get(Setting::CachePath)),
        );

        $app = new App(
            config: $config,
            rootContainer: $rootContainer,
            moduleDependencySorter: $dependencySorter,
        );

        $app->addPowerModuleSetup(new ModularAppConfigInjector())
            ->addPowerModuleSetup(new ExportsComponentsSetup())
            ->addPowerModuleSetup(new ImportsComponentsSetup())
            ->registerModules([ConfigModule::class])
            ->addPowerModuleSetup(new HasConfigSetup($rootContainer->get(Loader::class)))
        ;

        return $app;
    }
}
