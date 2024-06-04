<?php

namespace Deepcommerce\Phpshop\Helper;

/**
 * Class ArrayHelper
 * @package deepcommerce\Helper
 */
class ArrayHelper {

	/**
	 * Get an element from an array by key/value-match
	 * @param array $array
	 * @param string $attribute
	 * @param mixed $value
	 * @return array|null
	 */
	public static function getElementByAttribute(array $array, string $attribute, $value) {
		foreach ($array as $item) {
			if (isset($item[$attribute]) && $item[$attribute] == $value) {
				return $item;
			}
		}
		return null;
	}

	/**
	 * Filter elements from an array by key
	 * @param array $array
	 * @param string $attribute
	 * @param mixed $value
	 * @return array
	 */
	public static function filterArrayByAttribute(array $array, string $attribute, $value) {
		$list = [];
		foreach ($array as $item) {
			if (isset($item[$attribute]) && $item[$attribute] == $value) {
				$list[] = $item;
			}
		}
		return $list;
	}

	/**
	 * Get an element from an array by key
	 * @param array $array
	 * @param string $key
	 * @param bool $createMissingKeys
	 * @return array
	 */
	public static function filterByKeys(array $array, array $keys, $createMissingKeys = false) {
		$extract = [];
		foreach ($keys as $key) {
			if (isset($array[$key])) {
				$extract[$key] = $array[$key];
			} elseif ($createMissingKeys) {
				$extract[$key] = null;
			}
		}
		return $extract;
	}

	/**
	 * Build a tree from a flat array (parent_id -> id relation)
	 * @param array $array
	 * @param string $parent_attribute
	 * @return array
	 */
    public static function buildTree($array, $parent_attribute = 'parent_id') {
        $map = static::toMap($array, 'id');
        $tree = [];
        foreach ($map as $id => &$node) {
            if (isset($node[$parent_attribute])) {
                $map[$node[$parent_attribute]]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        if (empty($tree[0])) {
            return [];
        }
        return $tree[0]['children'];
    }

	/**
	 * Convert an array to a map by key
	 * @param array $array
	 * @param string $key
	 * @return array
	 */
	public static function toMap(array $array, string $key) {
		$map = [];
		foreach ($array as $item) {
			if (isset($item[$key])) {
				$map[$item[$key]] = $item;
			}
		}
		return $map;
	}

	/**
	 * Map attributes from an array to a key/value map
	 * @param array $array
	 * @param string $key
	 * @param string $value
	 * @return array
	 */
	public static function mapAttribute(array $array, string $key, string $value) {
		$map = [];
		foreach ($array as $item) {
			if (isset($item[$key]) && isset($item[$value])) {
				$map[$item[$key]] = $item[$value];
			}
		}
		return $map;
	}

	/**
	 * Get attributes from an array as a list
	 * @param array $array
	 * @param string $key
	 * @return array
	 */
	public static function getAttributesAsArray(array $array, string $key) {
		return array_column($array, $key);
	}

	/**
	 * Check if string contains JSON and parse it
	 * @param string $data
	 * @return mixed
	 */
	public static function parsePotentialJson($data) {
		if (is_array($data) || is_numeric($data)) {
			return $data;
		}
		if (in_array(substr($data, 0, 2), ['{"', '[{', '[]'])) {
			return json_decode($data, true);
		}
		return $data;
	}

	/**
	 * Encode data as JSON if it is an array
	 * @param mixed $data
	 * @return string
	 */
	public static function encodePotentialJson($data) {
		if (is_array($data)) {
			return json_encode($data, JSON_UNESCAPED_UNICODE);
		}
		return $data;
	}

	/**
	 * Flatten language array to single values
	 * @param mixed $value
	 * @param string $attributeName
	 * @param string $defaultLanguage
	 * @return array
	 */
	public static function flattenlanguages($value, $attributeName, $defaultLanguage = 'de') {
		$data = [];
		$value = static::parsePotentialJson($value);
		if (!is_array($value)) {
			$data[$attributeName] = $value;
		} else {
			foreach ($value as $language => $v) {
				$data[$attributeName . '_' . $language] = $v;
			}
			$data[$attributeName] = $data[$attributeName . '_' . $defaultLanguage];
		}
		return $data;
	}

	/**
	 * Merge two arrays, preserving null values and parsing JSON
	 * @param array $old
	 * @param array $new
	 * @param bool $parseJson
	 * @return array
	 * @example
	 * $old = ['a' => 1, 'b' => ['c' => 2]];
	 * $new = ['a' => 2, 'b' => ['d' => 3]];
	 * $merged = ArrayHelper::mergeValues($old, $new);
	 * // ['a' => 2, 'b' => ['c' => 2, 'd' => 3]]
	 */
	public static function mergeValues(array $old, array $new, $parseJson = true) {
		foreach ($new as $k => $v) {
			if ($v === null) {
				continue;
			}
			if ($parseJson) {
				$v = static::parsePotentialJson($v);
			}
			if (!is_array($v)) {
				$old[$k] = $v;
				continue;
			}
			if (is_array($old[$k])) {
				$old[$k] = array_merge($old[$k], $v);
			}
		}
		return $old;
	}

	/**
	 * Remove duplicates from an array by key
	 * @param array $array
	 * @param string $key
	 * @return array
	 * @example
	 * $array = [['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B'], ['id' => 1, 'name' => 'C']];
	 * $array = ArrayHelper::removeDuplicates($array, 'id');
	 * // [['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B']]
	 */
	public static function removeDuplicates($array, $key) {
		$list = [];
		$keys = [];
		foreach ($array as $item) {
			if (!isset($keys[$item[$key]])) {
				$list[] = $item;
				$keys[$item[$key]] = true;
			}
		}
		return $list;
	}

}
