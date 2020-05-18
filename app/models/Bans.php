<?php 
namespace app\models;

require_once 'app/models/Servers.php';

use app\core\Model;
use app\core\View;
use app\core\Config;
use app\lib\DB;
use app\models\Servers;
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
				return $result = ($bool == true) ? true : '<span style="color: #00ad17;">Разбанен</span>';
			} elseif ( $expired == 1 ) {
				// return '<span style="color: #00ad17;">' . $length . ' мин.</span>';
				return $result = ($bool == true) ? true : '<span style="color: #00ad17;">' . $length . ' мин.</span>';
			} elseif ( $length == 0 ) {
				// return '<span style="color: #c50000;">Бессрочно</span>';
				return $result = ($bool == true) ? false : '<span style="color: #c50000;">Бессрочно</span>';
			} elseif ( ($created + $length * 60) < $this->time ) {
				// return '<span style="color: #00ad17;">' . $length . ' мин.</span>';
				return $result = ($bool == true) ? true : '<span style="color: #00ad17;">' . $length . ' мин.</span>';
			} else {
				// return $row['ban_length'] . ' мин.';
				return $result = ($bool == true) ? false : $length . ' мин.';
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
		$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_bans` ORDER BY `bid` DESC')->fetchAll();
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

			default:
				die('models / bans / goPayUnban: shop error');
			break;
		}
	}

	public function bansCount()
	{
		return DB::run('SELECT COUNT(bid) FROM `'.$this->DB['prefix'].'_bans`')->fetchColumn();
	}

	public function listBans($route)
	{
		// $pagination = new Pagination($this->route, $this->model->bansCount(), 20);
		// 20 должны быть равные
		$max = 15;

		if ( empty($route['page']) ) {
			$start = 1;
		} else {
			$page 	= explode('/', $route['page']);
			$page 	= (int)$page[1];
			$start 	= (($page - 1) * $max);
		}

		try {
			$sql = DB::run("SELECT * FROM `{$this->DB['prefix']}_bans` ORDER BY `bid` DESC LIMIT {$start}, {$max}")->fetchAll();
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
		return $sql;
	}
}