<?php 
namespace app\models;

// PHP MAILER
require_once 'app/models/Sendmailer.php';

// UnitPay
require_once 'app/lib/unitpay/UnitPay.php';

use app\core\Model;
use app\core\Config;

use app\lib\UnitPay; // unitpay
use app\lib\DB;
use PDO;

use app\models\Sendmailer;

class Main extends Model
{
	public static function discount($cost, $discount)
	{
		$a = $cost / 100 * $discount;
		return $cost - $a;
	}

	public function htmlSelectShops()
	{
		$FK 	= ($this->FK['active'] == 1) ? $FK = '<option value="freekassa">Freekassa</option>' : '';
		$RK 	= ($this->RK['active'] == 1) ? $RK = '<option value="robokassa">Robokassa</option>' : '';
		$UP 	= ($this->UP['active'] == 1) ? $UP = '<option value="unitpay">UnitPay</option>' : '';
		$html 	= $FK . $RK . $UP;
		return $html;
	}

	// * * * * * * * * * * ПОКУПКА ПРИВИЛЕГИЯ * * * * * * * * * * //
	public function getAllServers()
	{
		$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_serverinfo` ORDER BY `id`')->fetchAll();
		return $sql;
	}

	public function getAllPrivileges()
	{
		$sql = DB::run('SELECT * FROM `ez_privileges` ORDER BY `id`')->fetchAll();
		return $sql;
	}

	public function getPrivileges($post)
	{
		$sql = DB::run('SELECT * FROM `ez_privileges` WHERE `sid` = ?', [$post['server_id']])->fetchAll();
		return $sql;
	}

	public function getPrivilegesFromServerId($sid)
	{
		$sql = DB::run('SELECT * FROM `ez_privileges` WHERE `sid` = ? AND `active` = 1', [$sid])->fetchAll();
		return $sql;
	}
	// * * * * * * * * * * ПОКУПКА ПРИВИЛЕГИЯ * * * * * * * * * * //

	// * * * * * * * * * * ПОКУПАТЕЛИ * * * * * * * * * * //
	// нужно передавать сервер Id и по нему уже выдавать
	public function getBuyers()
	{
		if ( isset($_SESSION['admin']) ) 
		{
			switch (Config::get('BUYERS_SORT')) {
				case 1:
				$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` `t1` JOIN `'.$this->DB['prefix'].'_admins_servers` `t2` WHERE `t1`.`id` = `t2`.`admin_id` AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL) ORDER BY `created` DESC')->fetchAll();
				break;
				
				case 2:
					$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` `t1` JOIN `'.$this->DB['prefix'].'_admins_servers` `t2` WHERE `t1`.`id` = `t2`.`admin_id` AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL)  AND (`t1`.`expired` >= ? OR `t1`.`expired` = 0) ORDER BY `created` DESC', [$this->time])->fetchAll();
				break;
			}
		} else {
			switch (Config::get('BUYERS_SORT')) {
				case 1:
					$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` `t1` JOIN `'.$this->DB['prefix'].'_admins_servers` `t2` WHERE `t1`.`id` = `t2`.`admin_id` AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL) AND `ashow` = 1 ORDER BY `created` DESC')->fetchAll();
				break;
				
				case 2:
					$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` `t1` JOIN `'.$this->DB['prefix'].'_admins_servers` `t2` WHERE `t1`.`id` = `t2`.`admin_id` AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL)  AND (`t1`.`expired` >= ? OR `t1`.`expired` = 0) AND `ashow` = 1 ORDER BY `created` DESC', [$this->time])->fetchAll();
				break;
			}
		}
		return $sql;
	}

	// получить название сервера по id, исправить Ip на Id
	public function getServerNameById($server_id)
	{
		$sql = DB::run('SELECT `hostname` FROM `'.$this->DB['prefix'].'_serverinfo` WHERE `id` = ?', [$server_id])->fetch(PDO::FETCH_ASSOC);
		return $sql['hostname'];
	}

	// получить название привилегии по id
	public function getPrivilegeNameById($pid)
	{
		$sql = DB::run('SELECT `name` FROM `ez_privileges` WHERE `id` = ?', [$pid])->fetch(PDO::FETCH_ASSOC);
		if (!$sql) return 'unknown';
		return $sql['name'];
	}

	// получить кол-во серверов в бд
	public function getCountServers()
	{
		$sql = DB::run('SELECT COUNT(id) FROM `'.$this->DB['prefix'].'_serverinfo`')->fetchColumn();
		return (int)$sql;
	}

	public function getIcon($pid)
	{
		$query = DB::run('SELECT `icon_img` FROM `ez_privileges` WHERE `id` = ?', [ $pid ])->fetch(PDO::FETCH_ASSOC);
		
		if ( $query['icon_img'] != '' && $query['icon_img'] != null ) {
			return '<img width="16px" src="'.$this->SITE_URL.'icons/'.$query['icon_img'].'">';
		} else {
			return '<img width="16px" src="'.$this->SITE_URL.'unknown.png">';
		}
	}

	public function buyerDataUpdate($post)
	{
		$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` `t1` JOIN `'.$this->DB['prefix'].'_admins_servers` `t2` WHERE `t1`.`id` = ? AND `t1`.`id` = `t2`.`admin_id` LIMIT 1', 
			[$post['user_id']])->fetch(PDO::FETCH_ASSOC);

		$username = ($post['type'] == 'a') ? $post['nickname'] : $post['steamid'];
		$password = ($post['password'] == '') ? $password = null : md5($post['password']);

		if ( !$sql ) {
			$this->errro = 'Ошибка получения ID игрока';
			return false;
		}

		switch ($post['type']) {
			case 'a':
				// check nickname
				if ( $username != $sql['nickname'] ) 
				{
					$check = DB::run("SELECT `username` FROM `{$this->DB['prefix']}_amxadmins` WHERE `username` = ? AND `nickname` = ?", [ $username, $username ])->rowCount();
					if ( $check > 0 ) 
					{
						$this->error = 'Такой никнейм уже занят!';
						return false;
					}
					DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `username` = ?, `steamid` = ?, `nickname` = ? WHERE `id` = ?", [ $username, $username, $username, $post['user_id'] ]);
				}
			break;
			
			case 'ac':
				// check steamid
				if ( $username != $sql['steamid'] ) {
					$check = DB::run('SELECT `username` FROM `'.$this->DB['prefix'].'_amxadmins` WHERE `username` = ? AND `steamid` = ?', 
						[ $username, $username ])->rowCount();
					if ( $check > 0 ) {
						$this->error = 'Такой steamid уже занят!';
						return false;
					}
					DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `username` = ?, `steamid` = ?, `nickname` = ? WHERE `id` = ?', 
						[ $username, $username, $username, $post['user_id'] ]);
				}
			break;
		}

		// check password
		if ( $password != $sql['password'] ) {
			DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `password` = ? WHERE `id` = ?', [ $password, $post['user_id'] ]);
		}

		// check ashow
		if ( $post['show'] != $sql['ashow'] ) {
			DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `ashow` = ? WHERE `id` = ?', [ $post['show'], $post['user_id'] ]);
		}

		// check email
		if ( $post['email'] != $sql['email'] ) {
			if ( $this->emailExist($post['email']) ) {
				return false;
			}
			DB::run('UPDATE `'.$this->DB['prefix'].'_admins_servers` SET `email` = ? WHERE `admin_id` = ?', [ $post['email'], $post['user_id'] ]);
		}

		// check vk
		if ( $post['vk'] != $sql['vk'] ) {
			if ( $this->vkExist($post['vk']) ) {
				return false;
			}
			DB::run('UPDATE `'.$this->DB['prefix'].'_admins_servers` SET `vk` = ? WHERE `admin_id` = ?', [ $post['vk'], $post['user_id'] ]);
		}

		// check type access
		if ( $post['type'] != $sql['flags'] ) {
			DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `flags` = ? WHERE `id` = ?', [ $post['type'], $post['user_id'] ]);
		}

		// check server
		if ( $post['server'] != $sql['server_id'] ) {
			DB::run('UPDATE `'.$this->DB['prefix'].'_admins_servers` SET `server_id` = ? WHERE `admin_id` = ?', [ $post['server'], $post['user_id'] ]);
		}

		// check privilege
		if ( $post['privilege'] != $sql['tarif_id'] ) {
			$queryPriv = DB::run('SELECT `access` FROM `ez_privileges` WHERE `id` = ?', [ $post['privilege'] ])->fetch(PDO::FETCH_ASSOC);
			
			DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `access` = ? WHERE `id` = ?", [ $queryPriv['access'], $post['user_id'] ]);
			DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `tarif_id` = ? WHERE `id` = ?", [ $post['privilege'], $post['user_id'] ]);
		}

		return true;
	}
	// * * * * * * * * * * ПОКУПАТЕЛИ * * * * * * * * * * //


	// * * * * * * * * * * ПРОВЕРКИ * * * * * * * * * * //
	public function checkMainBuyForm($post)
	{
		if ( !isset($post['server']) || @$post['server'] == 0 )	{
			$this->error = 'Выберите сервер';
			return false;
		}
		if ( !isset($post['privilege']) && @$post['privilege'] == 0 ) {
			$this->error = 'Выберите привилегию';
			return false;
		}
		if ( $post['days'] == -1 ) {
			$this->error = 'Выберите срок';
			return false;
		}
		if ( mb_strlen($post['password']) < 3 || mb_strlen($post['password']) > 20 ) {
			$this->error = 'Пароль должен быть от 3 до 20 символов';
			return false;
		}

		$check_email = DB::run('SELECT COUNT(*) FROM `'.$this->DB['prefix'].'_admins_servers` WHERE `email` = ?', [$post['email']])->fetchColumn();
		if ( $check_email > 0 ) {
			$this->error = 'Такой Email уже занят!';
			return false;
		}

		// добавить проверку на сервер
		switch ($post['type']) {
			case 'a':
				$check_username = DB::run('SELECT COUNT(*) FROM `'.$this->DB['prefix'].'_amxadmins` WHERE `username` = ?', [$post['nickname']])->fetchColumn();
				if ( $check_username > 0 ) {
					$this->error = 'Такой никнейм уже занят!';
					return false;
				}
			break;
			
			case 'ac':
			$check_username = DB::run('SELECT COUNT(*) FROM `'.$this->DB['prefix'].'_amxadmins` WHERE `username` = ?', [$post['steamid']])->fetchColumn();
				if ( $check_username > 0 ) {
					$this->error = 'Такой SteamID уже занят!';
					return false;
				}
			break;
		}
		
		$check_vk = DB::run('SELECT COUNT(*) FROM `'.$this->DB['prefix'].'_admins_servers` WHERE `vk` = ?', [$post['vk']])->fetchColumn();
		if ( $check_vk > 0 ) {
			$this->error = 'Ссылка на такой профиль ВК уже занята!';
			return false;
		}
		return true;
	}

	public function checkInputs($server, $privilege, $days, $password)
	{
		if ( !isset($server) )	{
			$this->error = 'Выберите сервер';
			return false;
		}
		if ( $privilege == 0 ) {
			$this->error = 'Выберите привилегию';
			return false;
		}
		if ( $days == -1 ) {
			$this->error = 'Выберите срок';
			return false;
		}
		if ( mb_strlen($password) < 3 || mb_strlen($password) > 20 ) {
			$this->error = 'Пароль должен быть от 3 до 20 символов';
			return false;
		}

		return true;
	}

	public function emailExist($email)
	{
		if ( mb_strlen($email) < 3 || mb_strlen($email) > 30 ) {
			$this->error = 'Email должен быть от 3 до 30 символов!';
			return true;
		}

		$sql = DB::run('SELECT COUNT(email) FROM `'.$this->DB['prefix'].'_admins_servers` WHERE `email` = ?', [$email])->fetchColumn();
		
		if( $sql > 0 ) {
			$this->error = 'Такой Email уже занят!';
			return true;
		}
		return false;
	}

	public function userExist($post)
	{
		if(!isset($post['server'])) {
			$this->error = 'Выберите сервер';
			return true;
		}

		$username = ($post['type'] == 'a') ? $post['nickname'] : $username = $post['steamid'];

		if ( mb_strlen($username) < 3 || mb_strlen($username) > 30 ) {
			$this->error = 'Ник или SteamID должен быть от 3 до 30 символов! (кириллица от 3 до 15)';
			return true;
		}

		$sql = DB::run('SELECT COUNT(*) FROM `'.$this->DB['prefix'].'_amxadmins` `t1` JOIN `'.$this->DB['prefix'].'_admins_servers` `t2` WHERE `t1`.`username` = ? AND `t2`.`server_id` = ?', [$username, $post['server']])->fetchColumn();
		
		if( $sql > 0 ) {
			$this->error = 'Такой ник или SteamID уже занят, укажите другие данные!';
			return true;
		}
		return false;
	}

	public function vkExist($vk)
	{
		if ( !empty($vk) || $vk != '' ) {
			if ( mb_strlen($vk) < 10 || mb_strlen($vk) > 40 ) {
				$this->error = 'Ссылка ВК должна быть от 10 до 40 символов!';
				return true;
			}
		}

		$sql = DB::run('SELECT `vk` FROM `'.$this->DB['prefix'].'_admins_servers` WHERE `vk` = ?', [$vk])->fetch(PDO::FETCH_ASSOC);
		
		if( $sql ) {
			$this->error = 'Такой ВК уже занят!';
			return true;
		}
		return false;
	}

	public function letsGoPay($post, $user, $updateTimeUser = null)
	{
		if ( $post['type'] != 'a' && $post['type'] != 'ac' ) {
			$this->error = 'Ошибка выбора Тип-а, можно только Ник + пароль или SteamID + пароль!';
			return false;
		}

		$vk = ( empty($post['vk']) ) ? NULL : $post['vk'];
		$web_id = ( isset($_SESSION['authorization']) ) ? $web_id = $_SESSION['account']['id'] : $web_id = null;

		$email = $post['email'];

		// рандомный номер заказа
		$pay_id = rand(999, 999999);

		$info = DB::run('SELECT * FROM `ez_privileges` `t1` JOIN `ez_privileges_times` `t2` WHERE `t2`.`pid` = ? AND `t1`.`sid` = ? AND `t1`.`id` = ? AND `t2`.`time` = ? LIMIT 1', [ $post['privilege'], $post['server'], $post['privilege'], $post['days'] ])->fetch(PDO::FETCH_ASSOC);

		if( !$info) {
			$this->errro = 'Ошибка получения информации о привилегии';
			return false;
		}

		$order_amount = $this->amountCalculate($info['price'], $info['sid'], $info['pid']);

		// var_dump($order_amount);
		// die();

		$srok = ( $info['time'] == 0 ) ? $srok = 'сроком Навсегда': $srok = 'сроком на '.$info['time'].' дней.';
		$desc = 'Привилегия '.$info['name'] .' '. $srok; // в юрл могут быть проблемы из за пробелов
		$browser = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 99) : null;
		$buy_type = 1;

		// продление привилегии через ЛК
		if ( isset($_SESSION['authorization']) && $updateTimeUser !== null ) {
			$core_id = 'core_id=3';
			$buy_type = 3;
		}
		// покупка новой привилегии через ЛК
		if ( isset($_SESSION['authorization']) && $updateTimeUser === null ) {
			$core_id = 'core_id=2';
			$buy_type = 2;
		}
		// покупка привилегий
		if ( !isset($_SESSION['authorization']) ) {
			$core_id = 'core_id=1';
			$buy_type = 1;
		}

		try {
			DB::run('INSERT INTO `ez_buy_logs`(`id`, `web_id`, `steamid`, `nickname`, `password`, `access`, `type`, `sid`, `pid`, `days`, `shop`, `browser`, `ip`, `vk`, `email`, `created`, `buy_type`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$pay_id, $web_id, $user, $user, $post['password'], $info['access'], $post['type'], $post['server'], $post['privilege'], $post['days'], $post['shop'], $browser, $_SERVER['REMOTE_ADDR'], $vk, $email, $this->time, $buy_type]);
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
			return false;
		}

		switch ($post['shop']) {
			case 'freekassa':
				$sign = md5($this->FK['merchant_id'].':'.$order_amount.':'.$this->FK['secret_word1'].':'.$pay_id);
				$url = $this->FK['url'] . '?s=' . $sign . '&o=' . $pay_id . '&m=' . $this->FK['merchant_id'] . '&oa=' . $order_amount . '&us_' . $core_id;
				return $url;
			break;

			case 'robokassa':
				$mrh_login 		= $this->RK['shop_id'];
				$mrh_pass1		= $this->RK['pass1'];
				$test = ($this->RK['test'] == 1) ? '&IsTest=1' : '';
				$url = $this->RK['url'];

				$sign = md5("$mrh_login:$order_amount:$pay_id:$mrh_pass1:shp_$core_id");
				$url = "$url?MrchLogin=$mrh_login&OutSum=$order_amount&InvId=$pay_id&SignatureValue=$sign&Culture=ru&Encoding=utf-8&shp_$core_id$test";
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

	public function paySuccess($data)
	{
		if(isset($data['MERCHANT_ORDER_ID'])) 
		{
			$core_id 	= $data['us_core_id'];
			$pay_id 	= $data['MERCHANT_ORDER_ID'];
		} 
		elseif (isset($data['InvId'])) 
		{
			$core_id 	= $data['shp_core_id'];
			$pay_id 	= $data['InvId'];
		} 
		elseif (isset($data['account'])) 
		{
			$exp_str 	= explode('.', $data['account']);
			$pay_id 	= $exp_str[0];
			$core_id 	= ( $exp_str[1] == 0 ) ? 'unban' : $exp_str[1];
		} 
		else 
		{
			die('Error: model / Main / paySuccess');
		}

		$logs = DB::run('SELECT * FROM `ez_buy_logs` WHERE `id` = ?', [ $pay_id ])->fetch(PDO::FETCH_ASSOC);
		$tariff = DB::run('SELECT `name` FROM `ez_privileges` WHERE `id` = ?', [ $logs['pid'] ])->fetch(PDO::FETCH_ASSOC);

		$expired = ($logs['days'] == 0) ? 'Бессрочно' : 'через ' . $logs['days'] . ' дн.';

		$arr = [
			'id' 		=> $logs['id'],
			'tariff' 	=> $tariff['name'],
			'expired' 	=> $expired,
			'core_id'	=> $core_id,
			'pay_id'	=> $pay_id,
		];
		return $arr;
	}

	public function payError($data)
	{
		if(isset($data['MERCHANT_ORDER_ID'])) 
		{
			return $data['MERCHANT_ORDER_ID'];
		} 
		elseif (isset($data['InvId'])) 
		{
			return $data['InvId'];
		}
		elseif (isset($data['account'])) 
		{
			$pay_id = explode('.', $data['account']);
			$pay_id = $pay_id[0];
			return $pay_id;
		}
		else 
		{
			die('Error: model / Main / payError');
		}
		return false;
	}

	public function runCron()
	{
		$SENDMAILER = new Sendmailer;
		$SENDMAILER->cronMessage();
	}

	public function checkStatusPromocode($post)
	{
		if ( empty($post['server']) || empty($post['tariff']) ) {
			$this->error = 'Выберите сервер и привилегию';
			return false;
		}
		$sql = DB::run('SELECT * FROM `ez_promo_codes` WHERE `code` = ? AND `sid` = ? AND `pid` = ?', 
			[ $post['thisPromoCode'], $post['server'], $post['tariff'] ])->fetch(PDO::FETCH_ASSOC);
		if ( empty($sql) ) {
			$this->error = 'Такой промокод не найден';
			return false;
		}
		if ( $sql['dateExpired'] < $this->time ) {
			$this->error = 'Срок действия промокода истёк!';
			return false;
		}
		if ( $sql['count_use'] == 0 ) {
			$this->error = 'Промокод уже был использован максимальное кол-во раз';
			return false;
		}

		return $this->checkUsePoromocode($post, $sql['discount']);
	}

	public function checkUsePoromocode($post, $discount)
	{
		$token = $_SERVER['REMOTE_ADDR'];
		$browser = substr($_SERVER['HTTP_USER_AGENT'], 0, 99);

		$sql = DB::run('SELECT `browser`, `token`, `code`, `sid`, `pid` FROM `ez_promo_logs` WHERE `browser` = ? AND `token` = ? AND `code` = ? AND `sid` = ? AND `pid` = ?', 
			[ $browser, $token, $post['thisPromoCode'], $post['server'], $post['tariff'] ])->fetch(PDO::FETCH_ASSOC);
		
		if ( empty($sql) ) {
			return $this->userUsePromocode($post, $discount);
		}

		$this->error = 'Этот промокод можно использовать только один раз';
		return false;
	}

	// ip - $_SERVER['REMOTE_ADDR'] // browser - $_SERVER['HTTP_USER_AGENT']
	public function userUsePromocode($post, $discount)
	{
		// $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
		// $token = rand(77777, 88888);
		$token = $_SERVER['REMOTE_ADDR'];
		$browser = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 99) : null;

		try {
			DB::beginTransaction();
			DB::run('INSERT INTO `ez_promo_logs`(`browser`, `token`, `code`, `discount`, `sid`, `pid`) VALUES (?, ?, ?, ?, ?, ?)', [ $browser, $token, $post['thisPromoCode'], $discount, $post['server'], $post['tariff'] ]);
			DB::run('UPDATE `ez_promo_codes` SET `count_use` = (`count_use` -1) WHERE `code` = ?', [ $post['thisPromoCode'] ]);
			DB::commit();
		} catch (Exception $e) {
			DB::rollBack();
			$this->error = 'Возникла ошибка, попробуйте позже';
			echo 'Error: ' . $e->getMessage();
			return false;
		}
		return true;
	}

	// return amount
	public function amountCalculate($price, $sid, $pid)
	{
		$token = $_SERVER['REMOTE_ADDR'];
		$browser = substr($_SERVER['HTTP_USER_AGENT'], 0, 99);

		$sql = DB::run('SELECT * FROM `ez_promo_logs` WHERE `browser` = ? AND `token` = ? AND `sid` = ? AND `pid` = ?', 
			[ $browser, $token, $sid, $pid ]);

		$amount = ($this->DISCOUNT['active'] == 1) ? self::discount($price, $this->DISCOUNT['discount']) : $price;

		if ( $sql->rowCount() == 1 )
		{
			$sql = $sql->fetch(PDO::FETCH_ASSOC);
			$amount_promo = $amount - (($amount / 100) * $sql['discount']);
			// echo "Цена до всех обработок: {$price}. \nЦена после глобальной скидки: {$amount}. \nЦена после промокода: {$amount_promo}\n";
			return $amount_promo;
		} else {
			$amount = ($this->DISCOUNT['active'] == 1) ? self::discount($price, $this->DISCOUNT['discount']) : $price;
		}
		return $amount;
	}

	/*
		PAGINATION
	*/
	public function sqlRequest($get, $page, $perPage)
	{
		$start 		= ( $page - 1 ) * $perPage;
		$orderSQL 	= 'ORDER BY `t1`.`created` DESC';
		$notAdmin 	= ( !isset($_SESSION['admin']) ) ? 'AND `t1`.`ashow` = 1' : '';
		$sort 		= Config::get('BUYERS_SORT');
		if ( $sort == 1 ) {
			$sortSQL = '';
		} elseif ( $sort == 2 ) {
			$sortSQL = "AND (`t1`.`expired` >= {$this->time} OR `t1`.`expired` = 0)";
		} else {
			$sortSQL = '';
		}
		
		$serverID 	= ( isset($get['server']) ) ? (int)htmlspecialchars($get['server']) : false;
		$search 	= ( isset($get['search']) ) ? '%' . $get['search'] . '%' : false;

		if ( $serverID !== false && $search === false ) { // только сервер
			$querySQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t2`.`server_id` = ? AND `t1`.`id` = `t2`.`admin_id` 
				AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL) $notAdmin $sortSQL $orderSQL LIMIT $start, $perPage
			", [ $serverID ]);
			
			$totalSQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t2`.`server_id` = ? AND `t1`.`id` = `t2`.`admin_id` 
				AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL) $notAdmin $sortSQL $orderSQL", [ $serverID ]);
			// $a = 1;
		}

		if ( $serverID === false && $search !== false ) { // только поиск
			$querySQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t1`.`username` LIKE ? AND `t1`.`id` = `t2`.`admin_id` 
				AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL) $notAdmin $sortSQL $orderSQL LIMIT $start, $perPage
			", [ $search ]);
			
			$totalSQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t1`.`username` LIKE ? AND `t1`.`id` = `t2`.`admin_id` 
				AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL) $notAdmin $sortSQL $orderSQL", [ $search ]);
			// $a = 2;
		}

		if ( $serverID && $search ) { // сервер и поиск
			$querySQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t2`.`server_id` = ? AND `t1`.`username` LIKE ? AND `t1`.`id` = `t2`.`admin_id` 
				AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL) $notAdmin $sortSQL $orderSQL LIMIT $start, $perPage
			", [ $serverID, $search ]);
			
			$totalSQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t2`.`server_id` = ? AND `t1`.`username` LIKE ? AND `t1`.`id` = `t2`.`admin_id` 
				AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL) $notAdmin $sortSQL $orderSQL", [ $serverID, $search ]);
			// $a = 3;
		}

		if ( !isset($querySQL) ) {
			$notAdmin = ( !isset($_SESSION['admin']) ) ? 'AND `t1`.`ashow` = 1' : '';
			$querySQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t1`.`id` = `t2`.`admin_id` 
				AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL) $notAdmin $sortSQL $orderSQL LIMIT $start, $perPage
			");
			
			$totalSQL = DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t1`.`id` = `t2`.`admin_id` 
				AND (`t1`.`tarif_id` != 0 OR `t1`.`tarif_id` != NULL) $notAdmin $sortSQL $orderSQL
			");
			// $a = 4;
		}

		// var_dump($a);

		return ['sql' => $querySQL->fetchAll(), 'total' => $totalSQL->rowCount(), 'start' => $start];
	}

	/*
		USER DELETE
	*/
	public function deleteUser($uid)
	{
		$uid = (int)$uid;

		$sql = DB::run("
			SELECT COUNT(id) FROM `{$this->DB['prefix']}_amxadmins` `t1` JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
			WHERE `t1`.`id` = ? AND `t1`.`id` = `t2`.`admin_id` LIMIT 1
		", [ $uid ])->fetchColumn();

		if ( !$sql || $sql = 0 ) {
			$this->error = 'Ошибка запроса к базе данных';
			return false;
		}

		try {
			DB::beginTransaction();
			DB::run("DELETE FROM `{$this->DB['prefix']}_amxadmins` WHERE `id` = ?", [ $uid ]);
			DB::run("DELETE FROM `{$this->DB['prefix']}_admins_servers` WHERE `admin_id` = ?", [ $uid ]);
			DB::Commit();
			return true;
		} catch (Exception $e) {
			DB::rollBack();
			$this->error = 'Ошибка удаления, обратитесь к разработчику';
			return false;
		}
	}
}