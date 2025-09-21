<?php

declare(strict_types=1);

namespace Modular\Framework\Cache;

use DateInterval;
use Psr\SimpleCache\CacheInterface;

class FilesystemCache implements CacheInterface
{
    private const FILE_EXTENSION = '.cache';

    public function __construct(private string $cacheDir)
    {
        if (!is_dir($this->cacheDir) && !@mkdir($this->cacheDir, 0775, true)) {
            throw new \RuntimeException(sprintf('Unable to create cache directory: %s', $this->cacheDir));
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $path = $this->getFilePath($key);

        if (!file_exists($path)) {
            return $default;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return $default;
        }

        $data = unserialize($content);

        if ($data['ttl'] !== null && $data['ttl'] < time()) {
            $this->delete($key);

            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $path = $this->getFilePath($key);

        $expiresAt = null;
        if ($ttl instanceof DateInterval) {
            $expiresAt = new \DateTime()->add($ttl)->getTimestamp();
        } elseif (is_int($ttl)) {
            $expiresAt = time() + $ttl;
        }

        $data = [
            'value' => $value,
            'ttl' => $expiresAt,
        ];

        return file_put_contents($path, serialize($data)) !== false;
    }

    public function delete(string $key): bool
    {
        $path = $this->getFilePath($key);

        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->cacheDir . '/*' . self::FILE_EXTENSION);
        if ($files === false) {
            return false;
        }
        $success = true;

        foreach ($files as $file) {
            if (!unlink($file)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param iterable<string> $keys
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param iterable<string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    private function getFilePath(string $key): string
    {
        return $this->cacheDir . '/' . sha1($key) . self::FILE_EXTENSION;
    }
}
