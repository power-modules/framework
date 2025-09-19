<?php

declare(strict_types=1);

namespace Modular\Framework\PowerModule;

use Modular\Framework\PowerModule\Contract\ModuleDependencySorter;
use Modular\Framework\PowerModule\Contract\PowerModule;
use Psr\SimpleCache\CacheInterface;

class CachingModuleDependencySorter implements ModuleDependencySorter
{
    private const CACHE_KEY_PREFIX = 'module_dependencies_';

    public function __construct(
        private ModuleDependencySorter $sorter,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @param array<class-string<PowerModule>> $powerModuleClassNames
     * @return array<class-string<PowerModule>>
     */
    public function sort(array $powerModuleClassNames): array
    {
        $cacheKey = $this->generateCacheKey($powerModuleClassNames);

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $sorted = $this->sorter->sort($powerModuleClassNames);

        $this->cache->set($cacheKey, $sorted);

        return $sorted;
    }

    /**
     * @param array<class-string<PowerModule>> $powerModuleClassNames
     */
    private function generateCacheKey(array $powerModuleClassNames): string
    {
        sort($powerModuleClassNames);

        return self::CACHE_KEY_PREFIX . md5(implode(',', $powerModuleClassNames));
    }
}
