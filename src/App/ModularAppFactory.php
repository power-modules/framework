<?php

declare(strict_types=1);

namespace Modular\Framework\App;

use InvalidArgumentException;
use Modular\Framework\App\Config\Config;
use Modular\Framework\Cache\FilesystemCache;
use Modular\Framework\Config\ConfigModule;
use Modular\Framework\Config\Loader;
use Modular\Framework\Container\ConfigurableContainer;
use Modular\Framework\Container\Exception\ContainerException;
use Modular\Framework\Container\Exception\ServiceDefinitionNotFound;
use Modular\Framework\PowerModule\CachingModuleDependencySorter;
use Modular\Framework\PowerModule\IterativeModuleDependencySorter;
use Modular\Framework\PowerModule\Setup\ExportsComponentsSetup;
use Modular\Framework\PowerModule\Setup\HasConfigSetup;
use Modular\Framework\PowerModule\Setup\ImportsComponentsSetup;
use Modular\Framework\PowerModule\Setup\ModularAppConfigInjector;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ModularAppFactory
{
    /**
     * Creates an instance of the App configured for a specific application root.
     *
     * @param string $appRoot Your application root directory.
     *
     * @throws ContainerException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ServiceDefinitionNotFound
     * @throws InvalidArgumentException
     */
    public static function forAppRoot(string $appRoot): App
    {
        $container = new ConfigurableContainer();
        $config = Config::forAppRoot($appRoot);

        $sorter = new IterativeModuleDependencySorter();
        $cache = new FilesystemCache($config->getCachePath());
        $cachingSorter = new CachingModuleDependencySorter($sorter, $cache);

        $app = new App(
            config: $config,
            rootContainer: $container,
            moduleDependencySorter: $cachingSorter,
        );

        $app
            ->addPowerModuleSetup(new ModularAppConfigInjector())
            ->addPowerModuleSetup(new ExportsComponentsSetup())
            ->addPowerModuleSetup(new ImportsComponentsSetup())
            ->registerModules([ConfigModule::class])
            ->addPowerModuleSetup(new HasConfigSetup($container->get(Loader::class)))
        ;

        return $app;
    }
}
