<?php

namespace Modular\Framework\PowerModule\Setup;

use Modular\Framework\App\Config\Config;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\PowerModule;

class PowerModuleSetupDto
{
    public function __construct(
        public readonly SetupPhase $setupPhase,
        public readonly PowerModule $powerModule,
        public readonly ConfigurableContainerInterface $rootContainer,
        public readonly ConfigurableContainerInterface $moduleContainer,
        public readonly Config $modularAppConfig,
    ) {
    }
}
