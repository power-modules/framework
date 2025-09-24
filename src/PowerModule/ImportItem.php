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

namespace Modular\Framework\PowerModule;

use Modular\Framework\PowerModule\Contract\ExportsComponents;

class ImportItem
{
    /**
     * @param class-string $moduleName
     * @param array<string|class-string> $itemsToImport
     *
     * @throws \InvalidArgumentException
     */
    private function __construct(
        public readonly string $moduleName,
        public readonly array $itemsToImport,
    ) {
        if (is_a($moduleName, ExportsComponents::class, true) === false) {
            throw new \InvalidArgumentException(
                sprintf('Provided module (%s) does not implement %s interface', $moduleName, ExportsComponents::class),
            );
        }

        /** @var class-string<ExportsComponents> $moduleName */
        $exportedItems = $moduleName::exports();

        foreach ($itemsToImport as $itemToImport) {
            if (in_array($itemToImport, $exportedItems) === false) {
                throw new \InvalidArgumentException(
                    sprintf('%s does not export requested item (%s)', $moduleName, $itemToImport),
                );
            }
        }
    }

    /**
     * @param class-string $powerModuleName
     * @param array<string|class-string> ...$itemsToImport
     */
    public static function create(string $powerModuleName, string ...$itemsToImport): self
    {
        return new self($powerModuleName, $itemsToImport);
    }
}
