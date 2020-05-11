<?php 

namespace app\core;

class Config
{
	public static $config = [];

	private static $prefix = [
		'default'	=> 'main',
		// 'default'	=> 'icons',
	];

	public static function get($key)
	{
		return self::_get($key, self::$prefix['default']);
	}

    public static function set($key, $value)
    {
		self::_set($key, $value, self::$prefix['default']);
	}

	public static function getJsConfig($key = "")
	{
		return self::_get($key, self::$prefix['js']);
	}

	public static function setJsConfig($key, $value)
	{
		self::_set($key, $value, self::$prefix['js']);
	}

	private static function _get($key, $source)
	{

		if (!isset(self::$config[$source])) 
		{

			$config_file = 'app/configs/' . $source . '.php';

			if (!file_exists($config_file)) {
				throw new Exception("Configuration file " . $source . " doesn't exist");
			}

			self::$config[$source] = require $config_file . "";
		}

		if(empty($key)){
			return self::$config[$source];
		} else if(isset(self::$config[$source][$key])){
			return self::$config[$source][$key];
		}

		return null;
	}

	private static function _set($key, $value, $source)
	{

		// load configurations if not already loaded
		if (!isset(self::$config[$source])) {
			self::_get($key, $source);
		}

		if($key && $source){
			self::$config[$source][$key] = $value;
		}
	}
}