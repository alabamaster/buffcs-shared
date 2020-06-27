<?php 
namespace app\models;

use app\core\Model;
use app\core\Config;

use app\lib\DB;
use PDO;

class Servers extends Model
{
	
	public function __construct()
	{
		parent::__construct();
	}

	// return string
	public function serverIp($ip)
	{
		$ip = explode(':', $ip);
		$ip = $ip[0];
		$port = $ip[1];
		
		if ( $port == '27015' ) return $ip;
		return $ip . ':' . $port;
	}


	public function getCountAllServers()
	{
		$sql = DB::run("SELECT COUNT(id) FROM {$this->DB['prefix']}_serverinfo")->fetchColumn();
		return $sql;
	}

	// return array
	public function getAllServers()
	{
		$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_serverinfo` ORDER BY `id`')->fetchAll();
		return $sql;
	}

	// return array
	public function getAllPrivileges($active = null)
	{
		if ( $active === null ) {
			$sql = DB::run('SELECT * FROM `ez_privileges` ORDER BY `id`')->fetchAll();
			return $sql;
		}
		if ( $active == 1 ) {
			$sql = DB::run('SELECT * FROM `ez_privileges` WHERE `active` = 1 ORDER BY `id`')->fetchAll();
			return $sql;
		}
	}

	public function getServerNameById($sid)
	{
		$sql = DB::run('SELECT `hostname` FROM `'.$this->DB['prefix'].'_serverinfo` WHERE `id` = ?', [ $sid ])->fetch(PDO::FETCH_ASSOC);
		if(empty($sql)) return 'hostname not found';
		return $sql['hostname'];
	}

	public function getServerNameByIp($ip)
	{
		$sql = DB::run('SELECT `hostname` FROM `'.$this->DB['prefix'].'_serverinfo` WHERE `address` = ?', [ $ip ])->fetch(PDO::FETCH_ASSOC);
		if(empty($sql)) return 'hostname not found';
		return $sql['hostname'];
	}

	public function getPrivilegeNameById($pid)
	{
		$sql = DB::run('SELECT `name` FROM `ez_privileges` WHERE `id` = ?', [ $pid ])->fetch(PDO::FETCH_ASSOC);
		if(empty($sql)) return 'privilege not found';
		return $sql['name'];
	}

	public function getServerIpById($sid)
	{
		$sql = DB::run("SELECT `address` FROM `{$this->DB['prefix']}_serverinfo` WHERE `id` = ?", [ $sid ])->fetch(PDO::FETCH_ASSOC);
		if(empty($sql)) return 'server where id = '.$sid.'not found';
		return $sql['address'];
	}

	public function getGeoIP($user_ip)
	{
		if ( Config::get('GEO_IP') == 0 ) {
			$country_code = mb_strtolower(geoip_country_code_by_name($user_ip));
			$country_name = geoip_country_name_by_name($user_ip);
			return $data = ['code' => $country_code, 'name' => $country_name];
		} else {
			$json = file_get_contents('https://freegeoip.app/json/' . $user_ip);
			$array = json_decode($json, true);
			$country_code = mb_strtolower($array['country_code']);
			$country_name = mb_strtolower($array['country_name']);
			return $data = ['code' => $country_code, 'name' => $country_name];
		}
	}

	// only $_SERVER['QUERY_STRING']
	// return server id or false
	public function filterServer($queryStr)
	{
		$filterUrl = explode('=', $queryStr);

		if ( !isset($filterUrl[0]) ) return false;

		$serverId = ( isset($filterUrl[0]) && $filterUrl[0] == 'server_id' ) ? $filterUrl[1] : false;

		if ( $serverId === false ) {
			$this->error = 'Bad request / filter server error';
			return false;
		}

		return (int)$serverId;
	}
}