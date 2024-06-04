<?php

namespace Deepcommerce\Phpshop\Core;

abstract class Config {

    /**
     * Global Settings
     */
    protected static $settings;

    /**
     * Shop specific settings that will overwrite global settings
     */
    protected static $shops;

    /**
     * Get Shop Config by Domain
     * @param string $domain
     * @return array|null
     */
    public static function getShopConfigById($shopId) {
        if (!isset(static::$shops[$shopId])) {
            return null;
        }
        foreach (static::$shops[$shopId] as $k => $v) {
            static::$settings[$k] = $v;
        }
        return static::$settings;
    }

    /**
     * Get Shop Config by Domain
     * @param string $domain
     * @return array|null
     */
    public static function getShopConfigByDomain($domain) {
        foreach (static::$shops as $id => $shop) {
            if (preg_match('/' . $shop['domain'] . '/', $domain)) {
                return static::getShopConfigById($id);
            }
        }
        return null;
    }

    /**
     * Get specific configuration setting
     * @param string $key
     * @return mixed
     */
    public static function get($key) {
        return static::$settings[$key];
    }

    /**
     * Set specific configuration setting
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        static::$settings[$key] = $value;
    }

    /**
     * Get available languages as numeric array
     * @return array
     */
    public static function getAvailableLanguages() {
        return array_values(static::$settings['languages']);
    }

    /**
     * Get default language
     * @return string
     */
    public static function defaultLanguage() {
        return static::get('default_language');
    }

    /**
     * Check if language is available
     * @param string $languageCode
     * @return bool
     */
    public static function isAvailableLanguage($languageCode) {
        return !empty(static::$settings['languages'][$languageCode]);
    }

    /**
     * Get language configuration
     * @param string $languageCode
     * @return array
     */
    public static function getLanguageConfig($languageCode) {
        return static::$settings['languages'][$languageCode];
    }

}
