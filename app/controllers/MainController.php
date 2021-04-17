<?php 
namespace app\controllers;

// базовый контроллер подключается для всех контроллеров
use app\core\Controller;

use app\core\Config;
use app\models\Main;
use app\lib\DB;

require_once 'app/models/Sendmailer.php';
use app\models\Sendmailer;

use app\models\Pagination;// remove
use app\models\Paginator;
use app\models\Servers;

class MainController extends Controller
{
	public function indexAction()
	{
		// редирект
		$this->view->redirect($this->SITE_URL . 'buy');
		$this->view->render(Config::get('NAME') . ' - Главная страница');
	}

	public function buyAction()
	{
		$SERVERS = new Servers;

		if ( !empty($_POST) ) 
		{
			if ( isset($_POST['type']) && $_POST['type'] == 'ac' ) {
				if ( !$SERVERS->checkSteamId($_POST['steamid']) ) {
					$this->view->message('error', 'Введите валидный SteamID или обратитесь к администрации');
				}
			}
			// промокод
			if ( isset($_POST['thisPromoCode']) ) 
			{
				if ( !$this->model->checkStatusPromocode($_POST) ) {
					$this->view->message('error', $this->model->error);
				}
				$this->view->message('success', 'Промокод успешно активирован!');
			}

			// проверка формы покупки
			if ( !$this->model->checkMainBuyForm($_POST) ) 
			{
				$this->view->message('error', $this->model->error);
			}

			$user = ( $_POST['type'] == 'ac' ) ? $user = $_POST['steamid'] : $user = $_POST['nickname'];

			$this->view->location(strval($this->model->letsGoPay($_POST, $user)));
		}
		$vars = [
			'url'			=> Config::get('SITEURL'),
			'urlRules'		=> Config::get('RULES_URL'),
			'disc'			=> Config::get('DISC'),
			'servers'		=> $this->model->getAllServers(),
			'serversPromo'	=> $this->model->getAllServers(),
			'countServers'	=> $this->model->getCountServers(),
			'htmlShops'		=> $this->model->htmlSelectShops(),
			'infoBlock'		=> Config::get('TEXT')['info_block'],
		];
		$this->view->render(Config::get('NAME') . ' - Покупка', $vars);
	}

	public function buyersAction()
	{
		$SERVERS = new Servers;

		// class Paginator
		$p_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$p_perPage = 10;
		$request = $this->model->sqlRequest($_GET, $p_page, $p_perPage);
		$p_total = $request['total'];
		$p_start = $request['start'];

		$P_PAGINATOR = new Paginator($p_page, $p_perPage, $p_total);

		if ( $p_page >= 2 && $p_total < $p_perPage ) {
			$this->view->redirect($this->SITEURL . 'buyers?page=1');
		}

		if ( !empty($_POST) && isset($_SESSION['admin']) ) 
		{
			if ( isset($_POST['deleteUser']) ) {
				if ( !$this->model->deleteUser($_POST['deleteUser']) ) {
					$this->view->message('error', $this->model->error);
				}
				$this->view->reload();
			}
			if ( !$this->model->buyerDataUpdate($_POST) ) {
				$this->view->message('error', $this->model->error);
			}
			$this->view->reload();
		}

		$vars = [
			'buyers' 		=> $this->model->getBuyers(),
			'allServers'	=> $this->model->getAllServers(),
			'allPrivileges'	=> $this->model->getAllPrivileges(),
			'paginator'		=> $P_PAGINATOR,
			'data'			=> $request['sql'],
			'dataTotalRows'	=> $p_total,
		];
		$this->view->render($this->SITE_NAME . ' - Покупатели', $vars);
	}

	public function successAction()
	{
		if ( !empty($_REQUEST) ) 
		{
			$data = $this->model->paySuccess($_REQUEST);

			if ( $data['core_id'] === false || $data['core_id'] == 'unban' ) {
				$this->view->redirect($this->SITE_URL . 'bans/ban' . $data['pay_id']);
			}
			
			$vars = [
				'data' => $this->model->paySuccess($_REQUEST),
			];
			$this->view->render(Config::get('NAME') . ' - Success', $vars);
		}
	}

	public function errorAction()
	{
		if ( !empty($_POST) ) 
		{
			$vars = [
				'pay_id'	=> $this->model->payError($_POST),
				'url'		=> Config::get('SITEURL'),
			];
			$this->view->render($this->SITE_NAME . ' - Error', $vars);
		} elseif ( !empty($_GET) ) {
			$vars = [
				'pay_id'	=> $this->model->payError($_GET),
				'url'		=> Config::get('SITEURL'),
			];
			$this->view->render($this->SITE_NAME . ' - Error', $vars);
		}
		else {
			$this->view->redirect($this->SITE_URL);
		}
	}

	public function supportAction()
	{
		$MAILER = new Sendmailer;
		
		if ( !empty($_POST) ) 
		{
			if ( isset($_SESSION['support_messages']) ) {
				$this->view->message('error', 'Вы уже отправили писмо, дождитесь ответа!');
			}

			if ( mb_strlen($_POST['message']) < 10 || mb_strlen($_POST['message']) > 200 ) {
				$this->view->message('error', 'Сообщение должно быть от 10 до 200 символов! Сейчас: ' . mb_strlen($_POST['message']));
			}
			if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				$this->view->message('error', 'Введите настоящий Email!');
			}

			if ( !$MAILER->supportMessage($_POST) ) {
				$this->view->message('error', $MAILER->error);
			}
			$this->view->message('success', 'Сообщение успешно отправлено!');
		}

		$vars = [
			'vkGroup'	=> Config::get('VK_GROUP'),
			'vkGroupApiId' => Config::get('VK_API_ID'),
		];

		$this->view->render($this->SITE_NAME . ' - Support', $vars);
	}

	public function cronAction()
	{
		if ( Config::get('WEB_SERVER_IP') != $_SERVER['SERVER_ADDR'] ) {
			die('bad server ip');
		}
		$this->model->runCron();
	}
}
