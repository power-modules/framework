<?php

declare(strict_types=1);

namespace Modular\Framework\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class ServiceDefinitionNotFound extends \Exception implements NotFoundExceptionInterface
{
    public function __construct(
        string $id,
    ) {
        parent::__construct(sprintf('Service definition with id "%s" was not found.', $id));
    }
}
