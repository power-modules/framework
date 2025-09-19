<?php

declare(strict_types=1);

namespace Modular\Framework\Test\App\Sample\LibB;

class LibBService1
{
    public function __construct(
        public readonly LibBService2 $libBService2,
    ) {
    }
}
