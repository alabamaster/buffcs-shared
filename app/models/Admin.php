<?php 
namespace app\models;

use app\core\Model;
use app\core\Config;

use app\lib\DB;
use PDO;

class Admin extends Model
{
	public function checkDataLogin($post) {
		$sql = DB::run('SELECT `username`, `password` FROM `'.$this->DB['prefix'].'_webadmins` WHERE `username` = ? AND `password` = ?', [$post['username'], md5($post['password'])])->rowCount();
		$this->error = 'Логин или пароль указан неверно';
		
		if ($sql == 0) {
			$this->error = 'Логин или пароль указан неверно';
			return false;
		}
		return true;
	}

	public function sessionGo()
	{
		$_SESSION['admin'] = true;
	}

	public function getAllPrivileges()
	{
		$sql = DB::run('SELECT * FROM `ez_privileges` ORDER BY `id`')->fetchAll();
		return $sql;
	}

	public function getAllServers()
	{
		$sql = DB::run('SELECT `id`, `hostname` ,`address` FROM `'.$this->DB['prefix'].'_serverinfo`')->fetchAll();
		return $sql;
	}

	public function savePromoCode($post)
	{
		if ( !isset($post['selectServer']) || !isset($post['selectTarif']) ) {
			$this->error = 'Выберите сервер и привилегию';
			return false;
		}

		$days = $post['countDays'];
		$dateExpired = strtotime("+$days days");
		try {
			DB::run('INSERT INTO `ez_promo_codes`(`pid`, `sid`, `code`, `discount`, `dateCreated`, `dateExpired`, `count_use`) VALUES (?, ?, ?, ?, ?, ?, ?)', [$post['selectTarif'], $post['selectServer'], $post['inputCode'], $post['codeDiscount'], $this->time, $dateExpired, $post['countUse'] ]);
			return true;
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
		$this->error = 'Ошибка сохранения: model->Admin->savePromoCode';
		return false;
	}

	public function deletePromoCode($code_id)
	{
		try {
			DB::run('DELETE FROM `ez_promo_codes` WHERE `id` = ?', [ $code_id ]);
			return true;
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
		$this->error = 'Ошибка удаления: model->Admin->deletePromoCode';
		return false;
	}

	public function getPrivilegeNameById($pid)
	{
		$sql = DB::run('SELECT `name` FROM `ez_privileges` WHERE `id` = ?', [ (int)$pid ])->fetch(PDO::FETCH_ASSOC);
		if ($sql == null) return 'unknown';
		return $sql['name'];
	}

	public function getPrivilegeInfoById($pid)
	{
		$sql = DB::run('SELECT `name` FROM `ez_privileges` WHERE `id` = ?', [ (int)$pid ])->fetch(PDO::FETCH_ASSOC);
		return $sql; // array
	}

	public function getServerNameById($sid)
	{
		$sql = DB::run('SELECT `hostname` FROM `'.$this->DB['prefix'].'_serverinfo` WHERE `id` = ?', [ (int)$sid ])->fetch(PDO::FETCH_ASSOC);
		return $sql['hostname'];
	}

	public function getAllPromocodes()
	{
		$sql = DB::run('SELECT * FROM `ez_promo_codes`')->fetchAll();
		return $sql;
	}

	public function sideBlockStats()
	{
		$activeUsers = DB::run('SELECT COUNT(id) FROM `'.$this->DB['prefix'].'_amxadmins` WHERE `expired` = 0 OR `expired` > ?', [ $this->time ])->fetchColumn();
		$expiredUsers = DB::run('SELECT COUNT(id) FROM `'.$this->DB['prefix'].'_amxadmins` WHERE `expired` < ? AND `expired` != 0', [ $this->time ])->fetchColumn();
		$countPrivileges = DB::run('SELECT COUNT(id) FROM `ez_privileges`')->fetchColumn();
		$countServers = DB::run('SELECT COUNT(id) FROM `'.$this->DB['prefix'].'_serverinfo`')->fetchColumn();

		$allStats = [
			'activeUsers'       => $activeUsers,
			'expiredUsers'      => $expiredUsers,
			'countPrivileges'   => $countPrivileges,
			'countServers'      => $countServers,
		];
		return $allStats;
	}

	public function viewBuyLogs()
	{
		$sql = DB::run('SELECT * FROM `ez_buy_logs` ORDER BY `table_id` DESC LIMIT 50')->fetchAll();
		return $sql;
	}

	public function defaultAddUser($post)
	{
		$username 	= ($post['type'] == 'a') ? $post['nickname'] : $post['steamid'];
		$steamid 	= ($post['type'] == 'a') ? $post['nickname'] : $post['steamid'];
		$nickname 	= ($post['type'] == 'a') ? $post['nickname'] : $post['steamid'];
		
		$days 	= (int)$post['days'];
		$icq 	= ( empty($post['icq']) || mb_strlen($post['icq']) == 0 ) ? $icq = NULL : $icq = $post['icq'];
		$exp 	= ($days == 0) ? 0 : $this->time + 3600 * 24 * $days;
		$vk 	= ( empty($post['vk']) || mb_strlen($post['vk']) == 0 ) ? $vk = NULL : $vk = $post['vk'];

		try {
			DB::run("INSERT INTO `{$this->DB['prefix']}_amxadmins`(`username`, `password`, `access`, `flags`, `steamid`, `nickname`, `icq`, `ashow`, `created`, `expired`, `days`, `tarif_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
				[ $username, md5($post['password']), $post['access'], $post['type'], $steamid, $nickname, $icq, $post['show'], $this->time, $exp, $days, $post['privilege'] ]);
			
			DB::run("INSERT INTO `{$this->DB['prefix']}_admins_servers`(`admin_id`, `server_id`, `custom_flags`, `use_static_bantime`, `email`, `vk`) VALUES (?, ?, ?, ?, ?, ?)", 
				[ DB::lastInsertId(), $post['server'], null, 'no', $post['email'], $vk ]);
			return true;
		} catch (Exception $e) {
			$this->error = 'Возникла ошибка: model->Admin->defaultAddUser';
			echo 'Error: ' . $e->getMessage();
			return false;
		}
		return false;
	}

	public function countBuyLogs()
	{
		$sql = DB::run('SELECT `id` FROM `ez_buy_logs`')->rowCount();
		return $sql;
	}

	public function str_obr($str, $strMax, $strNew)
	{
		if (mb_strlen($str) > $strMax) {
			$str = mb_substr($str, 0, $strNew) . ' ...';
		}
		return $str;
	}
}