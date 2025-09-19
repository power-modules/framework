<?php

declare(strict_types=1);

namespace Modular\Framework\Test\Container\Stub;

class AnotherClassToInject
{
    public ClassToInject $classDependency;

    public function __construct(
        ClassToInject $classDependency,
    ) {
        $this->classDependency = $classDependency;
    }
}
