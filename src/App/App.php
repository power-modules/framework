<?php

declare(strict_types=1);

namespace Modular\Framework\App;

use Modular\Framework\App\Config\Config;
use Modular\Framework\Container\ConfigurableContainer;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\Container\Exception\ContainerException;
use Modular\Framework\Container\InstanceResolver\RawValueInstanceResolver;
use Modular\Framework\PowerModule\Contract\CanCreatePowerModuleInstance;
use Modular\Framework\PowerModule\Contract\CanSetupPowerModule;
use Modular\Framework\PowerModule\Contract\ModuleDependencySorter;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\PowerModuleHelper;
use Modular\Framework\PowerModule\Setup\PowerModuleSetupDto;
use Modular\Framework\PowerModule\Setup\SetupPhase;
use Psr\Container\ContainerInterface;

class App implements ContainerInterface
{
    /**
     * @var array<CanSetupPowerModule>
     */
    private array $moduleSetups = [];

    public function __construct(
        private readonly Config $config,
        private readonly ConfigurableContainerInterface $rootContainer,
        private readonly ModuleDependencySorter $moduleDependencySorter,
        private readonly CanCreatePowerModuleInstance $canCreatePowerModuleInstance,
    ) {
    }

    /**
     * @template T
     *
     * @param class-string<T> $id
     *
     * @return T
     * Trade-off: the template helps with static analysis, but client code could complain if a simple string (not a class name) is used as $id
     */
    public function get(string $id)
    {
        return $this->rootContainer->get($id);
    }

    public function has(string $id): bool
    {
        return $this->rootContainer->has($id);
    }

    /**
     * @param array<class-string<PowerModule>> $powerModuleClassNames
     */
    public function registerModules(
        array $powerModuleClassNames,
    ): self {
        $modulesToRegister = $this->moduleDependencySorter->sort($powerModuleClassNames);

        /** @var array<PowerModule> $powerModules */
        $powerModules = array_map(
            $this->canCreatePowerModuleInstance->create(...),
            $modulesToRegister,
        );

        // First pass: register all modules and exported components in the root container. Setup phase: Pre
        foreach ($powerModules as $powerModule) {
            $this->registerModule($powerModule);
        }

        // Second pass: setup all modules. Setup phase: Post
        foreach ($powerModules as $powerModule) {
            $powerModuleName =  PowerModuleHelper::getPowerModuleName($powerModule);
            $setupDto = $this->getSetupDto($powerModule, SetupPhase::Post, $this->rootContainer->get($powerModuleName));

            foreach ($this->moduleSetups as $canSetupPowerModule) {
                $canSetupPowerModule->setup($setupDto);
            }
        }

        return $this;
    }

    public function addPowerModuleSetup(CanSetupPowerModule $canSetupPowerModule): self
    {
        $this->moduleSetups[$canSetupPowerModule::class] = $canSetupPowerModule;

        return $this;
    }

    private function registerModule(PowerModule $powerModule): self
    {
        $powerModuleName =  PowerModuleHelper::getPowerModuleName($powerModule);

        if ($this->rootContainer->has($powerModuleName) === true) {
            throw new ContainerException(
                sprintf('Cannot register module more than once: %s', $powerModuleName),
            );
        }

        $moduleContainer = new ConfigurableContainer();
        $setupDto = $this->getSetupDto($powerModule, SetupPhase::Pre, $moduleContainer);

        foreach ($this->moduleSetups as $canSetupPowerModule) {
            $canSetupPowerModule->setup($setupDto);
        }

        $powerModule->register($moduleContainer);

        $this->rootContainer->set(
            $powerModuleName,
            $moduleContainer,
            RawValueInstanceResolver::class,
        );

        return $this;
    }

    private function getSetupDto(
        PowerModule $powerModule,
        SetupPhase $setupPhase,
        ConfigurableContainerInterface $moduleContainer,
    ): PowerModuleSetupDto {
        return new PowerModuleSetupDto(
            $setupPhase,
            $powerModule,
            $this->rootContainer,
            $moduleContainer,
            $this->config,
        );
    }
}
