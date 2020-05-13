<?php 
namespace app\controllers;

// базовый контроллер подключается для всех контроллеров
use app\core\Controller;

use app\core\Config;
use app\models\Main;
use app\lib\DB;

require_once 'app/models/Sendmailer.php';
use app\models\Sendmailer;

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
		if ( !empty($_POST) ) 
		{
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
			'shops'			=> $this->model->htmlSelectShops(),
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
		if ( !empty($_POST) ) 
		{
			if ( !$this->model->buyerDataUpdate($_POST) ) {
				$this->view->message('error', $this->model->error);
			}
			// $this->view->reload();
			$this->view->message('success', 'save ok');
		}
		$vars = [
			'buyers' 		=> $this->model->getBuyers(),
			'allServers'	=> $this->model->getAllServers(),
			'allPrivileges'	=> $this->model->getAllPrivileges(),
		];
		$this->view->render($this->SITE_NAME . ' - Покупатели', $vars);
	}

	public function successAction()
	{
		if ( !empty($_POST) ) 
		{
			$data = $this->model->paySuccess($_POST);

			if ( $data['core_id'] == 'unban' ) {
				$this->view->redirect($this->SITE_URL . 'bans/ban' . $data['pay_id']);
			}
			
			$vars = [
				'data' => $this->model->paySuccess($_POST),
			];
			$this->view->render(Config::get('NAME') . ' - Success', $vars);
		} elseif ( !empty($_GET) ) 
		{
			$data = $this->model->paySuccess($_GET);

			if ( $data['core_id'] == 'unban' ) {
				$this->view->redirect($this->SITE_URL . 'bans/ban' . $data['pay_id']);
			}

			$vars = [
				'data' => $this->model->paySuccess($_GET),
			];
			$this->view->render(Config::get('NAME') . ' - Success', $vars);
		}
		else {
			$this->view->redirect($this->SITE_URL);
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