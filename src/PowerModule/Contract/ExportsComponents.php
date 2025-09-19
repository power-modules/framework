<?php

namespace Modular\Framework\PowerModule\Contract;

interface ExportsComponents
{
    /**
     * @return array<string>
     */
    public static function exports(): array;
}
