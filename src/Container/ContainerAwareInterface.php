<?php

declare(strict_types=1);

namespace Modular\Framework\Container;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container): self;

    public function getContainer(): ?ContainerInterface;
}
