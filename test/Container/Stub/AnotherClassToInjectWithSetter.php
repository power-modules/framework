<?php

declare(strict_types=1);

namespace Modular\Framework\Test\Container\Stub;

class AnotherClassToInjectWithSetter
{
    public ?ClassToInject $classDependency = null;

    public function setClassDependency(ClassToInject $classDependency): self
    {
        $this->classDependency = $classDependency;

        return $this;
    }
}
