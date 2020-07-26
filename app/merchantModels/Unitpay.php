<?php 
namespace app\merchantModels;

require_once 'app/lib/unitpay/UnitPay.php';
require_once 'app/models/Sendmailer.php';

use app\core\Model;
use app\core\Config;

use app\models\Merchant;
use app\models\Account;
use app\models\Sendmailer;

use app\lib\UnitPay;
use app\lib\DB;
use PDO;

class UnitpayModel extends Model
{
	private $MAILER;

	// other models
	private $MERCHANT;
	private $ACCOUNT;
	public $UnitPay;

	public function __construct()
	{
		parent::__construct();

		$this->UnitPay = new UnitPay($this->UP['domain'], $this->UP['secretKey']);

		// other models
		$this->MERCHANT = new Merchant;
		$this->ACCOUNT = new Account;
		$this->MAILER = new Sendmailer;
	}

	public function checkPay($get, $core_id)
	{
		if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
			die('method error');
		}

		$params = $get['params'];

		// переменные из поста
		$amount 		= $params['orderSum'];
		$method 		= $get['method'];
		$pay_id 		= explode('.', $params['account']);

		// другие переменные
		$nickname		= null;
		$steamid		= null;
		$username		= null;
		$check_admins	= false;

		$this->UnitPay = new UnitPay($this->UP['domain'], $this->UP['secretKey']);

		if( $core_id != 'up_core_id=1' ) {
			print $this->UnitPay->getErrorHandlerResponse("MerchantModels / Unitpay / checkPay / error #1 (core_id: $core_id)");
		}

		$temp = DB::run('SELECT * FROM `ez_buy_logs` WHERE `id` = ? LIMIT 1', [ $pay_id[0] ] )->fetch(PDO::FETCH_ASSOC);
		if(!$temp) {
			print $this->UnitPay->getErrorHandlerResponse("MerchantModels / Unitpay / checkPay / error #2 (sql false)");
		}

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
			'pay_id'	=> $pay_id[0],
		];

		$info = DB::run('SELECT * FROM `ez_privileges` `t1` JOIN `ez_privileges_times` `t2` WHERE `t2`.`pid` = ? AND `t1`.`sid` = ? AND `t1`.`id` = ? AND `t2`.`time` = ? LIMIT 1', [ $temp_arr['tariff'], $temp_arr['server'], $temp_arr['tariff'], $temp_arr['days'] ])->fetch(PDO::FETCH_ASSOC);

		$price = $this->MERCHANT->resultAmountCalculate($info['price'], $info['sid'], $info['pid'], $temp_arr['browser'], $temp_arr['ip']);
		
		// проверка цены
		if ( $amount != $price ) {
			var_dump($amount, $price);
			print $this->UnitPay->getErrorHandlerResponse('Error: fake amount! Check #1');
		}

		$user = ($temp_arr['type'] == 'a') ? $user = $temp_arr['nickname'] : $user = $temp_arr['steamid'];

		$check_admins = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` WHERE `username` = ? AND `password` = ? LIMIT 1', [$user, $temp_arr['pass_md5']])->fetch(PDO::FETCH_ASSOC);
		
		return $this->saveNewUser($check_admins, $temp_arr);
	}

	public function saveNewUser($check_admins, $arr)
	{
		switch ($check_admins) {
			case true: // нашли юзера в базе
				print $this->UnitPay->getErrorHandlerResponse('user exist');
			break;
			
			case false: // не нашли юзера в базе
				$days = $arr['days'];
				$ashow = 1;
				$static_ban = 'no';
				$date_end = ( $days ) ? 0 : $this->time + 3600 * 24 * $days;

				$reloadAdminsStatus = null;
				$sendMailStatus = false;
				$arrException = [];

				$username 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];
				$steamid 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];
				$nickname 	= ($arr['type'] == 'a') ? $arr['nickname'] : $arr['steamid'];

				// обновление статуса платежа
				DB::run('UPDATE `ez_buy_logs` SET `buy_status` = 1 WHERE `id` = ?', [ $arr['pay_id'] ]);

				// https://tproger.ru/translations/how-to-configure-and-use-pdo/#prepared_statements
				DB::beginTransaction();
				try {
					DB::run('
						INSERT INTO `'.$this->DB['prefix'].'_amxadmins` (
						`username`, `steamid`, `nickname`, `password`, `access`, `flags`, `created`, `expired`, 
						`ashow`, `days`, `tarif_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
					', [ $username, $steamid, $nickname, $arr['pass_md5'], $arr['access'], $arr['type'], $this->time, $date_end, $ashow, $days, $arr['tariff'] ]);

					$lastInsertId = DB::lastInsertId();

					DB::run('INSERT INTO `'.$this->DB['prefix'].'_admins_servers` (`admin_id`, `server_id`, `custom_flags`, `use_static_bantime`, `email`, `vk`) VALUES (LAST_INSERT_ID(), ?, NULL, ?, ?, ?)', [ $arr['server'], $static_ban, $arr['email'], $arr['vk'] ]);
					
					DB::run('UPDATE `ez_promo_logs` SET `user_id` = LAST_INSERT_ID(), `was_used` = 1 WHERE `browser` = ? AND `token` = ?', 
					[ $arr['browser'], $arr['ip'] ]);
					
					DB::commit();
				} catch (PDOException $e) {
					DB::rollBack();
					print $this->UnitPay->getErrorHandlerResponse('MerchantModels / Unitpay / saveNewUser: sql transaction error');
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
					print $this->UnitPay->getErrorHandlerResponse('MerchantModels / Unitpay / createBuyLog: error');
				}
			break;
		}
		return true;
	}

	public function checkAuthPay($get, $core_id)
	{
		if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
			print $this->UnitPay->getErrorHandlerResponse('MerchantModels / Unitpay / checkAuthPay / method error');
		}

		$params = $get['params'];

		// переменные
		$amount 		= $params['orderSum'];
		$method 		= $get['method'];
		$pay_id 		= explode('.', $params['account']);

		// другие переменные
		$nickname		= null;
		$steamid		= null;
		$username		= null;
		$check_admins	= false;

		if( $core_id != 'up_core_id=2' && $core_id != 'up_core_id=3' ) {
			print $this->UnitPay->getErrorHandlerResponse('MerchantModels / Unitpay / checkAuthPay / error #1');
			var_dump($core_id);
		}

		$temp = DB::run('SELECT * FROM `ez_buy_logs` WHERE `id` = ? LIMIT 1', [ $pay_id[0] ] )->fetch(PDO::FETCH_ASSOC);
		if(!$temp) {
			print $this->UnitPay->getErrorHandlerResponse('MerchantModels / Unitpay / checkAuthPay / error #2');
		}

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
			'pay_id'	=> $pay_id[0],
		];

		$info = DB::run('SELECT * FROM `ez_privileges` `t1` JOIN `ez_privileges_times` `t2` WHERE `t2`.`pid` = ? AND `t1`.`sid` = ? AND `t1`.`id` = ? AND `t2`.`time` = ? LIMIT 1', [ $temp_arr['tariff'], $temp_arr['server'], $temp_arr['tariff'], $temp_arr['days'] ])->fetch(PDO::FETCH_ASSOC);

		$price = $this->MERCHANT->resultAmountCalculate($info['price'], $info['sid'], $info['pid'], $temp_arr['browser'], $temp_arr['ip']);
		
		// проверка цены
		if ( $amount != $price ) {
			var_dump($amount, $price);
			print $this->UnitPay->getErrorHandlerResponse('Error: fake amount! Check #2');
		}

		$user = ($temp_arr['type'] == 'a') ? $user = $temp_arr['nickname'] : $user = $temp_arr['steamid'];

		$check_admins = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` WHERE `username` = ? AND `password` = ? LIMIT 1', [$user, $temp_arr['pass']])->fetch(PDO::FETCH_ASSOC);
		
		if($core_id == 'up_core_id=3') {
			return $this->updateTimeAuth($check_admins, $temp_arr);
		}
		return $this->saveAuthUser($check_admins, $temp_arr);
	}

	public function saveAuthUser($check_admins, $arr)
	{
		switch ($check_admins) {
			case false: // не нашли юзера в базе
				print $this->UnitPay->getErrorHandlerResponse('MerchantModels / Unitpay / saveAuthUser: user no exist');
			break;
			
			case true: //  нашли юзера в базе
				$days = $arr['days'];
				$date_end = ( $days ) ? 0 : $this->time + 3600 * 24 * $days;

				$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` `t1` JOIN `'.$this->DB['prefix'].'_admins_servers` `t2` WHERE `t1`.`id` = ? AND `t1`.`id` = `t2`.`admin_id` LIMIT 1', [$arr['user_id']])->fetch(PDO::FETCH_ASSOC);

				if(!$sql) {
					print $this->UnitPay->getErrorHandlerResponse('MerchantModels / Unitpay / saveAuthUser: case true: sql error');
				}

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
					print $this->UnitPay->getErrorHandlerResponse($e->getMessage());
				}
			break;
		}
		return true;
	}

	public function updateTimeAuth($check_admins, $arr)
	{
		switch ($check_admins) {
			case true:
				$days = $arr['days'];
				$date_end = ( $days ) ? 0 : $this->time + 3600 * 24 * $days;

				// обновление статуса платежа
				DB::run('UPDATE `ez_buy_logs` SET `buy_status` = 1 WHERE `id` = ?', [ $arr['pay_id'] ]);

				if( $date_end == 0 )
				{
					try {
						DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `expired` = 0 WHERE `id` = ?', [ $arr['user_id'] ]);
					} catch (Exception $e) {
						print $this->UnitPay->getErrorHandlerResponse($e->getMessage());
					}
					if ( !$this->MERCHANT->updateSessionExpiredTime(($check_admins['expired'] + $date_con_b)) ) {
						print $this->UnitPay->getErrorHandlerResponse('MerchantModels / Unitpay / updateTimeAuth: error session update #1');
					}
				} else {
					$date_con_a = $this->time + 3600 * 24 * $days;
					$date_con_b = 3600 * 24 * $days;

					if ( $check_admins['expired'] < $this->time ) // время окончания меньше текущего 
					{
						try {
							DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `expired` = ? WHERE `id` = ?', [ $date_con_a, $arr['user_id'] ]);
						} catch (Exception $e) {
							print $this->UnitPay->getErrorHandlerResponse($e->getMessage());
						}
						if ( !$this->MERCHANT->updateSessionExpiredTime($date_con_a) ) {
							print $this->UnitPay->getErrorHandlerResponse('MerchantModels / Unitpay / updateTimeAuth: error session update #2');
						} 
					} else { // если же нет
						try {
							DB::run('UPDATE `'.$this->DB['prefix'].'_amxadmins` SET `expired` = (`expired` + ?) WHERE `id` = ?', [ $date_con_b, $arr['user_id'] ]);
						} catch (Exception $e) {
							print $unitPay->getErrorHandlerResponse($e->getMessage());
						}
						if ( !$this->MERCHANT->updateSessionExpiredTime(($check_admins['expired'] + $date_con_b)) ) {
							print $this->UnitPay->getErrorHandlerResponse('MerchantModels / Unitpay / updateTimeAuth: error session update #3');
						} 
					}
				}
				return true;
			break;
			
			case false:
				print $this->UnitPay->getErrorHandlerResponse('MerchantModels / Unitpay / updateTimeAuth: case false');
			break;
		}
	}

	public function unBan($get)
	{
		if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
			die('method error');
		}

		$params = $get['params'];

		$price 		= Config::get('BANS')['price'];
		$amount 	= $params['orderSum'];
		$ban_id 	=  explode('.', $params['account']);

		if ( $amount != $price ) {
			print $this->UnitPay->getErrorHandlerResponse("MerchantModels / Unitpay / unBan: fake amount! Ban ID: " .$ban_id[0]. ". amount: $amount, price: $price");
		}

		try {
			DB::run("UPDATE `{$this->DB['prefix']}_bans` SET `ban_length` = -1 WHERE `bid` = ?", [ $ban_id[0] ]);
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
	}
}