<?php

namespace Modular\Framework\PowerModule\Contract;

use Modular\Framework\PowerModule\ImportItem;

interface ImportsComponents
{
    /**
     * @return array<ImportItem>
     */
    public static function imports(): array;
}
