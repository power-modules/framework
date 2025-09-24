<?php

/**
 * This file is part of the Modular Framework package.
 *
 * (c) 2025 Evgenii Teterin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
