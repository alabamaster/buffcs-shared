<?php 
namespace app\merchantModels;

require_once 'app/models/Sendmailer.php';

use app\core\Model;
use app\core\Config;

use app\models\Merchant;
use app\models\Account;
use app\models\Sendmailer;

use app\lib\DB;
use PDO;

class Freekassa extends Model
{
	// private $DB = [];
	// private $DISC;
	// private $FK;
	private $MAILER;

	// other models
	private $MERCHANT;
	private $ACCOUNT;

	public function __construct()
	{
		parent::__construct();
		// $this->DB = require 'app/configs/db.php';
		// $this->time = time();

		// $this->DISC = Config::get('DISC');
		// $this->FK = Config::get('FK');

		// other models
		$this->MERCHANT = new Merchant;
		$this->ACCOUNT = new Account;
		$this->MAILER = new Sendmailer;
	}

	public function checkPay($post)
	{
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			die("Error: merchantModels / Freekassa / checkPay: method error\n");
		}

		// переменные из поста
		$amount 		= $post['AMOUNT'];
		$pay_id 		= $post['MERCHANT_ORDER_ID'];
		$core_id 		= $post['us_core_id'];

		// проверка подписи
		$sign = md5($this->FK['merchant_id'].':'.$amount.':'.$this->FK['secret_word2'].':'.$pay_id);
		if ($sign != $post['SIGN']) 
		{
			die("Error: merchantModels / Freekassa / checkPay: wrong sign\n"); // Неправильная подпись
		}

		if($core_id != 1) die("Error: merchantModels / Freekassa / checkPay: core_id error\n");

		$temp = DB::run('SELECT * FROM `ez_buy_logs` WHERE `id` = ? LIMIT 1', [ $pay_id ] )->fetch(PDO::FETCH_ASSOC);
		if(!$temp) return false;

		$temp_arr = [
			'nickname'	=> $temp['nickname'],
			'steamid'	=> $temp['steamid'],
			'pass_md5' 	=> md5($temp['password']),
			'pass'		=> $temp['password'],
			'access' 	=> $temp['access'],
			'type'		=> $temp['type'],
			'server'	=> (int)$temp['sid'],
			'tariff'	=> (int)$temp['pid'],
			'days'		=> (int)$temp['days'],
			'vk'		=> $temp['vk'],
			'email'		=> $temp['email'],
			'browser'	=> $temp['browser'],
			'ip'		=> $temp['ip'],
		];

		$info = DB::run('SELECT * FROM `ez_privileges` `t1` JOIN `ez_privileges_times` `t2` WHERE `t2`.`pid` = ? AND `t1`.`sid` = ? AND `t1`.`id` = ? AND `t2`.`time` = ? LIMIT 1', [ $temp_arr['tariff'], $temp_arr['server'], $temp_arr['tariff'], $temp_arr['days'] ])->fetch(PDO::FETCH_ASSOC);

		// $price = ($this->DISC['active'] == 1) ? $price = Main::discount($info['price'], $this->DISC['discount']) : $price = $info['price'];
		$price = $this->MERCHANT->resultAmountCalculate($info['price'], $info['sid'], $info['pid'], $temp_arr['browser'], $temp_arr['ip']);
		
		// проверка цены
		if ( $amount != $price ) {
			var_dump($amount, $price);
			die("Error: merchantModels / Freekassa / checkPay: fake amount! Check #1\n");
		}

		$user = ($temp_arr['type'] == 'a') ? $user = $temp_arr['nickname'] : $user = $temp_arr['steamid'];

		$check_admins = DB::run("SELECT * FROM `{$this->DB['prefix']}_amxadmins` WHERE `username` = ? AND `password` = ? LIMIT 1", 
			[$user, $temp_arr['pass_md5']])->fetch(PDO::FETCH_ASSOC);
		
		echo "OK$pay_id\n";
		return $this->saveNewUser($check_admins, $temp_arr, $pay_id);
	}

	public function saveNewUser($check_admins, $arr, $pay_id)
	{
		switch ($check_admins) {
			case true: // нашли юзера в базе
				die("Error: merchantModels / Freekassa / saveNewUser: user exist in database\n");
			break;
			
			case false: // не нашли юзера в базе
				if ( $arr['days'] == 0 ) 
				{
					$date_end = 0;
				} else {
					$date_end = $this->time + 3600 * 24 * $arr['days'];
				}

				$days = $arr['days'];
				$ashow = 1;
				$static_ban = 'no';

				$username 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];
				$steamid 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];
				$nickname 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];

				if( $arr['type'] != 0 && $arr['type'] != 'a' && $arr['type'] != 'ac' )
					die("Error: merchantModels / Freekassa / saveNewUser: type error\n");
				
				// https://tproger.ru/translations/how-to-configure-and-use-pdo/#prepared_statements
				DB::beginTransaction();
				try {
					$lastInsertId = DB::lastInsertId();
					DB::run("
						INSERT INTO `{$this->DB['prefix']}_amxadmins` (
						`username`, `steamid`, `nickname`, `password`, `access`, `flags`, `created`, `expired`, 
						`ashow`, `days`, `tarif_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
					", [ $username, $steamid, $nickname, $arr['pass_md5'], $arr['access'], $arr['type'], $this->time, $date_end, $ashow, $days, $arr['tariff'] ]);

					DB::run("INSERT INTO `{$this->DB['prefix']}_admins_servers` (`admin_id`, `server_id`, `custom_flags`, `use_static_bantime`, `email`, `vk`) VALUES (LAST_INSERT_ID(), ?, NULL, ?, ?, ?)", [ $arr['server'], $static_ban, $arr['email'], $arr['vk'] ]);
					
					// update promo logs
					DB::run('UPDATE `ez_promo_logs` SET `user_id` = LAST_INSERT_ID(), `was_used` = 1 WHERE `browser` = ? AND `token` = ?', 
					[ $arr['browser'], $arr['ip'] ]);
					
					DB::commit();
				} catch (PDOException $e) {
					DB::rollBack();
					echo 'Error: ' . $e->getMessage();
				}

				// отправка почты
				$this->MAILER->newPaySuccessMessage($pay_id);

				// отправка amx_reloadadmins
				if ( Config::get('RELOADADMINS') == 1 ) {
					if ( !$this->MERCHANT->reloadAdmins($arr['server']) ) {
						echo $this->MERCHANT->error;
					}
				}
			break;
		}
	}

	public function checkAuthPay($post)
	{
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			die("Error: merchantModels / Robokassa / checkAuthPay: method error\n");
		}

		// переменные из поста
		$amount 		= $post['AMOUNT'];
		$pay_id 		= $post['MERCHANT_ORDER_ID'];
		$core_id 		= $post['us_core_id'];

		// проверка подписи
		$sign = md5($this->FK['merchant_id'].':'.$amount.':'.$this->FK['secret_word2'].':'.$pay_id);
		if ($sign != $post['SIGN']) die("Error: merchantModels / Robokassa / checkAuthPay: wrong sign\n");

		if($core_id != 2 && $core_id != 3) die("Error: merchantModels / Freekassa / checkAuthPay: core_id error\n");

		$temp = DB::run('SELECT * FROM `ez_buy_logs` WHERE `id` = ? LIMIT 1', [ $pay_id ] )->fetch(PDO::FETCH_ASSOC);
		if(!$temp) die("Error: merchantModels / Freekassa / checkAuthPay: this purchase was not found in the logs.\n");

		$temp_arr = [
			'user_id' 	=> $temp['web_id'],
			'nickname'	=> $temp['nickname'],
			'steamid'	=> $temp['steamid'],
			'pass'		=> $temp['password'],
			'access' 	=> $temp['access'],
			'type'		=> $temp['type'],
			'server'	=> (int)$temp['sid'],
			'tariff'	=> (int)$temp['pid'],
			'days'		=> (int)$temp['days'],
			'vk'		=> $temp['vk'],
			'email'		=> $temp['email'],
			'browser'	=> $temp['browser'],
			'ip'		=> $temp['ip'],
		];

		$info = DB::run('SELECT * FROM `ez_privileges` `t1` JOIN `ez_privileges_times` `t2` WHERE `t2`.`pid` = ? AND `t1`.`sid` = ? AND `t1`.`id` = ? AND `t2`.`time` = ? LIMIT 1', [ $temp_arr['tariff'], $temp_arr['server'], $temp_arr['tariff'], $temp_arr['days'] ])->fetch(PDO::FETCH_ASSOC);

		$price = $this->MERCHANT->resultAmountCalculate($info['price'], $info['sid'], $info['pid'], $temp_arr['browser'], $temp_arr['ip']);
		
		// проверка цены
		if ( $amount != $price ) {
			var_dump($amount, $price);
			die("Error: merchantModels / Freekassa / checkAuthPay: fake amount! Check #2\n");
		}

		$user = ($temp_arr['type'] == 'a') ? $user = $temp_arr['nickname'] : $user = $temp_arr['steamid'];

		$check_admins = DB::run("SELECT * FROM `{$this->DB['prefix']}_amxadmins` WHERE `username` = ? AND `password` = ? LIMIT 1", 
			[ $user, $temp_arr['pass'] ])->fetch(PDO::FETCH_ASSOC);

		echo "OK$pay_id\n";
		
		if($post['us_core_id'] == 3) {
			return $this->updateTimeAuth($check_admins, $temp_arr);
		}
		return $this->saveAuthUser($check_admins, $temp_arr);
	}

	public function saveAuthUser($check_admins, $arr)
	{
		switch ($check_admins) {
			case false: // не нашли юзера в базе
				die("Error: merchantModels / Freekassa / saveAuthUser: user no exist in database!\n");
			break;
			
			case true: //  нашли юзера в базе
				$days 		= $arr['days'];
				$date_end 	= ($days == 0) ? $date_end = 0 : $date_end = $this->time + 3600 * 24 * $days;

				$username 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];
				$steamid 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];
				$nickname 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];

				$sql = DB::run("SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` JOIN `{$this->DB['prefix']}_admins_servers` `t2` WHERE `t1`.`id` = ? AND `t1`.`id` = `t2`.`admin_id` LIMIT 1", [ $arr['user_id'] ])->fetch(PDO::FETCH_ASSOC);

				if(!$sql) die("Error: merchantModels / Freekassa / saveAuthUser: case true: sql error\n");

				try {
					DB::run("UPDATE {$this->DB['prefix']}_amxadmins SET username = ?, steamid = ?, nickname = ?, access = ?, created = ?, expired = ?, days = ?, tarif_id = ? WHERE id = ?", 
						[ $username, $steamid, $nickname, $arr['access'], $this->time, $date_end, $days, $arr['tariff'], $arr['user_id']]);
					DB::run("UPDATE {$this->DB['prefix']}_admins_servers SET `server_id` = ? WHERE `admin_id` = ?", [ $arr['server'], $arr['user_id'] ]);
				} catch (PDOException $e) {
					echo 'Error:' . $e->getMessage();
				}
			break;
		}
	}

	public function updateTimeAuth($check_admins, $arr)
	{
		switch ($check_admins) {
			case true:
				$days = $arr['days'];
				$date_end = ($days == 0) ? $date_end = 0 : $date_end = $this->time + 3600 * 24 * $days;

				// die();

				if( $date_end == 0 )
				{
					try {
						DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `expired` = 0 WHERE `id` = ?", [ $arr['user_id'] ]);
					} catch (Exception $e) {
						die('Error: ' . $e->getMessage());
					}
					if ( !$this->MERCHANT->updateSessionExpiredTime(($check_admins['expired'] + $date_con_b)) ) {
						die("Error: merchantModels / Freekassa / updateTimeAuth: updateSessionExpiredTime #1\n");
					}
				} else {
					$date_con_a = $this->time + 3600 * 24 * $days;
					$date_con_b = 3600 * 24 * $days;

					if ( $check_admins['expired'] < $this->time ) // время окончания меньше текущего 
					{
						try {
							DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `expired` = ? WHERE `id` = ?", [ $date_con_a, $arr['user_id'] ]);
						} catch (Exception $e) {
							die('Error: ' . $e->getMessage());
						}
						if ( !$this->MERCHANT->updateSessionExpiredTime($date_con_a) ) {
							die("Error: merchantModels / Freekassa / updateTimeAuth: updateSessionExpiredTime #2\n");
						}
					} else { // если же нет
						try {
							DB::run("UPDATE `{$this->DB['prefix']}_amxadmins` SET `expired` = (`expired` + ?) WHERE `id` = ?", [ $date_con_b, $arr['user_id'] ]);
						} catch (Exception $e) {
							die('Error: ' . $e->getMessage());
						}
						if ( !$this->MERCHANT->updateSessionExpiredTime(($check_admins['expired'] + $date_con_b)) ) {
							die("Error: merchantModels / Freekassa / updateTimeAuth: updateSessionExpiredTime #3\n");
						}
					}
				}
			break;
			
			case false:
				die("Error: modelsMerchant / Freekassa / updateTimeAuth: case false\n");
			break;
		}
	}

	public function unBan($post)
	{
		$price 	= Config::get('BANS')['price'];
		$amount = $post['AMOUNT'];
		$ban_id	= $post['MERCHANT_ORDER_ID'];

		if ( $amount != $price ) die("Error: merchantModels / Freekassa / unBan: fake amount! Ban ID: {$ban_id}\n");

		try {
			DB::run("UPDATE `{$this->DB['prefix']}_bans` SET `ban_length` = -1 WHERE `bid` = ?", [ $ban_id ]);
			echo "OK$pay_id\n";
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
	}
}