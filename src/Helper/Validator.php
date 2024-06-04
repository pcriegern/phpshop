<?php

namespace Deepcommerce\Phpshop\Helper;

/**
 * Class Validator
 * @package deepcommerce\Helper
 */
class Validator {

    /**
     * Last validation key and rule
     */
    private static $lastValidationKey  = '';
    private static $lastValidationRule = '';

    /**
     * Validate an array of data against a set of rules
     * @param array $data
     * @param array $rules
     * @return bool
     * @example
     * $data = [
     *   'name'  => 'John Doe',
     *   'email' => 'john@doe.com',
     *   'age'   => 25,
     * ];
     * $rules = [
     *   'name'  => 'required',
     *   'email' => 'required|email',
     *   'age'   => 'required|integer|min:18|max:99',
     * ];
     * $isValid = Validator::validateArray($data, $rules); // returns true
     */
    public static function validateArray(array $data, array $rules) {
        static::$lastValidationKey  = '';
        static::$lastValidationRule = '';
        if (empty($data) || !is_array($data) || empty($rules) || !is_array($rules)) {
            return false;
        }
        foreach ($rules as $key => $rule) {
            $value = isset($data[$key]) ? $data[$key] : null;
            static::$lastValidationKey = $key;
            if (!static::validate($value, $rule)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate an array of data against a set of rules and throw an exception if validation fails
     * @param array $data
     * @param array $rules
     * @throws \Exception
     */
    public static function validateArrayOrFail(array $data, array $rules) {
        if (!static::validateArray($data, $rules)) {
            throw new \Exception('Validation failed: ' . static::getLastValidation());
        }
    }

    /**
     * Validate a single value against a rule
     * @param mixed $value
     * @param string $rule
     * @param mixed $param
     * @return bool
     * @example
     * $isValid = Validator::validate($age, 'required|integer|min:18|max:99');
     */
    public static function validate($value, $rule) {
        $rule = strtolower(preg_replace('/\s/', '', $rule));
        $attributeRules = explode('|', $rule);
        if (in_array('optional', $attributeRules) && !isset($value)) {
            return true;
        }
        foreach ($attributeRules as $attributeRule) {
            static::$lastValidationRule = $attributeRule;
            if (strpos($attributeRule, ':') !== false) {
                list($attributeRule, $param) = explode(':', $attributeRule);
            } else {
                $param = null;
            }
            if (!self::validateSingleCondition($value, $attributeRule, $param)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the last validation error
     * @return string
     */
    public static function getLastValidation() {
        return static::$lastValidationKey . ' ' . static::$lastValidationRule;
    }

    /**
     * Validate a single condition
     * @param mixed $value
     * @param string $rule
     * @param mixed $param
     * @return bool
     * @example
     * $isValid = Validator::validateSingleCondition('$age', 'min', 18);
     */
    private static function validateSingleCondition($value, $rule, $param = null) {
        if (!isset($value)) {
            return false;
        }
        switch ($rule) {
            case 'optional':
                return true;
            case 'required':
                return preg_match('/\S+/', $value);
            case 'uuid':
                return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $value);
            case 'token':
                return preg_match('/^[0-9a-f]{40}$/', $value);
            case 'int':
            case 'integer':
                return preg_match('/^\d+$/', $value);
            case 'word':
                return preg_match('/^[\w\-]+$/', $value);
            case 'min':
                return $value >= $param;
            case 'max':
                return $value <= $param;
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL);
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL);
            case 'date':
                return strtotime($value) !== false;
            case 'regex':
                return preg_match($param, $value);
            default:
                return false;
        }
    }

}
