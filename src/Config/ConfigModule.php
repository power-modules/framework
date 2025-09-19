<?php

declare(strict_types=1);

namespace Modular\Framework\Config;

use Modular\Framework\App\Config\Config;
use Modular\Framework\App\Config\Setting;
use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;

class ConfigModule implements PowerModule, ExportsComponents
{
    public static function exports(): array
    {
        return [
            Loader::class,
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(
            Loader::class,
            Loader::class,
        )->addArguments([
            static fn (Config $config): string => sprintf('%s/config/', $config->get(Setting::AppRoot)),
        ]);
    }
}
