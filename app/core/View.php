<?php 
namespace app\core;

use app\core\Config;

class View
{
	public $path;
	public $route;
	// public $layout = 'test';
	public $layout = 'default';
	public $SITE_URL;
	public $SITE_STYLE;
	public $SITE_NAME;

	public function __construct($route)
	{
		$this->route 		= $route;
		$this->path 		= $route['controller'] . '/' . $route['action'];
		$this->SITE_URL 	= Config::get('SITEURL');
		$this->SITE_STYLE 	= Config::get('STYLE');
		$this->SITE_NAME 	= Config::get('NAME');
	}

	public function render($title, $vars = []) {
		extract($vars);
		$path = 'app/views/' . $this->path . '.php';
		if ( file_exists($path) ) 
		{
			ob_start();
			require_once $path;
			$content = ob_get_clean();
			require_once 'app/views/layouts/'.$this->layout.'.php';
		}
	}

	public static function errorCode($code)
	{
		http_response_code($code);
		require 'app/views/errors/' . $code . '.php';
		exit();
	}

	public function redirect($url)
	{
		header('Location:' . $url);
		exit();
	}

	public function reload()
	{
		exit(json_encode(['reload' => true]));
	}

	public function message($status, $message) 
	{
		exit(json_encode(['status' => $status, 'message' => $message]));
	}

	public function location($url) 
	{
		exit(json_encode(['url' => $url]));
	}

	public function adminAmxadmins($data) 
	{
		exit(json_encode(['adminAmxadmins' => true, 'data' => $data]));
	}
}