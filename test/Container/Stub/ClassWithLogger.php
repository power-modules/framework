<?php

declare(strict_types=1);

namespace Modular\Framework\Test\Container\Stub;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ClassWithLogger implements LoggerAwareInterface
{
    use LoggerAwareTrait;
}
