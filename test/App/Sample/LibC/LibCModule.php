<?php

declare(strict_types=1);

namespace Modular\Framework\Test\App\Sample\LibC;

use Modular\Framework\Container\ConfigurableContainerInterface;
use Modular\Framework\PowerModule\Contract\ImportsComponents;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Modular\Framework\PowerModule\ImportItem;
use Modular\Framework\Test\App\Sample\LibB\LibBModule;
use Modular\Framework\Test\App\Sample\LibB\LibBService1;

class LibCModule implements PowerModule, ImportsComponents
{
    public static function imports(): array
    {
        return [
            ImportItem::create(LibBModule::class, LibBService1::class),
        ];
    }

    public function register(ConfigurableContainerInterface $container): void
    {
        $container->set(
            LibCServiceDependsOnLibBService1::class,
            LibCServiceDependsOnLibBService1::class,
        )->addArguments([
            LibBService1::class,
        ]);
    }
}
