<?php

declare(strict_types=1);

namespace Modular\Framework\App\Config;

enum Setting
{
    /**
     * Application root directory.
     */
    case AppRoot;

    /**
     * Cache directory.
     */
    case CachePath;
}
