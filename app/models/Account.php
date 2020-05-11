<?php 
namespace app\models;

require_once 'app/models/Sendmailer.php';
require_once 'app/lib/unitpay/UnitPay.php';
require_once 'app/models/Main.php';

use app\core\Model;
use app\core\Config;
use app\lib\DB;
use app\lib\SourceQuery;
use app\lib\UnitPay; // unitpay
use PDO;
use app\models\Sendmailer;
use app\models\Main;
use app\models\Admin;

class Account extends Model
{
	public function generateKey()
	{
		return password_hash('foiy57348y8B' . time() . 'sdo8e3rtg38gt39t987', PASSWORD_DEFAULT);
	}

	public static function expired_time($time)
	{
		$time = (int)$time;
		$time = ( $time == 0 ) ? 'Бессрочно ( ͡ᵔ ͜ʖ ͡ᵔ )' : date('d.m.Y', $time);
		return $time;
	}
	
	public static function discount($cost, $discount)
	{
		$a = $cost / 100 * $discount;
		return $cost - $a;
	}

	public static function timeName($time)
	{
		$disc = Config::get('DISC');
		$time = (int)$time;
		
		if( $time == 0 )
			return 'Навсегда';
		else
			return $time . ' дн.';
	}

	public static function secToStrDate($secs)
	{
		$res = array();

		$res['days'] = floor($secs / 86400);
		$secs = $secs % 86400;

		$res['hours'] = floor($secs / 3600);
		$secs = $secs % 3600;

		$res['minutes'] = floor($secs / 60);
		$res['secs'] = $secs % 60;

		$res['seconds'] = floor($secs / 60);
		$res['secs'] = ($secs / 60);

		// return $res;
		return $res['hours'] . 'ч '  . $res['minutes'] . 'м ' . $res['seconds'] . 'с';
	}

	// $pid - privilege id // $sid - server id
	// return array
	public function getPrivilegeTime($pid, $sid)
	{
		$query = DB::run('SELECT `id`, `price`, `time` FROM `ez_privileges_times` WHERE `pid` = ? AND `sid` = ? ORDER BY `id`', [ $pid, $sid ])->fetchAll();
		return $query;
	}

	public function getServerDataById($sid)
	{
		// require_once '../lib/SourceQuery.php';
		// префикс исправить
		$sql = DB::run('SELECT `id`, `address` FROM `'.$this->DB['prefix'].'_serverinfo` WHERE `id` = ?', [$sid])->fetch(PDO::FETCH_ASSOC);
		list($ip, $port) = explode(":", $sql['address']);

		// ПОФИКСИТЬ ЧАСЫ В СПИСКЕ ИГРОКОВ
		$sq = new SourceQuery($ip, $port);
		$info  = $sq->getInfos();
		$players = $sq->getPlayers();

		if ( !$info ) return false;

		// map images
		$url = "https://image.gametracker.com/images/maps/160x120/cs/" .$info['map']. ".jpg";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, true);   
		curl_setopt($ch, CURLOPT_NOBODY, true);    
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.4");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$map_url = ($httpcode == 200) ? $url : 'https://image.gametracker.com/images/maps/160x120/nomap.jpg';

		$data = [
			'arr_info' 		=> $info,
			'arr_players' 	=> $players,
			'map_url' 		=> $map_url,
		];
		return $data;
	}

	public function checkAuthorization()
	{
		if ( isset($_COOKIE['id']) && isset($_COOKIE['hash']) ) 
		{
			$id 		= (int)$_COOKIE['id'];
			$auth_hash 	= $_COOKIE['hash'];
			$sql = DB::run('SELECT COUNT(*) FROM `'.$this->DB['prefix'].'_admins_servers` WHERE `admin_id` = ? AND `auth_hash` = ?', [$id, $auth_hash])->fetchColumn();

			if ( $sql > 0 || $sql == NULL ) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}

	public function authorizationExit($uid)
	{
		$auth_hash 	= $_COOKIE['hash'];
		$sql = DB::run('SELECT COUNT(*) FROM `'.$this->DB['prefix'].'_admins_servers` WHERE `admin_id` = ? AND `auth_hash` = ?', [$uid, $auth_hash])->fetchColumn();
		
		unset($_COOKIE['id']);
		unset($_COOKIE['hash']);
		setcookie('id', NULL, time() - 3600, "/");
		setcookie('hash', NULL, time() - 3600, "/");

		unset($_SESSION['account']);
		unset($_SESSION['authorization']);

		if ( $sql > 0 ) {
			$auth_hash = $this->generateKey();
			DB::run('UPDATE `'.$this->DB['prefix'].'_admins_servers` SET `auth_hash` = ? WHERE `admin_id` = ?', [ $auth_hash, $uid ]);
			return true;
		}
		return false;
	}

	public function authorization($post)
	{
		$username = trim(htmlspecialchars($post['username']));

		// if ( !preg_match('/^[a-z\d_][\d:.]{2,20}$/i', $post['username']) ) {
		if ( mb_strlen($post['username']) < 3 || mb_strlen($post['username']) > 20 ) {
			$this->error = 'Логин должен быть от 2 до 32 символов';
			return false;
		}

		// if ( !preg_match('/^[a-z\d_]{2,20}$/i', $post['password']) ) {
		if ( mb_strlen($post['password']) < 3 || mb_strlen($post['password']) > 20 ) {
			$this->error = 'Пароль должен быть от 2 до 32 символов';
			return false;
		}

		if ( !isset($post['server']) || $post['server'] == 0 ) {
			$this->error = 'Выберите сервер';
			return false;
		}

		$type = $post['type'];
		$pass = md5($post['password']);

		if ( $type == 'a' ) {
			$getData = DB::run('SELECT * FROM 
				`'.$this->DB['prefix'].'_amxadmins` `t1` JOIN 
				`'.$this->DB['prefix'].'_admins_servers` `t2` WHERE 
				`t1`.`username` = ? AND `t1`.`nickname` = ? AND `t1`.`password` = ? AND `t1`.`id` = `t2`.`admin_id` AND `t2`.`server_id` = ? LIMIT 1
			', [ $username, $username, $pass, $post['server'] ])->fetch(PDO::FETCH_ASSOC);
		} elseif ( $type == 'ac' ) {
			$getData = DB::run('SELECT * FROM 
				`'.$this->DB['prefix'].'_amxadmins` `t1` JOIN 
				`'.$this->DB['prefix'].'_admins_servers` `t2` WHERE 
				`t1`.`username` = ? AND `t1`.`steamid` = ? AND `t1`.`password` = ? AND `t1`.`id` = `t2`.`admin_id` AND `t2`.`server_id` = ? LIMIT 1
			', [ $username, $username, $pass, $post['server'] ])->fetch(PDO::FETCH_ASSOC);
		}

		if ( $getData ) 
		{
			$_SESSION['account'] = $getData;
			$_SESSION['authorization'] = true;

			try {
				$auth_hash = $this->generateKey();
			} catch (Exception $e) {
				echo 'Error: ' . $e->getMessage();
			}
			setcookie('hash', $auth_hash, time() + 60 * 60 * 24 * 7, '/');
			setcookie('id', $getData['id'], time() + 60 * 60 * 24 * 7, '/');

			try {
				DB::run('UPDATE `'.$this->DB['prefix'].'_admins_servers` SET `auth_hash` = ? WHERE `admin_id` = ?', [ $auth_hash, $getData['id'] ]);
			} catch (Exception $e) {
				echo 'Error: ' . $e->getMessage();
			}
			return true;
		} else {
			$this->error = 'Неверный логин или пароль';
			return false;
		}
	}

	public function getUserData($username, $password)
	{
		$query = DB::run('
			SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` `t1` JOIN `'.$this->DB['prefix'].'_admins_servers` `t2` WHERE
			`t1`.`username` = ? AND `t1`.`password` = ? AND `t1`.`id` = `t2`.`admin_id` LIMIT 1', [ $username, $password ])->fetch(PDO::FETCH_ASSOC);
		return $query;
	}

	public function amxxDataServer($sess_acc)
	{
		return $_SESSION['account'];
	}

	// FUNCTIONS FOR CHANGE PRIVILEGES
	public function getInfoCurrentPrivilege($exp, $pid)
	{
		$sql = DB::run('SELECT * FROM `ez_privileges_times` WHERE `pid` = ? AND `time` = ?', [ $pid, 30 ])->fetch(PDO::FETCH_ASSOC);

		if (!$sql) {
			$this->error = 'Ошибка получения информации о привилегии';
			return false;
		}

		$balance 		= $sql['price'];
		$price_one_day 	= round($sql['price'] / $sql['time'], 1);
		$days_left 		= floor(($exp - time()) / 86400);
		$pseudo_balance	= $days_left * $price_one_day;

		$arr = [
			'price' 		=> $balance,
			'price_one_day' => $price_one_day,
			'days_left'		=> $days_left,
			'p_balance'		=> $pseudo_balance,
		];
		return $arr;
	}
	public function changePrivilege($arr, $uid)
	{
		$pri_id = (int)$arr['privilege_id'];
		$exp 	= $arr['daysLeftInTimestamp'];

		try {
			DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `expired` = ?, `tarif_id` = ? WHERE `id` = ?', [ $exp, $pri_id, $uid ]);
			$_SESSION['account']['tarif_id'] = $pri_id;
			$_SESSION['account']['expired'] = $exp;
			return true;
		} catch (Exception $e) {
			$this->error = 'Ошибка, обратитесь к администрации. F: changePrivilege';
			return false;
		}
	}

	public function changeSettings($post, $sess_acc)
	{
		$MAIN = new Main;
		$check_change = false;

		$user = ($post['type'] == 'a') ? $post['nickname'] : $post['steamid'];

		// смена почты
		if ( $post['email'] != $sess_acc['email'] ) 
		{
			if( $post['email'] == '' ) {
				$this->error = 'Вы должны указать свой почтовый адрес!';
				return false;
			}

			$check_email = DB::run('SELECT COUNT(*) FROM `'.$this->DB['prefix'].'_admins_servers` WHERE `email` = ?', [ $post['email'] ])->fetchColumn();
			if ( $check_email > 0 ) {
				$this->error = 'Такой Email уже занят, укажите другой';
				return false;
			}

			DB::run('UPDATE `'.$this->DB['prefix'].'_admins_servers` SET `email` = ? WHERE `admin_id` = ?', [ $post['email'], $sess_acc['id'] ]);
			$_SESSION['account']['email'] = $post['email'];
			$check_change = true;
		}

		// смена пароля
		if ( $post['password'] != '' && md5($post['password']) != $sess_acc['password'] ) {
			DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `password` = ? WHERE `id` = ?', [ md5($post['password']), $sess_acc['id'] ]);
			$_SESSION['account']['password'] = md5($post['password']);
			$check_change = true;
		}

		// смена ВК
		if ( $post['vk'] != '' && $post['vk'] != $sess_acc['vk'] ) 
		{
			$check_vk = DB::run('SELECT COUNT(*) FROM `'.$this->DB['prefix'].'_admins_servers` WHERE `vk` = ?', [ $post['vk'] ])->fetchColumn();
			if ( $check_vk > 0 ) {
				$this->error = 'Такой VK уже занят, укажите другой';
				return false;
			}

			DB::run('UPDATE `'.$this->DB['prefix'].'_admins_servers` SET `vk` = ? WHERE `admin_id` = ?', [ $post['vk'], $sess_acc['id'] ]);
			$_SESSION['account']['vk'] = $post['vk'];
			$check_change = true;
		}

		// смена ника/steamid
		if ( $user != $sess_acc['username'] ) {
			if ( empty($user) || mb_strlen($user) < 3 || mb_strlen($user) > 32 ) {
				$this->error = 'Короткий ник или SteamID';
				return false;
			}
			if ( !$this->checkUsernameInDb($user) ) {
				return false;
			}

			if ( $post['type'] == 'a' ) {
				DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `username` = ?, `steamid` = ?, `nickname` = ? WHERE `id` = ?", 
					[ $user, $user, $user, $sess_acc['id'] ]);
			}
			if ( $post['type'] == 'ac' ) {
				DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `username` = ?, `steamid` = ?, `nickname` = ? WHERE `id` = ?", 
					[ $user, $user, $user, $sess_acc['id'] ]);
			}
			$_SESSION['account']['username'] = $user;
			$_SESSION['account']['steamid'] = $user;
			$_SESSION['account']['nickname'] = $user;
			$check_change = true;
		}

		// смена типа
		if ( $post['type'] != $sess_acc['flags'] ) {
			if ( $post['type'] == 'a' ) {
				DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `flags` = ? WHERE `id` = ?", [ $post['type'], $sess_acc['id'] ]);
			}
			if ( $post['type'] == 'ac' ) {
				DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `flags` = ? WHERE `id` = ?", [ $post['type'], $sess_acc['id'] ]);
			}
			$_SESSION['account']['flags'] = $post['type'];
			$check_change = true;
		}

		// смена типа аккаунта
		// if ( $post['type'] /*!= $sess_acc['flags']*/ ) 
		// {
		// 	$user = ($post['type'] == 'a') ? $post['nickname'] : $post['steamid'];

		// 	if ( empty($user) || mb_strlen($user) < 3 || mb_strlen($user) > 32 ) {
		// 		$this->error = 'Короткий ник или SteamID';
		// 		return false;
		// 	}
		// 	if ( !$this->checkUsernameInDb($user) ) {
		// 		return false;
		// 	}
			
		// 	if ( $post['type'] == 'a' ) {
		// 		DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `flags` = ?, `username` = ?, `steamid` = ?, `nickname` = ? WHERE `id` = ?", 
		// 			[ $post['type'], $user, $user, $user, $sess_acc['id'] ]);
		// 	}
		// 	if ( $post['type'] == 'ac' ) {
		// 		DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `flags` = ?, `username` = ?, `steamid` = ?, `nickname` = ? WHERE `id` = ?", 
		// 			[ $post['type'], $user, $user, $user, $sess_acc['id'] ]);
		// 	}
		// 	$_SESSION['account']['username'] = $user;
		// 	$_SESSION['account']['steamid'] = $user;
		// 	$_SESSION['account']['nickname'] = $user;
		// 	$_SESSION['account']['flags'] = $post['type'];

		// 	$check_change = true;
		// }

		if ( $check_change == false ) {
			$this->error = 'Вы ничего не поменяли, зачем нажимать кнопку?';
			return false;
		}
		return true;
	}

	public function authorizedBuy($post, $sess_acc, $updateTimeUser = null)
	{
		$MAIN = new Main;

		// рандомный номер заказа
		$pay_id 	= rand(999, 999999);
		$shop 		= $post['shop'];
		$server 	= $post['server'];
		$privilege 	= $post['privilege'];
		$days 		= $post['days'];

		$info = DB::run('SELECT * FROM `ez_privileges` `t1` JOIN `ez_privileges_times` `t2` WHERE `t2`.`pid` = ? AND `t1`.`sid` = ? AND `t1`.`id` = ? AND `t2`.`time` = ? LIMIT 1', [ $post['privilege'], $post['server'], $post['privilege'], $post['days'] ])->fetch(PDO::FETCH_ASSOC);

		if(empty($info) || $info == false) {
			$this->errro = 'Ошибка получения информации о привилегии';
			return false;
		}

		$order_amount = $MAIN->amountCalculate($info['price'], $info['sid'], $info['pid']);
		// var_dump($order_amount);

		$srok = ( $info['time'] == 0 ) ? $srok = 'сроком Навсегда': $srok = 'сроком на '.$info['time'].' дней.';
		$desc = 'Привилегия '.$info['name'] .' '. $srok; // в юрл могут быть проблемы из за пробелов

		try {
			DB::run('INSERT INTO `ez_buy_logs`(`id`, `web_id`, `steamid`, `nickname`, `password`, `access`, `type`, `sid`, `pid`, `days`, `shop`, `vk`, `email`, `created`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
				[ $pay_id, $sess_acc['id'], $sess_acc['steamid'], $sess_acc['nickname'], $sess_acc['password'], $info['access'], $sess_acc['flags'], $server, $privilege, $days, $shop, $sess_acc['vk'], $sess_acc['email'], $this->time ]);
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
			return false;
		}

		// продление привилегии через ЛК
		if ( isset($_SESSION['authorization']) && $updateTimeUser != null ) {
			$core_id = 'core_id=3';
		}
		// покупка новой привилегии через ЛК
		if ( isset($_SESSION['authorization']) && $updateTimeUser == null ) {
			$core_id = 'core_id=2';
		}
		// покупка привилегий
		if ( !isset($_SESSION['authorization']) ) {
			$core_id = 'core_id=1';
		}

		switch ($shop) {
			case 'freekassa':
				$sign = md5($this->FK['merchant_id'].':'.$order_amount.':'.$this->FK['secret_word1'].':'.$pay_id);
				$url = $this->FK['url'] . '?s=' . $sign . '&o=' . $pay_id . '&m=' . $this->FK['merchant_id'] . '&oa=' . $order_amount . '&us_' . $core_id;
				return $url;
			break;

			case 'robokassa':
				$mrh_login 		= $this->RK['shop_id'];
				$mrh_pass1		= $this->RK['pass1'];
				$test = ($this->RK['test'] == 1) ? '&IsTest=1' : '';
				$rk_url = $this->RK['url'];

				$sign = md5("$mrh_login:$order_amount:$pay_id:$mrh_pass1:shp_$core_id");
				$url = "$rk_url?MrchLogin=$mrh_login&OutSum=$order_amount&InvId=$pay_id&SignatureValue=$sign&Encoding=utf-8&shp_$core_id$test";
				return $url;
			break;

			case 'unitpay':
				$unitPay = new UnitPay($this->UP['domain'], $this->UP['secretKey']);
				$core_id = explode('=', $core_id);
				$url = $unitPay->form($this->UP['publicId'], $order_amount, $pay_id.'.'.$core_id[1], $desc, $this->UP['currency']);
				return $url;
			break;
		}
	}

	public function resetPassword($email)
	{
		$MAILER = new Sendmailer;
		$newpass = rand(11111, 999999);
		$newpass_md5 = md5($newpass);

		if ( $MAILER->resetPasswordMessage($email, $newpass) ) 
		{
			$sql = DB::run('SELECT `admin_id`, `email` FROM `'.$this->DB['prefix'].'_admins_servers` WHERE `email` = ?', [ $email ])->fetch(PDO::FETCH_ASSOC);
			if ( empty($sql) ) {
				$this->error = 'Пользователь с таким Email-ом не найден';
				return false;
			}

			try {
				DB::beginTransaction();
				DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `password` = ? WHERE `id` = ?', [ $newpass_md5, $sql['admin_id'] ]);
			} catch (Exception $e) {
				DB::rollBack();
				echo 'Error:' . $e->getMessage();
				return false;
			}
			return true;
		}
		return false;
	}

	// MINI FUNCTIONS
	public function getServerNameById($server_id)
	{
		$sql = DB::run('SELECT `hostname` FROM `'.$this->DB['prefix'].'_serverinfo` WHERE `id` = ?', [ $server_id ])->fetch(PDO::FETCH_ASSOC);
		return $sql['hostname'];
	}
	public function getTariffNameById($server_id)
	{
		$sql = DB::run('SELECT `name` FROM `ez_privileges` WHERE `id` = ?', [ $server_id ])->fetch(PDO::FETCH_ASSOC);
		if(!$sql) return 'unknown';
		return $sql['name'];
	}
	public function checkUsernameInDb($username)
	{
		$sql = DB::run("SELECT COUNT(id) FROM `{$this->DB['prefix']}_amxadmins` WHERE `username` = ?", [ $username ])->fetchColumn();
		if ( $sql > 0 ) {
			$this->error = 'Такой ник/steamid уже занят!';
			return false;
		}
		return true;
	}
}