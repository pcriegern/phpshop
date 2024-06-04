<?php

namespace deepcommerce\phpshop\Core;

use \deepcommerce\phpshop\Core\Request;
use \deepcommerce\phpshop\Core\Response;
use \deepcommerce\phpshop\Core\Localize;
use \deepcommerce\phpshop\Core\Api;

class App {

    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var \deepcommerce\phpshop\Core\Request
     */
    protected $request;
    /**
     * @var \deepcommerce\phpshop\Core\Response
     */
    protected $response;

    /**
     * Constructor
     */
    public function __construct($config = []) {
        $this->config = $config;
        Localize::init($config);
        Api::setGateway($config['api_gateway']);
        Api::setToken($config['api_token']);
    }

    /**
     * Request Factory
     * @return \deepcommerce\phpshop\Core\Request
     */
    public function request() {
        if (empty($this->request)) {
            $this->request = new Request(@$_SERVER['REDIRECT_URL'] ?: '', @$_SERVER['SCRIPT_NAME'] ?: '', @$_SERVER['REQUEST_METHOD'] ?: 'GET');
        }
        return $this->request;
    }

    /**
     * Response Factory
     * @return \deepcommerce\phpshop\Core\Response
     */
    public function response() {
        if (empty($this->response)) {
            $this->response = new Response($this);
            if (isset($this->config['template_dir'])) {
                $this->response->setTemplateDir($this->config['template_dir']);
            }
            if (isset($this->config['template_parser'])) {
                $this->response->setParserConfig($this->config['template_parser']);
            }
        }
        return $this->response;
    }

    /**
     * Config Getter
     */
    public function config() {
        return $this->config;
    }

    /**
     * Set a config value
     */
	public function setConfig($key, $value) {
		$this->config[$key] = $value;
	}

    /**
     * Get a config value
     */
    public function getConfig($key) {
        return $this->config[$key] ?? null;
    }

    /**
     * Set Language
     */
    public function setLanguage($language) {
        Localize::setLanguage($language);
        $this->setConfig('language',  Localize::getLanguageConfig($language));
    }

    /**
     * Get Carft Token from Cookie
     */
    public function getCartToken() {
        if (empty($_COOKIE['cartToken'])) {
            return false;
        }
        $token = $_COOKIE['cartToken'];
        if (!preg_match('/^[\w\-]+$/', $token)) {
            return false;
        }
        return $token;
    }
    
    /**
     * Get Customer Token from Cookie
     */
    function getCustomerToken() {
        if (empty($_COOKIE['customerToken'])) {
            return false;
        }
        $token = $_COOKIE['customerToken'];
        if (!preg_match('/^\w+$/', $token)) {
            return false;
        }
        return $token;
    }

    /**
     * Add Custom Twig Filters and Functions
     * @param \Twig\Environment $twig
     */
    public function addCustomParserExtensions($twig) {}

}