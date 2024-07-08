<?php

namespace Deepcommerce\Phpshop\Core;

use Deepcommerce\Phpshop\Core\Cache;

class Api {

	/**
	 * @var string
	 */
	private static $gateway  = '';
	/**
	 * @var string
	 */
	private static $apitoken = '';

	/**
	 * Set API Token
	 * @param string $token
	 */
	public static function setToken($token) {
		static::$apitoken = $token;
	}

	/**
	 * Set API Gateway
	 * @param string $gateway
	 */
	public static function setGateway($gateway) {
		static::$gateway = $gateway;
	}

	/**
	 * Make a request to the API
	 * @param string $method
	 * @param string $uri
	 * @param mixed $data
	 * @return mixed
	 */
	public static function request($method, $uri, $data = false) {
		$url = static::$gateway . '/' . ltrim($uri, '/');
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		$headers = ['Content-Type: application/json'];
		if ($data) {
			$json = json_encode($data);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
			$headers[] = 'Content-Length: ' . strlen($json);
		}
		if (static::$apitoken) {
			$headers[] = 'Authorization: Bearer ' . static::$apitoken;
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($curl);
		curl_close($curl);
		$json = json_decode($result, true);
		if ($json === null && strlen($result) > 0) {
			return ['error' => true, 'message' => 'Invalid JSON response', 'response' => $result];
		}
		return $json;
	}

	/**
	 * Get data from the API by GET Request
	 * @param string $path
	 * @param bool $useCache
	 * @return mixed
	 */
	public static function get ($path, $useCache = false) {
		if ($useCache) {
			$cache = new Cache($path);
			if ($cache->hit()) {
				return $cache->get();
			}
		}
		$data = static::request('GET', $path);
		if ($useCache) {
			$cache->set($data);
		}
		return $data;
	}

	/**
	 * Post data to the API
	 * @param string $path
	 * @param mixed $data
	 * @return mixed
	 */
	public static function post ($path, $data) {
		return static::request('POST', $path, $data);
	}

	/**
	 * Patch data to the API
	 * @param string $path
	 * @param mixed $data
	 * @return mixed
	 */
	public static function patch ($path, $data) {
		return static::request('PATCH', $path, $data);
	}

	/**
	 * Put data to the API
	 * @param string $path
	 * @param mixed $data
	 * @return mixed
	 */
	public static function put ($path, $data) {
		return static::request('PUT', $path, $data);
	}

	/**
	 * Delete data from the API
	 * @param string $path
	 * @return mixed
	 */
	public static function delete ($path) {
		return static::request('DELETE', $path);
	}

}
