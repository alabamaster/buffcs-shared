<?php
namespace app\controllers;

use app\core\Controller;
use app\models\Main;
use app\models\Servers;
use app\models\Pagination;
use app\models\Paginator;
use app\lib\DB;
use app\core\Config;

class BansController extends Controller
{
	public function indexAction()
	{
		// $PAGINATION = new Pagination;
		$SERVERS = new Servers;

		// class Paginator
		// разобраться здесь, тотал должен адоптироваться под гет параметры, поиска, и ид севрера
		$p_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$p_perPage = 10;
		$request = $this->model->sqlRequest($_GET, $p_page, $p_perPage);
		$p_total = $request['total'];
		$p_start = $request['start'];

		$P_PAGINATOR = new Paginator($p_page, $p_perPage, $p_total);
		// $p_data = $this->model->getData($_GET, $p_start, $p_perPage);
		$p_data = $request['sql'];

		$vars = [
			'allBans'		=> $this->model->getAllBans(),
			'model'			=> $this->model,
			'allServers'	=> $this->model->getAllServers(),
			'SERVERS'		=> $SERVERS,
			'count_serv'	=> $SERVERS->getCountAllServers(),
			'paginator'		=> $P_PAGINATOR,
			'data'			=> $p_data,
			'dataTotalRows'	=> $p_total,
		];
		$this->view->render(Config::get('NAME') . ' - Банлист', $vars);
	}

	public function banAction()
	{
		$SERVERS = new Servers;

		if ( !empty($_POST) )
		{
			if ( isset($_POST['buyUnban']) ) {
				$this->view->location(strval($this->model->goPayUnban($_POST)));
			}

			if ( $this->model->unbanPlayer($_POST['ban_id']) ) {
				$this->view->reload();
			}
		}

		$vars = [
			'data'		=> $this->model->getDataBan($this->route['id']),
			'model'		=> $this->model,
			'ban_cfg' 	=> Config::get('BANS'),
			'fk_active' => Config::get('FK')['active'],
			'rk_active' => Config::get('RK')['active'],
			'up_active'	=> Config::get('UP')['active'],
			'ban_price' => Config::get('BANS')['price'],
			'currency' 	=> Config::get('DISC')['currency'],
			'SERVERS'	=> $SERVERS,
		];
		$this->view->render(Config::get('NAME') . ' - Бан инфо', $vars);
	}
}
