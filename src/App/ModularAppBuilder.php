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

namespace Modular\Framework\App;

use InvalidArgumentException;
use Modular\Framework\App\Config\Config;
use Modular\Framework\App\Config\Setting;
use Modular\Framework\Cache\FilesystemCache;
use Modular\Framework\Config\ConfigModule;
use Modular\Framework\Container\ConfigurableContainer;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\CachingModuleDependencySorter;
use Modular\Framework\PowerModule\Contract\CanCreatePowerModuleInstance;
use Modular\Framework\PowerModule\Contract\CanSetupPowerModule;
use Modular\Framework\PowerModule\Contract\ModuleDependencySorter;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\DefaultModuleResolver;
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
    private ?CanCreatePowerModuleInstance $canCreatePowerModuleInstance = null;
    private ?CacheInterface $cache = null;

    /**
     * @var array<string,CanSetupPowerModule>
     */
    private array $powerSetups = [];

    /**
     * @var array<class-string<PowerModule>>
     */
    private array $modules = [];

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
        $this->cache = $cache;

        return $this;
    }

    public function withModuleResolver(CanCreatePowerModuleInstance $moduleResolver): self
    {
        $this->canCreatePowerModuleInstance = $moduleResolver;

        return $this;
    }

    public function withPowerSetup(CanSetupPowerModule ...$setups): self
    {
        foreach ($setups as $setup) {
            $this->powerSetups[$setup::class] = $setup;
        }

        return $this;
    }

    /**
     * @param class-string<PowerModule> $modules
     */
    public function withModules(string ...$modules): self
    {
        foreach ($modules as $module) {
            // @phpstan-ignore function.alreadyNarrowedType
            if (is_a($module, PowerModule::class, true) === false) {
                throw new InvalidArgumentException(sprintf('Module %s must implement %s', $module, PowerModule::class));
            }

            $this->modules[$module] = $module;
        }

        return $this;
    }

    public function build(): App
    {
        $config = $this->config ?? Config::forAppRoot($this->appRoot);
        $cache = $this->cache ?? new FilesystemCache($config->get(Setting::CachePath));
        $rootContainer = $this->rootContainer ?? new ConfigurableContainer();
        $rootContainer->set(Config::class, $config);
        $dependencySorter = $this->moduleDependencySorter ?? new CachingModuleDependencySorter(
            new IterativeModuleDependencySorter(),
            $cache,
        );
        $moduleResolver = $this->canCreatePowerModuleInstance ?? new DefaultModuleResolver();

        $app = new App(
            config: $config,
            rootContainer: $rootContainer,
            moduleDependencySorter: $dependencySorter,
            canCreatePowerModuleInstance: $moduleResolver,
        );

        $setups = [
            new ModularAppConfigInjector(),
            new ExportsComponentsSetup(),
            new ImportsComponentsSetup(),
            new HasConfigSetup(),
            ...array_values($this->powerSetups),
        ];

        foreach ($setups as $setup) {
            $app->addPowerModuleSetup($setup);
        }

        $modules = [
            ConfigModule::class,
            ...array_values($this->modules),
        ];

        $app->registerModules($modules);

        return $app;
    }
}
