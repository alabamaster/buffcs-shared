<?php 
namespace app\core;

use app\core\View;
use app\models\Main;
use app\lib\DB;
use app\core\Config;

abstract class Controller
{
	public $route;
	public $view;
	public $acl;
	public $time;
	public $SITE_NAME;
	public $SITE_URL;
	public $SITE_STYLE;

	public function __construct($route)
	{
		$this->route = $route;

		if (!$this->checkAcl()) {
			View::errorCode(403);
		}

		$this->view = new View($route);

		// загрузка моделей из контроллера
		$this->model 		= $this->loadModel($route['controller']);
		// $this->modal 		= new Config;
		$this->time 		= time();
		$this->SITE_NAME 	= Config::get('NAME');
		$this->SITE_URL 	= Config::get('SITEURL');
		$this->SITE_STYLE 	= Config::get('STYLE');
	}

	public function loadModel($name)
	{
		$path = 'app\models\\' . ucfirst($name);

		if ( class_exists($path) ) 
		{
			return new $path;
		} 
	}

	public function checkAcl() {
		$this->acl = require 'app/acl/'.$this->route['controller'].'.php';
		if ($this->isAcl('all')) {
			return true;
		}
		elseif (isset($_SESSION['account']['id']) and $this->isAcl('authorize')) {
			return true;
		}
		elseif (!isset($_SESSION['account']['id']) and $this->isAcl('guest')) {
			return true;
		}
		elseif (isset($_SESSION['admin']) and $this->isAcl('admin')) {
			return true;
		}
		return false;
	}
	
	public function isAcl($key) {
		return in_array($this->route['action'], $this->acl[$key]);
	}
}