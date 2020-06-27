<?php 
namespace app\models;

use app\core\Model;
use app\core\Config;

use app\lib\DB;
use PDO;

use app\models\Servers;

class Pagination extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	// BANS
	public function allGetRows($server = false, $controller)
	{		
		if ( $server !== false ) 
		{
			$SERVER = new Servers;
			$server_ip = $SERVER->getServerIpById($server);

			if ( $controller == 'bans' ) {
				return DB::run("
					SELECT * FROM `{$this->DB['prefix']}_bans` 
					WHERE `server_ip` = ?", [ $server_ip ])->rowCount();
			}
		}

		if ( $controller == 'bans' ) {
			return DB::run("SELECT * FROM `{$this->DB['prefix']}_bans`")->rowCount();
		}
	}
	public function allGetListDb($offset, $limit, $server = false, $controller)
	{
		if ( $server !== false ) {
			$server_ip = $SERVER->getServerIpById($server);

			return DB::run("
				SELECT * FROM `{$this->DB['prefix']}_bans` WHERE `server_ip` = ? ORDER BY `bid` DESC LIMIT $offset, $limit
				", [ $server_ip ])->fetchAll();
		}

		return DB::run("SELECT * FROM `{$this->DB['prefix']}_bans` ORDER BY `bid` DESC LIMIT $offset, $limit")->fetchAll();
	}
	public function allGetList($page = false, $server = false, $controller)
	{
		if ( $page === false || $page == 0 ) {
			$page = 1;
		}

		$this->limit = 10;
		$count = $this->allGetRows($server, $controller);
		$this->total_pages = ceil( $count / $this->limit );
		$this->offset = ($page -1) * $this->limit;

		$answer = $this->allGetListDb($this->offset, $this->limit, $server, $controller);

		return [
			'answer' 	=> $answer,
			'count'		=> $this->total_pages,
			'page'		=> $page,
			'server'	=> $server,
			'countRows'	=> $count,
		];
	}

	// BUYERS
	public function buyersGetAllRows($server = false)
	{
		if ( $server !== false ) {
			return DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` 
				JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t1`.`id` = `t2`.`admin_id` AND `t2`.`server_id` = ?", [ $server ])->rowCount();
		}

		return DB::run("
			SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` 
			JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
			WHERE `t1`.`id` = `t2`.`admin_id`")->rowCount();
	}

	public function buyersGetListDb($offset, $limit, $server = false)
	{
		if ( $server !== false ) {
			return DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` 
				JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t1`.`id` = `t2`.`admin_id` AND `t2`.`server_id` = ?
				LIMIT $offset, $limit", [ $server ])->fetchAll();
		}

		return DB::run("
			SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` 
			JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
			WHERE `t1`.`id` = `t2`.`admin_id`
			LIMIT $offset, $limit")->fetchAll();
	}

	public function buyersGetList($page = false, $server = false)
	{
		if ( $page === false || $page == 0 ) {
			$page = 1;
		}

		$this->limit = 10;
		$count = $this->buyersGetAllRows($server);
		$this->total_pages = ceil( $count / $this->limit );
		$this->offset = ($page -1) * $this->limit;

		$answer = $this->buyersGetListDb($this->offset, $this->limit, $server);

		return [
			'answer' 	=> $answer,
			'count'		=> $this->total_pages,
			'page'		=> $page,
			'server'	=> $server,
			'countRows'	=> $count,
		];
	}

	// SEARCH BUYERS
	public function searchBuyersGetAllRows($searchData, $server = false)
	{
		if ( $server !== false ) {
			return DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` 
				JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t1`.`username` LIKE '%{$searchData}%' 
				AND `t1`.`id` = `t2`.`admin_id` AND `t2`.`server_id` = ?", [ $server ])->rowCount();
		}

		return DB::run("
			SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` 
			JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
			WHERE `t1`.`username` LIKE '%{$searchData}%' 
			AND `t1`.`id` = `t2`.`admin_id`")->rowCount();
	}

	public function searchBuyersGetListDb($searchData, $offset, $limit, $server = false)
	{
		if ( $server !== false ) {
			return DB::run("
				SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` 
				JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
				WHERE `t1`.`username` LIKE '%{$searchData}%' 
				AND `t1`.`id` = `t2`.`admin_id` AND `t2`.`server_id` = ?
				LIMIT $offset, $limit", [ $server ])->fetchAll();
		}

		return DB::run("
			SELECT * FROM `{$this->DB['prefix']}_amxadmins` `t1` 
			JOIN `{$this->DB['prefix']}_admins_servers` `t2` 
			WHERE `t1`.`username` LIKE '%{$searchData}%' 
			AND `t1`.`id` = `t2`.`admin_id`
			LIMIT $offset, $limit")->fetchAll();
	}

	public function searchBuyersGetList($page = false, $searchData, $server = false)
	{
		if ( $page === false || $page == 0 ) {
			$page = 1;
		}

		$this->limit = 5;
		$count = $this->searchBuyersGetAllRows($searchData, $server);
		$this->total_pages = ceil( $count / $this->limit );
		$this->offset = ($page -1) * $this->limit;

		$answer = $this->searchBuyersGetListDb($searchData, $this->offset, $this->limit, $server);

		return [
			'answer' 	=> $answer,
			'count'		=> $this->total_pages,
			'page'		=> $page,
			'server'	=> $server,
			'countRows'	=> $count,
			
			'searchData'=> htmlspecialchars($searchData),
		];
	}


}