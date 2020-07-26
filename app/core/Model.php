<?php 
namespace app\core;

use app\core\Config;
use app\lib\DB;
use PDO;

abstract class Model
{
	public $DB = [];
	public $time;

	public $DISCOUNT; // discount
	public $FK; // freekassa
	public $RK; // robokassa
	public $UP; // unitpay
	public $SITE_URL; // site url

	public function __construct()
	{
		$this->DB 		= require 'app/configs/db.php';
		$this->time 	= time();
		
		$this->DISCOUNT = Config::get('DISC');
		$this->FK 		= Config::get('FK');
		$this->RK 		= Config::get('RK');
		$this->UP 		= Config::get('UP');
		$this->SITE_URL = Config::get('SITEURL');
	}
}