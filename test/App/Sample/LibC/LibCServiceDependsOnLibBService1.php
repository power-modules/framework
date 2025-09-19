<?php

declare(strict_types=1);

namespace Modular\Framework\Test\App\Sample\LibC;

use Modular\Framework\Test\App\Sample\LibB\LibBService1;

class LibCServiceDependsOnLibBService1
{
    public function __construct(
        public readonly LibBService1 $libBService1,
    ) {
    }
}
