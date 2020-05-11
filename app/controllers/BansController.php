<?php 
namespace app\controllers;

use app\core\Controller;
use app\models\Main;
use app\models\Servers;
use app\lib\DB;
use app\core\Config;

class BansController extends Controller
{
	public function indexAction()
	{
		$vars = [
			'allBans'		=> $this->model->getAllBans(),
			'model'			=> $this->model,
			'allServers'	=> $this->model->getAllServers(),
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
			'cfg'		=> Config::get('BANS'),
			'SERVERS'	=> $SERVERS,
		];
		$this->view->render(Config::get('NAME') . ' - Бан инфо', $vars);
	}
}