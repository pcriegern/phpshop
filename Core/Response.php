<?php

namespace deepcommerce\phpshop\Core;

use deepcommerce\phpshop\Helper\ProductHelper;
use deepcommerce\phpshop\Helper\StringHelper;
use deepcommerce\phpshop\Helper\HtmlHelper;
use deepcommerce\phpshop\Core\Localize;
use \Twig\Loader\FilesystemLoader;
use \Twig\Environment;
use \Twig\TwigFilter;
use \Twig\TwigFunction;

class Response {

    /**
     * @var \deepcommerce\Core\App
     */
    private $app;

	/**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @var string Template Directory
     */
    private $templateDir = __DIR__ . '/../../../../templates';

    /**
     * @var array Parser Config
     */
    private $parserConfig = [
            'cache'      => __DIR__ . '/../../../templates/cache',
            'autoescape' => false,
            'debug'      => false,
    ];

    /**
     * Constructor
     */
    public function __construct($app) {
        $this->app = $app;
	}

    /**
     * Set Template Directory
     * @param string $dir
     */
    public function setTemplateDir($dir) {
        $this->templateDir = $dir;
    }

    /**
     * Set Parser Config
     * @param array $config
     */
    public function setParserConfig($config) {
        $this->parserConfig = $config;
    }

    /**
     * Send JSON Response
     */
	public function json($data) {
		header('Content-Type: application/json');
		print json_encode($data);
	}

    /**
     * Send JSON Success Response
     */
	public function jsonSuccess($message = '', $data = []) {
        $this->json(array_merge(['success' => true, 'message' => $message], $data));
    }

    /**
     * Send JSON Error Response     
     */
	public function jsonError($message, $code = 500) {
		$this->json(['error' => true, 'code' => $code, 'message' => $message]);
	}

    /**
     * Twig Factory
     */
    public function twig() {
        if (empty($this->twig)) {

            $loader = new \Twig\Loader\FilesystemLoader($this->templateDir);
            $this->twig = new \Twig\Environment($loader, $this->parserConfig);

            // Add Localize Filters
            $this->twig->addFilter(new TwigFilter('translate', function ($string) {
                return Localize::translate($string);
            }));
            $this->twig->addFilter(new TwigFilter('localize', function ($label, $default = null) {
                return Localize::text($label, $default);
            }));
            $this->twig->addFilter(new TwigFilter('date', function ($date) {
                return Localize::date($date);
            }));
            $this->twig->addFilter(new TwigFilter('price', function ($price) {
                return Localize::price($price);
            }));
            $this->twig->addFilter(new TwigFilter('absoluteimagepath', function ($price) {
                return Localize::absoluteImagePath($price);
            }));
    
    
            $this->twig->addFilter(new TwigFilter('json', function ($data) {
                return json_encode($data);
            }));
            $this->twig->addFilter(new TwigFilter('productprice', function ($product) {
                return ProductHelper::formattedPrice($product);
            }));
            $this->twig->addFilter(new TwigFilter('oldproductprice', function ($product) {
                return ProductHelper::formattedOldPrice($product);
            }));
            $this->twig->addFilter(new TwigFilter('productdiscount', function ($product) {
                return ProductHelper::discount($product);
            }));
            $this->twig->addFilter(new TwigFilter('productpricecolor', function ($product) {
                return ProductHelper::priceColorClass($product);
            }));
    
            $this->twig->addFilter(new TwigFilter('ratingstars', function ($stars, $size = 5) {
                return HtmlHelper::ratingStars($stars, $size);
            }));
            $this->twig->addFilter(new TwigFilter('tagname', function ($label) {
                return StringHelper::tagname($label);
            }));
            $this->twig->addFilter(new TwigFilter('count', function ($array) {
                return count($array);
            }));
            $this->twig->addFilter(new TwigFilter('sprintf', function ($text, $variable) {
                return sprintf($text, $variable);
            }));
    
            // Custom Functions
            $this->twig->addFunction(new TwigFunction('hasoldprice', function ($product) {
                return ProductHelper::hasOldPrice($product);
            }));
            $this->twig->addFunction(new TwigFunction('count', function ($array) {
                return count($array);
            }));

            // Add Custom Extensions
            $this->app->addCustomParserExtensions($this->twig);
        }

        return $this->twig;
    }

    /**
     * Render a Twig Template     
     */
	public function render($template, array $data = []) {
		$page = array_merge($this->app->config(), $data);
		print $this->twig()->render($template, $page);
	}

    /**
     * Redirect to a URL
     * @param string $url
     * @param string $message
     * @param string $error
     */
	public function redirect($url, $message = '', $error = '') {
		$concat = strpos($url, '?') === false ? '?' : '&';
		if ($message) {
			$url .= $concat . 'message=' . urlencode($message);
		} else if ($error) {
			$url .= $concat . 'error=' . urlencode($error);
		}
		header("Location: $url");
	}

    /**
     * Handle Error
     * @param int $code
     * @param string $message
     */
    public function error($message, $code = 500) {
        http_response_code($code);
        print $message;exit;
        $this->render('error.html', ['message' => $message]);
    }

    /**
     * Handle API Error
     * @param array $apiResponse
     * @param string $redirectUrl
     */
	public function handleApiError($apiResponse, $redirectUrl) {
		$errorMessage = 'API Error';
		if (isset($apiResponse['message'])) {
			$errorMessage .= ': ' . $apiResponse['message'];
		}
		$this->redirect($redirectUrl, '', $errorMessage);
		exit;
	}
	
	/**
	 * Store Customer Session in Browser (Cookie and LocalStore), then redirect
     * @param array $customerObject
     * @param string $redirectLocation
	 */
    public function storeCustomerSession($customerObject, $redirectLocation) {

		// Store Customer Token in Cookie
		$customerToken = $customerObject['accessToken'];
        setcookie('customerToken', $customerToken, 0, '/');

		// Copy to Local Store
        $pageData = [
            'customerObject' => $customerObject,
            'redirect' => $redirectLocation,
        ];

		// Customer Account created
        $this->render('js.html', $pageData);
    }

}
