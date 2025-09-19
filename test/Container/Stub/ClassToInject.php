<?php

declare(strict_types=1);

namespace Modular\Framework\Test\Container\Stub;

class ClassToInject
{
    public string $stringDependency;

    public int $intDependency;

    public function __construct(
        string $stringDependency = 'stringDependencyValue',
        int $intDependency = 86_400,
    ) {
        $this->stringDependency = $stringDependency;
        $this->intDependency = $intDependency;
    }
}
