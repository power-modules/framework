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

namespace Modular\Framework\PowerModule\Setup\Exception;

use RuntimeException;

class ExportCollisionException extends RuntimeException
{
    public function __construct(string $componentId, string $existingModule, string $conflictingModule)
    {
        $message = sprintf(
            'Export collision detected: Component "%s" is already exported by module "%s". Module "%s" cannot export it again. ' .
            'Each component must be exported by only one module. Update exports to avoid duplicates or use a shared module for common components.',
            $componentId,
            $existingModule,
            $conflictingModule,
        );

        parent::__construct($message);
    }
}
