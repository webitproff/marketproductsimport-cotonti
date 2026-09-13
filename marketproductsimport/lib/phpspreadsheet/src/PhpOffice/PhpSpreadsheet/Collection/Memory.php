<?php

namespace PhpOffice\PhpSpreadsheet\Collection;

use Psr\SimpleCache\CacheInterface;

class Memory implements CacheInterface
{
    /**
     * @var array<string, mixed>
     */
    private $cache = [];

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->has($key)) {
            return $this->cache[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null|int|\DateInterval $ttl
     * @return bool
     */
    public function set(string $key, mixed $value, $ttl = null): bool
    {
        $this->cache[$key] = $value;

        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        unset($this->cache[$key]);

        return true;
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        $this->cache = [];

        return true;
    }

    /**
     * @param iterable<string> $keys
     * @param mixed $default
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * @param iterable<string, mixed> $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            if (is_int($key)) {
                $key = (string) $key;
            }
            $this->cache[$key] = $value;
        }

        return true;
    }

    /**
     * @param iterable<string> $keys
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->cache);
    }
}