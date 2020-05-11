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
		return $ip . $port;
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
}