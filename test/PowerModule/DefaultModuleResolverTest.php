<?php

declare(strict_types=1);

namespace Modular\Framework\Test\PowerModule;

use Modular\Framework\PowerModule\DefaultModuleResolver;
use Modular\Framework\Test\PowerModule\Sample\ValidPowerModule;
use PHPUnit\Framework\TestCase;

class DefaultModuleResolverTest extends TestCase
{
    public function testCreateWillResolveValidPowerModule(): void
    {
        $resolver = new DefaultModuleResolver();
        $this->assertInstanceOf(ValidPowerModule::class, $resolver->create(ValidPowerModule::class));
    }
}
