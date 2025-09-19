<?php

declare(strict_types=1);

namespace Modular\Framework\Test\App\Sample\LibB;

use Modular\Framework\App\Config\Config;
use Modular\Framework\App\Config\Setting;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;

class LibBModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [
            LibBService1::class,
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(
            LibBService1::class,
            LibBService1::class,
        )->addArguments([
            LibBService2::class,
        ]);

        $container->set(
            LibBService2::class,
            LibBService2::class,
        )->addArguments([
            static fn (Config $modularAppConfig): string => $modularAppConfig->get(Setting::AppRoot),
        ]);
    }
}
