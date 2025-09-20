<?php

namespace Modular\Framework\PowerModule\Setup;

use Modular\Framework\App\Config\Config;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;

final readonly class PowerModuleSetupDto
{
    public function __construct(
        public SetupPhase $setupPhase,
        public PowerModule $powerModule,
        public ConfigurableContainerInterface $rootContainer,
        public ConfigurableContainerInterface $moduleContainer,
        public Config $modularAppConfig,
    ) {
    }
}
