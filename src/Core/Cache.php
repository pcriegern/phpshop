<?php

namespace Deepcommerce\Phpshop\Core;

class Cache {

    /**
     * @var int Default TTL in seconds
     * 
     */
    private static $defaultTtl = 600;

    /**
     * @var string Cache directory
     */
    private static $cacheDir = '';

    /**
     * @var string Cache key
     */
    private $key;

    /**
     * @var int TTL in seconds
     */
    private $ttl;

    /**
     * @var string Filename
     */
    private $filename;

    /**
     * @var boolean useCache
     */
    private static $useCache = true;

    /**
     * Set default TTL
     */
    public static function setDefaultTtl($ttl) {
        static::$defaultTtl = $ttl;
    }

    /**
     * Set cache directory
     */
    public static function setCacheDir($dir) {
        static::$cacheDir = $dir;
        if ($dir == 'DISABLED') {
            static::$useCache = false;
        }
    }

    /**
     * Constructor
     * @param mixed $key
     * @param int $customTtl
     */
    public function __construct($key, $customTtl = 0) {
        if (!static::$useCache) {
            return false;
        }
        $this->key = $key;
        $this->ttl = ($customTtl > 0) ? $this->ttl = $customTtl: static::$defaultTtl;
        if (is_array($key) || is_object($key)) {
            $key = serialize($key);
        }
        $this->filename = static::$cacheDir . '/' . md5($key);
    }

    /**
     * Check if stored cache data is existing (and not expired)
     * @return bool
     */
    public function hit() {
        if (!static::$useCache) {
            return false;
        }
        if (!is_file($this->filename)) {
            return false;
        }
        if (filemtime($this->filename) < time() - $this->ttl) {
            unlink($this->filename);
            return false;
        }
        return true;
    }

    /**
     * Get cache data
     * @return mixed
     */
    public function get() {
        if (!$this->hit()) {
            return false;
        }
        return unserialize(file_get_contents($this->filename));
    }

    /**
     * Set cache data
     * @param mixed $data
     * @return void
     */
    public function set($data) {
        if (!static::$useCache) {
            return false;
        }
        file_put_contents($this->filename, serialize($data));
    }

}
