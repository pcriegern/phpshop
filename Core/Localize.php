<?php

namespace deepcommerce\phpshop\Core;

class Localize {

    /**
     * Default settings
     */
    public static $currencySymbol        = '€';
    public static $currencyCode          = 'EUR';
    public static $decimalPoint          = ',';
    public static $thousandsSeparator    = '.';
    public static $dateFormat            = 'd.m.Y';
    public static $baseHref              = '';
    
    protected static $availableLanguages = [];
    protected static $language           = 'de';
    protected static $fallbackLanguage   = 'en';

    protected static $translations;

    /**
     * Initialize Localize, get settings from Config
     * @param array $config
     */
    public static function init(array $config) {
        if (isset($config['currency'])) {
            self::$currencyCode = $config['currency'];
        }
        if (isset($config['currency_symbol'])) {
            self::$currencySymbol = $config['currency_symbol'];
        }
        if (isset($config['decimal_point'])) {
            self::$decimalPoint = $config['decimal_point'];
        }
        if (isset($config['thousands_separator'])) {
            self::$thousandsSeparator = $config['thousands_separator'];
        }
        if (isset($config['date_format'])) {
            self::$dateFormat = $config['date_format'];
        }
        if (isset($config['languages'])) {
            self::$availableLanguages = $config['languages'];
        }
        if (isset($config['default_language'])) {
            self::$language = $config['default_language'];
        }
        if (isset($config['fallback_language'])) {
            self::$fallbackLanguage = $config['fallback_language'];
        }
        if (isset($config['base_href'])) {
            self::$baseHref = $config['base_href'];
        }
    }

    /**
     * Load context-specific translations
     * @param string $context
     * @param string $context2
     * @throws \Exception
     */
    public static function loadTranslations($context = '', $context2 = '') {
		if (empty(self::$translations)) {
			self::$translations = static::getTranslationContent('translations.json');
        }
        if (strlen($context2)) {
            // "static" approach to avoid redundant merges (redundand code instead of redundand executions)
            // (merge a + b + c instead of merging a + b, then merging a+b + c)
            self::$translations = array_merge(
                self::$translations, 
                static::getTranslationContent("translations-{$context}.json"), 
                static::getTranslationContent("translations-{$context2}.json")
            );
        } else if (strlen($context)) {
            self::$translations = array_merge(
                self::$translations, 
                static::getTranslationContent("translations-{$context}.json")
            );
        }
	}

    /**
     * Load translations from JSON files
     * @param string $filename
     * @return array
     * @throws \Exception
     */
	protected static function getTranslationContent($filename) {
		$path = __DIR__ . '/../../../../config/' . $filename;
		if (!file_exists($path)) {
            throw new \Exception('TRANSLATION NOT FOUND: ' . $path);
		}
		return json_decode(file_get_contents($path), true);
	}

    /**
     * Set active language
     * @param string $language
     * @throws \Exception
     */
    public static function setLanguage($language) {
        if (empty(static::$availableLanguages[$language])) {
            throw new \Exception('Language not defined: ' . $language);
        }
        self::$language = $language;
    }

    /**
     * Get language configuration
     * @param string $language
     * @return array
     */
    public static function getLanguageConfig($language) {
        return static::$availableLanguages[$language];
    }

    /**
     * Get active language
     * @return string
     */
    public static function getLanguage() {
        return self::$language;
    }

    /**
     * Get text in active language
     * @example Localize::text(["de":"Hallo","en":"Hello"], 'en');
     * @param string|array $text
     * @param string $language
     * @return string
     */
    public static function text($text, $language = null) {
        if (is_string($text) && substr($text, 0, 2) === '{"') {
            $text = json_decode($text, true);
        }
        if (is_string($text) || is_numeric($text)) {
            return $text;
        } else if (is_array($text)) {
            if ($language === null) {
                $language = self::$language;
            }
            if (isset($text[$language])) {
                return $text[$language];
            } else if (isset($text[self::$fallbackLanguage])) {
                return $text[self::$fallbackLanguage];
            }
        } else if ($text === null) {
            return '';
        }
        return '???' . print_r($text, true) . '???';
    }

    /**
     * Translate text
     * @param string $text
     * @return string
     * @example Localize::translate('Hello');
     */
    public static function translate($text) {
		if (static::$translations === null) {
			static::loadTranslations();
		}
        if (!empty(self::$translations[$text])) {
            if (!empty(self::$translations[$text][self::$language])) {
                return self::text(self::$translations[$text][self::$language]);
            }
        }
        return $text;
    }

    /**
     * Convert Database Timestamp or Unix Timestamp to Date
     * @param int $timestamp
     * @return string
     */
    public static function date ($timestamp) {
        if (is_numeric($timestamp)) {
            return date(static::$dateFormat, $timestamp);
        }
        return date(static::$dateFormat, strtotime($timestamp));
    }

    /**
     * Format price
     * @param int $amount
     * @return string
     */
    public static function price ($amount) {
        return static::numberFormat($amount/100) . '&nbsp;' . static::$currencySymbol;
    }

    /**
     * Format number
     * @param int $value
     * @param int $decimals
     * @return string
     */
    public static function numberFormat($value, $decimals = 2) {
        return number_format($value, $decimals, static::$decimalPoint, static::$thousandsSeparator);
    }

    /**
     * Get absolute image path
     * @param string $path
     * @return string
     */
    public static function absoluteImagePath($path) {
        if (empty($path)) {
            return $path;
        }
        if (preg_match('/^https?\:/', $path)) {
            return $path;
        }
        return self::$baseHref . $path;
    }

}
