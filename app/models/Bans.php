<?php 
namespace app\models;

// require_once 'app/models/Servers.php';

// UnitPay
require_once 'app/lib/unitpay/UnitPay.php';

use app\core\Model;
use app\core\View;
use app\core\Config;
use app\lib\DB;

use app\models\Servers;
use app\lib\UnitPay; // unitpay

use PDO;
use Exception;

class Bans extends Model
{
	public function __construct()
	{
		parent::__construct();
		DB::exec('SET NAMES ' . Config::get('BANS')['charset']);
	}

	public function getAllServers()
	{
		DB::exec('SET NAMES utf8');
		$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_serverinfo` ORDER BY `id`')->fetchAll();
		return $sql;
	}

	public function bansExpiredCalc($created, $expired, $length, $bool = false)
	{
		// #00ad17 - green // #c50000 - red
		if ( $length == -1 ) {
				// return '<span style="color: #00ad17;">Разбанен</span>';
				return $result = ($bool === true) ? true : '<span style="color: #00ad17;">Разбанен</span>';
			} elseif ( $expired == 1 ) {
				// return '<span style="color: #00ad17;">' . $length . ' мин.</span>';
				return $result = ($bool === true) ? true : '<span style="color: #00ad17;">' . $length . ' мин.</span>';
			} elseif ( $length == 0 ) {
				// return '<span style="color: #c50000;">Бессрочно</span>';
				return $result = ($bool === true) ? false : '<span style="color: #c50000;">Бессрочно</span>';
			} elseif ( ($created + $length * 60) < $this->time ) {
				// return '<span style="color: #00ad17;">' . $length . ' мин.</span>';
				return $result = ($bool === true) ? true : '<span style="color: #00ad17;">' . $length . ' мин.</span>';
			} else {
				// return $row['ban_length'] . ' мин.';
				return $result = ($bool === true) ? false : $length . ' мин.';
			}
	}

	public function checkUserIP($ip)
	{
		$sql = DB::run("SELECT `bid` FROM `{$this->DB['prefix']}_bans` WHERE `player_ip` = ? AND (`ban_length` = 0 OR `ban_created` + (`ban_length` * 60) >= UNIX_TIMESTAMP())", [ $ip ])->fetch(PDO::FETCH_ASSOC);

		if ( $sql ) return [ 'exist' => true, 'bid' => $sql['bid'] ];
		return ['exist' => false];
	}

	public function getAllBans()
	{
		$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_bans` ORDER BY `bid` DESC LIMIT 20')->fetchAll();
		return $sql;
	}

	public function getDataBan($ban_id)
	{
		$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_bans` WHERE `bid` = ?', [ $ban_id ])->fetch(PDO::FETCH_ASSOC);

		if ( !$sql ) View::errorCode(404);

		return $sql;
	}

	public function searchBan($value)
	{
		$sql = DB::run('SELECT * FROM '.$this->DB['prefix'].'_bans WHERE `player_nick` LIKE ? OR `player_id` LIKE ? ORDER BY `bid`', 
			[ $value, $value ])->fetch(PDO::FETCH_ASSOC);

		if ( !$sql ){
			$this->error = 'Ничего не нашлось';
			return false;
		}

		return $sql;
	}

	public function unbanPlayer($ban_id)
	{
		try {
			DB::run('UPDATE `'.$this->DB['prefix'].'_bans` SET `ban_length` = -1 WHERE `bid` = ?', [ $ban_id ]);
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
		return true;
	}

	public function goPayUnban($post)
	{
		$ban_id		= $post['bid'];
		$pay_id		= $ban_id; // pay id = ban id
		$shop		= $post['shop'];
		$amount		= Config::get('BANS')['price'];
		$desc 		= 'Unban player ID:' . $pay_id;

		switch ($shop) {
			case 'freekassa':
				$sign = md5($this->FK['merchant_id'].':'.$amount.':'.$this->FK['secret_word1'].':'.$pay_id);
				$url = $this->FK['url'] . '?s=' . $sign . '&o=' . $pay_id . '&m=' . $this->FK['merchant_id'] . '&oa=' . $amount . '&us_core_id=unban';
				return $url;
			break;

			case 'robokassa':
				$mrh_login 		= $this->RK['shop_id'];
				$mrh_pass1		= $this->RK['pass1'];
				$test = ($this->RK['test'] == 1) ? '&IsTest=1' : '';
				$url = $this->RK['url'];

				$sign = md5("$mrh_login:$amount:$pay_id:$mrh_pass1:shp_core_id=unban");
				$url = "$url?MrchLogin=$mrh_login&OutSum=$amount&InvId=$pay_id&SignatureValue=$sign&Culture=ru&Encoding=utf-8&shp_core_id=unban$test";
				return $url;
			break;

			case 'unitpay':
				$unitPay = new UnitPay($this->UP['domain'], $this->UP['secretKey']);
				$url = $unitPay->form($this->UP['publicId'], $amount, $pay_id . '.0', $desc, $this->UP['currency']);
				return $url;
			break;

			default:
				die('models / bans / goPayUnban: shop error');
			break;
		}
	}

	/*
		PAGINATION
	*/
	public function sqlRequest($get, $page, $perPage)
	{
		$start = ( $page - 1 ) * $perPage;
		$orderSQL = 'ORDER BY `bid` DESC';
		
		$search = ( isset($get['search']) ) ? '%' . $get['search'] . '%' : false;

		if ( isset($get['server']) ) {
			$SERVERS = new Servers;
			$serverID = (int)$get['server'];
			$serverIP = $SERVERS->getServerIpById($serverID);
		}

		if ( isset($get['server']) && $search === false ) { // только сервер
			$querySQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_bans` 
				WHERE `server_ip` = ? $orderSQL LIMIT $start, $perPage
			", [ $serverIP ]);
			
			$totalSQL = DB::run("SELECT * FROM `{$this->DB['prefix']}_bans` WHERE `server_ip` = ? $orderSQL", [ $serverIP ]);
			$a = 1;
		}

		if ( !isset($get['server']) && $search !== false ) { // только поиск
			$querySQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_bans` 
				WHERE `player_ip` LIKE ? 
				OR`player_id` LIKE ? 
				OR `player_nick` LIKE ? $orderSQL LIMIT $start, $perPage
			", [ $search, $search, $search ]);
			
			$totalSQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_bans` 
				WHERE `player_ip` LIKE ? 
				OR`player_id` LIKE ? 
				OR `player_nick` LIKE ? $orderSQL
			", [ $search, $search, $search ]);
			$a = 2;
		}

		if ( isset($get['server']) && $search !== false ) { // сервер и поиск
			$querySQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_bans` 
				WHERE `server_ip` = ? AND (`player_ip` LIKE ? OR `player_id` LIKE ? OR `player_nick` LIKE ?) $orderSQL LIMIT $start, $perPage
			", [ $serverIP, $search, $search, $search ]);
			
			$totalSQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_bans` 
				WHERE `server_ip` = ? AND (`player_ip` LIKE ? OR `player_id` LIKE ? OR `player_nick` LIKE ?) $orderSQL
			", [ $serverIP, $search, $search, $search ]);
			$a = 3;
		}

		if ( !isset($querySQL) ) {
			$querySQL = DB::run("SELECT * FROM `{$this->DB['prefix']}_bans` $orderSQL LIMIT $start, $perPage");
			$totalSQL = DB::run("SELECT * FROM `{$this->DB['prefix']}_bans` $orderSQL");
			$a = 4;
		}
		// var_dump($a);

		return ['sql' => $querySQL->fetchAll(), 'total' => $totalSQL->rowCount(), 'start' => $start];
	}
}