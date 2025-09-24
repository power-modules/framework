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

namespace Modular\Framework\Test\PowerModule;

use Modular\Framework\PowerModule\Contract\ExportsComponents;
use Modular\Framework\PowerModule\ImportItem;
use PHPUnit\Framework\TestCase;

class ImportItemTest extends TestCase
{
    public function testCreateWithValidModuleAndItems(): void
    {
        $moduleName = ValidExportsModule::class;
        $item1 = 'foo';
        $item2 = 'bar';
        $importItem = ImportItem::create($moduleName, $item1, $item2);
        $this->assertInstanceOf(ImportItem::class, $importItem);
        $this->assertSame($moduleName, $importItem->moduleName);
        $this->assertSame([$item1, $item2], $importItem->itemsToImport);
    }

    public function testCreateThrowsForNonExportingModule(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ImportItem::create(NonExportsModule::class, 'foo');
    }

    public function testCreateThrowsForNonExportedItem(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ImportItem::create(ValidExportsModule::class, 'not_exported');
    }
}

class ValidExportsModule implements ExportsComponents
{
    public static function exports(): array
    {
        return ['foo', 'bar'];
    }
}

class NonExportsModule
{
}
