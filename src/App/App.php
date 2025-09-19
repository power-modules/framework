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
use Modular\Framework\PowerModule\DefaultModuleResolver;
use Modular\Framework\PowerModule\GetPowerModuleNameTrait;
use Modular\Framework\PowerModule\Setup\PowerModuleSetupDto;
use Modular\Framework\PowerModule\Setup\SetupPhase;
use Psr\Container\ContainerInterface;

class App implements ContainerInterface
{
    use GetPowerModuleNameTrait;

    /**
     * @var array<CanSetupPowerModule>
     */
    private array $moduleSetups = [];
    private CanCreatePowerModuleInstance $canCreatePowerModuleInstance;

    public function __construct(
        private Config $config,
        private ConfigurableContainerInterface $rootContainer,
        private ModuleDependencySorter $moduleDependencySorter,
    ) {
        $this->canCreatePowerModuleInstance = new DefaultModuleResolver();
    }

    /**
     * @template T
     *
     * @param class-string<T> $id
     *
     * @return T
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
            fn (string $powerModuleClassName): PowerModule => $this->canCreatePowerModuleInstance->create($powerModuleClassName),
            $modulesToRegister,
        );

        foreach ($powerModules as $powerModule) {
            $this->registerModule($powerModule);
        }

        foreach ($powerModules as $powerModule) {
            foreach ($this->moduleSetups as $canSetupPowerModule) {
                $canSetupPowerModule->setup(
                    $this->getSetupDto($powerModule, SetupPhase::Post, $this->rootContainer->get($this->getPowerModuleName($powerModule))),
                );
            }
        }

        return $this;
    }

    public function addPowerModuleSetup(CanSetupPowerModule $canSetupPowerModule): self
    {
        $this->moduleSetups[$canSetupPowerModule::class] = $canSetupPowerModule;

        return $this;
    }

    public function setModuleResolver(CanCreatePowerModuleInstance $canCreatePowerModuleInstance): self
    {
        $this->canCreatePowerModuleInstance = $canCreatePowerModuleInstance;

        return $this;
    }

    private function registerModule(PowerModule $powerModule): self
    {
        $powerModuleName = $this->getPowerModuleName($powerModule);

        if ($this->rootContainer->has($powerModuleName) === true) {
            throw new ContainerException(
                sprintf('Cannot register module more than once: %s', $powerModuleName),
            );
        }

        $moduleContainer = new ConfigurableContainer();

        foreach ($this->moduleSetups as $canSetupPowerModule) {
            $canSetupPowerModule->setup(
                $this->getSetupDto($powerModule, SetupPhase::Pre, $moduleContainer),
            );
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
