<?php 
namespace app\core;

use app\core\View;
use app\core\Config;

class Router
{
	protected $routes = [];
	protected $params = [];

	public function __construct()
	{
		$arr = require 'app/configs/routes.php';

		foreach ($arr as $key => $value) {
			$this->add($key, $value);
		}
	}

	public function add($route, $params)
	{
		// $route = preg_replace('/{([a-z]+):([^\}]+)}/', '(?P<\1>\2)', $route);
		// $route = '#^'.$route.'$#';
		// $this->routes[$route] = $params;
		$patterns = ['/{([a-z]+):([^\}]+)}/', '/{([a-z]+)([^\}]+)}/'];
		$route = preg_replace($patterns, '(?P<$1>$2)', $route);
		$route = '#^'.$route.'$#';
		$this->routes[$route] = $params;
	}

	public function match() 
	{
		$url = trim($_SERVER['REQUEST_URI'], '/');
		foreach ($this->routes as $route => $params)
		{
			if (preg_match($route, $url, $matches))
			{
				foreach ($matches as $key => $match)
				{
					if (is_string($key))
					{
						if (is_numeric($match))
						{
							$match = (int) $match;
						}
						$params[$key] = $match;
					}
				}
				$this->params = $params;
				return true;
			}
		}
		return false;
	}

	public function run()
	{
		if ( $this->match() ) {
			$path = 'app\controllers\\' . ucfirst($this->params['controller']) . 'Controller';

			if ( class_exists($path) ) 
			{
				$action = $this->params['action'] . 'Action';

				if ( method_exists($path, $action) ) 
				{
					$controller = new $path($this->params);
					$controller->$action();
				} else {
					View::errorCode(404);
					// echo 'action ' .$action. ' not found';
				}
			} else {
				View::errorCode(404);
				//echo 'class '.$path.' not found';
			}
		} else {
			if( $_SERVER['REQUEST_URI'] == '/bans' ) {
				header('Location: ' . Config::get('SITEURL') . 'bans?page=1');
				exit();
			}
			View::errorCode(404);
			//echo 'route not found';
		}
	}
}