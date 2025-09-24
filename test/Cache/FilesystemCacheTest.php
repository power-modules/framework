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

namespace Modular\Framework\Test\Cache;

use DateInterval;
use Modular\Framework\Cache\FilesystemCache;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class FilesystemCacheTest extends TestCase
{
    private string $cacheDir;
    private FilesystemCache $cache;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/test_cache_' . uniqid();
        $this->cache = new FilesystemCache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        // Clean up test cache directory
        if (is_dir($this->cacheDir)) {
            $this->removeDirectory($this->cacheDir);
        }
    }

    public function testImplementsCacheInterface(): void
    {
        $this->assertInstanceOf(CacheInterface::class, $this->cache);
    }

    public function testConstructorCreatesDirectory(): void
    {
        $this->assertTrue(is_dir($this->cacheDir));
        $this->assertTrue(is_writable($this->cacheDir));
    }

    public function testConstructorThrowsExceptionWhenCannotCreateDirectory(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to create cache directory:');

        // Create a mock directory that already exists as a file, not a directory
        $tempFile = tempnam(sys_get_temp_dir(), 'test_cache_file');

        try {
            // Try to create a cache directory using a path that's already a file
            new FilesystemCache($tempFile);
        } finally {
            // Clean up the temporary file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testSetAndGet(): void
    {
        $key = 'test_key';
        $value = 'test_value';

        $this->assertTrue($this->cache->set($key, $value));
        $this->assertEquals($value, $this->cache->get($key));
    }

    public function testGetWithDefault(): void
    {
        $defaultValue = 'default_value';
        $this->assertEquals($defaultValue, $this->cache->get('non_existent_key', $defaultValue));
    }

    public function testGetReturnsNullForNonExistentKey(): void
    {
        $this->assertNull($this->cache->get('non_existent_key'));
    }

    public function testSetWithIntegerTtl(): void
    {
        $key = 'ttl_key';
        $value = 'ttl_value';
        $ttl = 1; // 1 second

        $this->assertTrue($this->cache->set($key, $value, $ttl));
        $this->assertEquals($value, $this->cache->get($key));

        // Wait for expiration
        sleep(2);
        $this->assertNull($this->cache->get($key));
    }

    public function testSetWithDateIntervalTtl(): void
    {
        $key = 'interval_key';
        $value = 'interval_value';
        $ttl = new DateInterval('PT1S'); // 1 second

        $this->assertTrue($this->cache->set($key, $value, $ttl));
        $this->assertEquals($value, $this->cache->get($key));

        // Wait for expiration
        sleep(2);
        $this->assertNull($this->cache->get($key));
    }

    public function testSetWithNullTtl(): void
    {
        $key = 'permanent_key';
        $value = 'permanent_value';

        $this->assertTrue($this->cache->set($key, $value, null));
        $this->assertEquals($value, $this->cache->get($key));

        // Should still be there after some time
        sleep(1);
        $this->assertEquals($value, $this->cache->get($key));
    }

    public function testDelete(): void
    {
        $key = 'delete_key';
        $value = 'delete_value';

        $this->cache->set($key, $value);
        $this->assertEquals($value, $this->cache->get($key));

        $this->assertTrue($this->cache->delete($key));
        $this->assertNull($this->cache->get($key));
    }

    public function testDeleteNonExistentKey(): void
    {
        $this->assertTrue($this->cache->delete('non_existent_key'));
    }

    public function testClear(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->cache->set('key3', 'value3');

        $this->assertEquals('value1', $this->cache->get('key1'));
        $this->assertEquals('value2', $this->cache->get('key2'));
        $this->assertEquals('value3', $this->cache->get('key3'));

        $this->assertTrue($this->cache->clear());

        $this->assertNull($this->cache->get('key1'));
        $this->assertNull($this->cache->get('key2'));
        $this->assertNull($this->cache->get('key3'));
    }

    public function testHas(): void
    {
        $key = 'has_key';
        $value = 'has_value';

        $this->assertFalse($this->cache->has($key));

        $this->cache->set($key, $value);
        $this->assertTrue($this->cache->has($key));

        $this->cache->delete($key);
        $this->assertFalse($this->cache->has($key));
    }

    public function testHasWithExpiredKey(): void
    {
        $key = 'expired_key';
        $value = 'expired_value';

        $this->cache->set($key, $value, 1);
        $this->assertTrue($this->cache->has($key));

        sleep(2);
        $this->assertFalse($this->cache->has($key));
    }

    public function testGetMultiple(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');

        $keys = ['key1', 'key2', 'key3'];
        $result = $this->cache->getMultiple($keys, 'default');

        $expected = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'default',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testSetMultiple(): void
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $this->assertTrue($this->cache->setMultiple($values));

        $this->assertEquals('value1', $this->cache->get('key1'));
        $this->assertEquals('value2', $this->cache->get('key2'));
        $this->assertEquals('value3', $this->cache->get('key3'));
    }

    public function testSetMultipleWithTtl(): void
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $this->assertTrue($this->cache->setMultiple($values, 1));

        $this->assertEquals('value1', $this->cache->get('key1'));
        $this->assertEquals('value2', $this->cache->get('key2'));

        sleep(2);

        $this->assertNull($this->cache->get('key1'));
        $this->assertNull($this->cache->get('key2'));
    }

    public function testDeleteMultiple(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->cache->set('key3', 'value3');

        $keys = ['key1', 'key3'];
        $this->assertTrue($this->cache->deleteMultiple($keys));

        $this->assertNull($this->cache->get('key1'));
        $this->assertEquals('value2', $this->cache->get('key2'));
        $this->assertNull($this->cache->get('key3'));
    }

    public function testDeleteMultipleWithNonExistentKeys(): void
    {
        $keys = ['non_existent1', 'non_existent2'];
        $this->assertTrue($this->cache->deleteMultiple($keys));
    }

    public function testSetAndGetWithComplexData(): void
    {
        $complexData = [
            'array' => [1, 2, 3],
            'object' => (object) ['property' => 'value'],
            'nested' => [
                'level1' => [
                    'level2' => 'deep_value',
                ],
            ],
        ];

        $this->assertTrue($this->cache->set('complex_key', $complexData));
        $retrieved = $this->cache->get('complex_key');

        $this->assertEquals($complexData, $retrieved);
    }

    public function testCacheKeyHashing(): void
    {
        // Test that different keys produce different cache files
        $key1 = 'short';
        $key2 = 'a_very_long_key_that_should_be_hashed_differently';

        $this->cache->set($key1, 'value1');
        $this->cache->set($key2, 'value2');

        $this->assertEquals('value1', $this->cache->get($key1));
        $this->assertEquals('value2', $this->cache->get($key2));
    }

    public function testConcurrentAccess(): void
    {
        // Test that multiple operations don't interfere with each other
        $this->cache->set('concurrent1', 'value1');
        $this->cache->set('concurrent2', 'value2');

        $this->assertEquals('value1', $this->cache->get('concurrent1'));
        $this->assertEquals('value2', $this->cache->get('concurrent2'));

        $this->cache->delete('concurrent1');

        $this->assertNull($this->cache->get('concurrent1'));
        $this->assertEquals('value2', $this->cache->get('concurrent2'));
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
