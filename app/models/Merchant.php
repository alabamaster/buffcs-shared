<?php 
namespace app\models;

require_once 'app/lib/SourceQuery_xPaw/bootstrap.php';
require_once 'app/models/Main.php';
use xPaw\SourceQuery\SourceQuery;

use app\core\Model;
use app\core\Config;

// use app\lib\SourceQuery;
use app\lib\DB;
use PDO;

use app\models\Main;

class Merchant extends Model
{
	public $DB = [];
	public $SQUERY;
	public $MAIN_MODEL;
	public $DISCOUNT;

	public function __construct()
	{
		$this->DB 		= require 'app/configs/db.php';
		$this->time 	= time();
		$this->SQUERY 	= new SourceQuery();
		$this->MAIN_MODEL = new Main;
		$this->DISCOUNT = Config::get('DISC');
	}

	public function discount($cost, $discount)
	{
		$a = $cost / 100 * $discount;
		return $cost - $a;
	}

	public function updateSession($player_id)
	{
		$sql = DB::run('SELECT * FROM `'.$this->DB['prefix'].'_amxadmins` `t1` JOIN `'.$this->DB['prefix'].'_admins_servers` `t2` WHERE `t1`.`id` = ? AND `t1`.`id` = `t2`.`admin_id` LIMIT 1', [$player_id])->fetch(PDO::FETCH_ASSOC);

		$_SESSION['account']['access'] 		= $sql['access'];
		$_SESSION['account']['expired'] 	= $sql['expired'];
		$_SESSION['account']['days'] 		= $sql['days'];
		$_SESSION['account']['tarif_id']	= $sql['tarif_id'];
		$_SESSION['account']['server_id'] 	= $sql['server_id'];
		return true;
	}

	public function updateSessionExpiredTime($date_end)
	{
		try {
			$_SESSION['expired'] = $date_end;
			return true;
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
		return false;
	}

	public function reloadAdmins($sid)
	{
		$query = DB::run('SELECT `address`, `rcon` FROM `'.$this->DB['prefix'].'_serverinfo` WHERE `id` = ?', [ $sid ]);
		
		if ( $query->rowCount() < 1 ) { // не нашли ркон
			$this->error = 'Ошибка отправки, не найден RCON в таблице ' . $this->DB['prefix'] . '_serverinfo, где server id: ' . $sid;
			return false;
		}

		try {
			$cs_server = $query->fetch(PDO::FETCH_ASSOC);
			$dataServer = explode(':', $cs_server['address']);
			$ip = $dataServer[0];
			$port = $dataServer[1];

			$cmd = 'amx_reloadadmins';
			$this->SQUERY->Connect($ip, $port, 1, SourceQuery::GOLDSOURCE);
			$this->SQUERY->SetRconPassword($cs_server['rcon']);
			$this->SQUERY->Rcon($cmd);
			$this->SQUERY->Disconnect();
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
		return true;
	}

	public function resultAmountCalculate($price, $sid, $pid, $browser, $token)
	{
		// $token = $_SERVER['REMOTE_ADDR'];
		// $browser = substr($_SERVER['HTTP_USER_AGENT'], 0, 99);

		$sql = DB::run('SELECT * FROM `ez_promo_logs` WHERE `browser` = ? AND `token` = ? AND `sid` = ? AND `pid` = ?', 
			[ $browser, $token, $sid, $pid ])->fetch(PDO::FETCH_ASSOC);

		$amount = ($this->DISCOUNT['active'] == 1) ? self::discount($price, $this->DISCOUNT['discount']) : $price;

		if ( $sql )
		{
			$amount_promo = $amount - (($amount / 100) * $sql['discount']);
			// echo "Цена до всех обработок: {$price}. \nЦена после глобальной скидки: {$amount}. \nЦена после промокода: {$amount_promo}\n";
			return $amount_promo;
		}
		return $amount;
	}
}