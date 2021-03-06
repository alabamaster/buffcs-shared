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

class Robokassa extends Model
{
	private $MAILER;

	// other models
	private $MERCHANT;
	private $ACCOUNT;

	public function __construct()
	{
		parent::__construct();

		// other models
		$this->MERCHANT = new Merchant;
		$this->ACCOUNT = new Account;
		$this->MAILER = new Sendmailer;
	}

	public function checkPay($post)
	{
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			die("Error: merchantModels / Robokassa / checkPay: method error\n");
		}

		// переменные из поста
		$amount 		= $post['OutSum'];
		$pay_id 		= $post['InvId'];
		$core_id 		= $post['shp_core_id'];
		$crc 			= strtoupper($post['SignatureValue']);
		$password2		= $this->RK['pass2'];

		// проверка подписи
		$my_crc = strtoupper( md5("$amount:$pay_id:$password2:shp_core_id=1") );
		if ( $my_crc != $crc ) die("Error: merchantModels / Robokassa / checkPay: wrong sign\n");
		
		if( $core_id != 1 ) die("Error: merchantModels / Robokassa / checkPay: core_id error\n");

		$temp = DB::run('SELECT * FROM `ez_buy_logs` WHERE `id` = ? LIMIT 1', [ $pay_id ] )->fetch(PDO::FETCH_ASSOC);
		if(!$temp) die("Error: merchantModels / Robokassa / checkPay: order id: {$pay_id} not found\n");

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
			'pay_id'	=> $pay_id,
		];

		$info = DB::run('SELECT * FROM `ez_privileges` `t1` JOIN `ez_privileges_times` `t2` WHERE `t2`.`pid` = ? AND `t1`.`sid` = ? AND `t1`.`id` = ? AND `t2`.`time` = ? LIMIT 1', [ $temp_arr['tariff'], $temp_arr['server'], $temp_arr['tariff'], $temp_arr['days'] ])->fetch(PDO::FETCH_ASSOC);

		$price = $this->MERCHANT->resultAmountCalculate($info['price'], $info['sid'], $info['pid'], $temp_arr['browser'], $temp_arr['ip']);
		
		// проверка цены
		if ( $amount != $price ) {
			var_dump($amount, $price);
			die("Error: merchantModels / Robokassa / checkPay: fake amount! Check #1\n");
		}

		$user = ($temp_arr['type'] == 'a') ? $user = $temp_arr['nickname'] : $user = $temp_arr['steamid'];

		$check_admins = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` WHERE `username` = ? AND `password` = ? LIMIT 1', [$user, $temp_arr['pass_md5']])->fetch(PDO::FETCH_ASSOC);

		echo "OK$pay_id\n";
		return $this->saveNewUser($check_admins, $temp_arr);
	}

	public function saveNewUser($check_admins, $arr)
	{
		switch ($check_admins) {
			case true: // нашли юзера в базе
				die("Error: merchantModels / Robokassa / saveNewUser: user exist in database!\n");
			break;
			
			case false: // не нашли юзера в базе
				$days 		= $arr['days'];
				$ashow 		= 1;
				$static_ban = 'no';
				$date_end 	= ( $days == 0 ) ? 0 : $this->time + 3600 * 24 * $days;

				$reloadAdminsStatus = null;
				$sendMailStatus = false;
				$arrException = [];

				$username 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];
				$steamid 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];
				$nickname 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];

				// обновление статуса платежа
				DB::run('UPDATE `ez_buy_logs` SET `buy_status` = 1 WHERE `id` = ?', [ $arr['pay_id'] ]);

				# https://tproger.ru/translations/how-to-configure-and-use-pdo/#prepared_statements
				DB::beginTransaction();
				try {
					DB::run('
						INSERT INTO `'.$this->DB['prefix'].'_amxadmins` (
						`username`, `steamid`, `nickname`, `password`, `access`, `flags`, `created`, `expired`, 
						`ashow`, `days`, `tarif_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
					', [ $username, $steamid, $nickname, $arr['pass_md5'], $arr['access'], $arr['type'], $this->time, $date_end, $ashow, $days, $arr['tariff'] ]);

					$lastInsertId = DB::lastInsertId();

					DB::run('INSERT INTO `'.$this->DB['prefix'].'_admins_servers` (`admin_id`, `server_id`, `custom_flags`, `use_static_bantime`, `email`, `vk`) VALUES (LAST_INSERT_ID(), ?, NULL, ?, ?, ?)', [ $arr['server'], $static_ban, $arr['email'], $arr['vk'] ]);
					
					// update promo logs
					DB::run('UPDATE `ez_promo_logs` SET `user_id` = LAST_INSERT_ID(), `was_used` = 1 WHERE `browser` = ? AND `token` = ?', 
					[ $arr['browser'], $arr['ip'] ]);

					DB::commit();
				} catch (PDOException $e) {
					DB::rollBack();
					echo 'Error: ' . $e->getMessage();
				}

				// reload admins
				if ( Config::get('RELOADADMINS') == 1 ) 
				{
					if ( !$this->MERCHANT->reloadAdmins($arr['server']) ) 
					{
						$reloadAdminsStatus = false;
						$arrException[] = $this->MERCHANT->error;
					} else {
						$reloadAdminsStatus = true;
					}
				}

				// отправка почты
				if ( $this->MAILER->newPaySuccessMessage($arr['pay_id']) ) {
					$sendMailStatus = true;
				}

				// лог
				if ( !$this->MERCHANT->createBuyLog($this->time, $lastInsertId, $arr['server'], $reloadAdminsStatus, $sendMailStatus, $arrException) ) {
					die('function createBuyLog: error');
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
		$amount 		= $post['OutSum'];
		$pay_id 		= $post['InvId'];
		$core_id 		= $post['shp_core_id'];
		$crc 			= strtoupper($post['SignatureValue']);
		$password2		= $this->RK['pass2'];

		// проверка подписи
		if ( $core_id == 2 ) {
			$my_crc = strtoupper(md5("$amount:$pay_id:$password2:shp_core_id=2"));
		} elseif ( $core_id == 3 ) {
			$my_crc = strtoupper(md5("$amount:$pay_id:$password2:shp_core_id=3"));
		} else {
			die("Error: merchantModels / Robokassa / checkAuthPay: my_src error\n");
		}

		if ( $my_crc != $crc ) 
		{
			die("Error: merchantModels / Robokassa / checkAuthPay: wrong sign\n");
		}

		if($core_id != 2 && $core_id != 3) die("Error: merchantModels / Robokassa / checkAuthPay: core_id error\n");

		$temp = DB::run('SELECT * FROM `ez_buy_logs` WHERE `id` = ? LIMIT 1', [ $pay_id ] )->fetch(PDO::FETCH_ASSOC);
		if(!$temp) die("Error: merchantModels / Robokassa / checkAuthPay: this purchase was not found in the logs.\n");

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
			'pay_id'	=> $pay_id,
		];

		$info = DB::run('SELECT * FROM `ez_privileges` `t1` JOIN `ez_privileges_times` `t2` WHERE `t2`.`pid` = ? AND `t1`.`sid` = ? AND `t1`.`id` = ? AND `t2`.`time` = ? LIMIT 1', [ $temp_arr['tariff'], $temp_arr['server'], $temp_arr['tariff'], $temp_arr['days'] ])->fetch(PDO::FETCH_ASSOC);

		$price = $this->MERCHANT->resultAmountCalculate($info['price'], $info['sid'], $info['pid'], $temp_arr['browser'], $temp_arr['ip']);
		
		// проверка цены
		if ( $amount != $price ) {
			var_dump($amount, $price);
			die("Error: merchantModels / Robokassa / checkAuthPay: fake amount! Check #2\n");
		}

		$user = ($temp_arr['type'] == 'a') ? $user = $temp_arr['nickname'] : $user = $temp_arr['steamid'];

		$check_admins = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` WHERE `username` = ? AND `password` = ? LIMIT 1', [$user, $temp_arr['pass']])->fetch(PDO::FETCH_ASSOC);

		echo "OK$pay_id\n";
		
		if($post['shp_core_id'] == 3) {
			return $this->updateTimeAuth($check_admins, $temp_arr);
		}
		return $this->saveAuthUser($check_admins, $temp_arr);
	}

	public function saveAuthUser($check_admins, $arr)
	{
		switch ($check_admins) {
			case false: // не нашли юзера в базе
				die("Error: merchantModels / Robokassa / saveAuthUser: user no exist in database\n");
			break;
			
			case true: //  нашли юзера в базе
				$days 		= $arr['days'];
				$date_end 	= ( $days == 0 ) ? 0 : $this->time + 3600 * 24 * $days;

				$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` `t1` JOIN `'.$this->DB['prefix'].'_admins_servers` `t2` WHERE `t1`.`id` = ? AND `t1`.`id` = `t2`.`admin_id` LIMIT 1', [$arr['user_id']])->fetch(PDO::FETCH_ASSOC);

				if(!$sql) die("Error: merchantModels / Robokassa / saveAuthUser: case true: sql error\n");

				// обновление статуса платежа
				DB::run('UPDATE `ez_buy_logs` SET `buy_status` = 1 WHERE `id` = ?', [ $arr['pay_id'] ]);

				try {
					DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `access` = ?, `created` = ?, `expired` = ?, `days` = ?, `tarif_id` = ? WHERE `id` = ?', [
						$arr['access'], $this->time, $date_end, $days, $arr['tariff'], $arr['user_id']
					]);
					DB::run('UPDATE `'.$this->DB['prefix'].'_admins_servers` SET `server_id` = ? WHERE `admin_id` = ?', [ 
						$arr['server'], $arr['user_id']
					]);
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
				$days 		= $arr['days'];
				$date_end 	= ( $days == 0 ) ? 0 : $this->time + 3600 * 24 * $days;

				// обновление статуса платежа
				DB::run('UPDATE `ez_buy_logs` SET `buy_status` = 1 WHERE `id` = ?', [ $arr['pay_id'] ]);

				if( $date_end == 0 )
				{
					try {
						DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `expired` = 0 WHERE `id` = ?', [ $arr['user_id'] ]);
					} catch (Exception $e) {
						echo 'Error: ' . $e->getMessage();
					}
					if ( !$this->MERCHANT->updateSessionExpiredTime(($check_admins['expired'] + $date_con_b)) ) {
						die("Error: merchantModels / Robokassa / updateTimeAuth: updateSessionExpiredTime #1\n");
					}
				} else {
					$date_con_a = $this->time + 3600 * 24 * $days;
					$date_con_b = 3600 * 24 * $days;

					if ( $check_admins['expired'] < $this->time ) // время окончания меньше текущего 
					{
						try {
							DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `expired` = ? WHERE `id` = ?', [ $date_con_a, $arr['user_id'] ]);
						} catch (Exception $e) {
							echo 'Error: ' . $e->getMessage();
						}
						if ( !$this->MERCHANT->updateSessionExpiredTime($date_con_a) ) {
							die("Error: merchantModels / Robokassa / updateTimeAuth: updateSessionExpiredTime #2\n");
						}
					} else { // если же нет
						try {
							DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `expired` = (`expired` + ?) WHERE `id` = ?', [ $date_con_b, $arr['user_id'] ]);
						} catch (Exception $e) {
							echo 'Error: ' . $e->getMessage();
						}
						if ( !$this->MERCHANT->updateSessionExpiredTime(($check_admins['expired'] + $date_con_b)) ) {
							die("Error: merchantModels / Robokassa / updateTimeAuth: updateSessionExpiredTime #3\n");
						}
					}
				}
			break;
			
			case false:
				die("Error: modelsMerchant / Robokassa / updateTimeAuth: case false\n");
			break;
		}
	}

	public function unBan($post)
	{
		$price 		= Config::get('BANS')['price'];
		$amount 	= $post['OutSum'];
		$ban_id 	= $post['InvId'];

		if ( $amount != $price ) die("Error: merchantModels / Robokassa / unBan: fake amount! Ban ID: $ban_id. amount: $amount, price: $price");

		// обновление статуса платежа
		DB::run('UPDATE `ez_buy_logs` SET `buy_status` = 1 WHERE `id` = ?', [ $ban_id ]);

		try {
			DB::run("UPDATE `{$this->DB['prefix']}_bans` SET `ban_length` = -1 WHERE `bid` = ?", [ $ban_id ]);
			echo "OK$ban_id\n";
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
	}
}