<?php

namespace Deepcommerce\Phpshop\Core;

class Request {

	private $method;
	private $scriptName;
    private $path;
	private $uri;
	private $isPathHandled = false;

	/**
	 * Constructor
	 * @param string $path
	 * @param string $scriptName
	 * @param string $method
	 */
    public function __construct($path, $scriptName, $method) {
		$this->path       = $path;
		$this->method     = $method;
		$this->scriptName = $scriptName;

		$basePath  = preg_replace('/\/\w+\.php$/', '', $scriptName);
		$this->uri = str_replace($basePath, '', $path);
		$this->uri = '/' . ltrim($this->uri, '/');
	}

	/**
	 * extract the first part of the uri
	 * @return string
	 */
	public function pop() {
		return $this->uri = '/' . trim(preg_replace('/^\/\w+/', '', $this->uri), '/');
	}

	/**
	 * Get the request body or throw an exception
	 * @return string	 
	 */
	public function getBodyOrFail() {
		$body = $this->body();
		if (empty($body)) {
			throw new \Exception('Missing request body');
		}
		return $body;
	}

	/**
	 * Get the request body and decode JSON
	 * @return string	 
	 */
	public function body() {
		return json_decode(file_get_contents('php://input'), true);
	}

	/**
	 * Validate the value against the types
	 */
	public function validate($value, $types) {
		$validationRegex = [
			'url' => '^[a-zA-Z0-9\-_]*$',
			'required' => '.+',
		];
		foreach ($types as $type) {
			if (empty($validationRegex[$type])) {
				return false;
			}
			if (!preg_match('/' . $validationRegex[$type] . '/', $value)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Replace the uri with a new value
	 * @param string $search
	 * @param string $replace
	 */
	public function replaceUri($search, $replace) {
		$this->uri = preg_replace("#$search#", $replace, $this->uri);
	}

	/**
	 * Match the Request URI with the given path, without checking the request method
	 */
	public function match ($path, $callback) {
		if (preg_match("#^$path$#", $this->uri, $var)) {
			array_shift($var);
			call_user_func_array($callback, $var);
		}
	}

	/**
	 * Matching a GET Request to a URI pattern
	 * @param string $path
	 * @param callable $callback
	 */
	public function get ($path, $callback) {
		if ($this->method == 'GET' && preg_match("|^$path$|", $this->uri, $var)) {
			array_shift($var);
			call_user_func_array($callback, $var);
			$this->isPathHandled = true;
		}
	}

	/**
	 * Matching a POST Request to a URI pattern
	 * @param string $path
	 * @param callable $callback
	 */ 
	public function post ($path, $callback) {
		if ($this->method == 'POST' && preg_match("|^$path$|", $this->uri, $var)) {
			$var[0] = empty($_POST) ? $this->body() : $_POST;
			call_user_func_array($callback, $var);
			$this->isPathHandled = true;
		}	
	}	

	/**
	 * Matching a PUT Request to a URI pattern
	 * @param string $path
	 * @param callable $callback
	 */
	public function put ($path, $callback) {
		if ($this->method == 'PUT' && preg_match("|^$path$|", $this->uri, $var)) {
			$var[0] = $this->body();
			call_user_func_array($callback, $var);
			$this->isPathHandled = true;
		}
	}

	/**
	 * Matching a PATCH Request to a URI pattern
	 * @param string $path
	 * @param callable $callback
	 */
	public function patch ($path, $callback) {
		if ($this->method == 'PATCH' && preg_match("|^$path$|", $this->uri, $var)) {
			$var[0] = $this->body();
			call_user_func_array($callback, $var);
			$this->isPathHandled = true;
		}
	}

	/**
	 * Matching a DELETE Request to a URI pattern
	 * @param string $path
	 * @param callable $callback
	 */
	public function delete ($path, $callback) {
		if ($this->method == 'DELETE' && preg_match("|^$path$|", $this->uri, $var)) {
			array_shift($var);
			call_user_func_array($callback, $var);
			$this->isPathHandled = true;
		}
	}

	/**
	 * Default callback if no path is matched (404 Page)
	 * @param callable $callback
	 */
	public function default($callback) {
		if (!$this->isPathHandled) {
			call_user_func($callback, $this->uri);
		}
	}

}
