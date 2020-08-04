<?php 
namespace app\controllers;

use app\core\Controller;
use app\core\Config;
use app\core\View;

use app\models\Main;
use app\models\Servers;

class AccountController extends Controller
{
	public $arr = [];
	public $user_data = [];
	public $MAIN;
	
	public function __construct($route) 
	{
		parent::__construct($route);

		if ( isset($_SESSION['authorization']) ) {
			$this->arr = $_SESSION['account'];
			if ( !$this->user_data = $this->model->getUserData($_SESSION['account']['username'], $_SESSION['account']['password']) ) {
				if ( $this->model->authorizationExit($_SESSION['account']['id']) ) {
					$this->view->location($this->SITE_URL);
				} else {
					die("Error: user not found. send mail to admin. or go to home page\n");
				}
			}
		}

		if ( 
			$route['action'] == 'update' && $this->model->getTariffNameById($this->user_data['tarif_id']) == 'unknown' ||
			$route['action'] == 'change' && $this->model->getTariffNameById($this->user_data['tarif_id']) == 'unknown'
		) {
			$this->view->redirect($this->SITE_URL . 'account');
		}

		if ($route['action'] == 'update' && $this->user_data['expired'] == 0) {
			$this->view->redirect($this->SITE_URL . 'account');
		}

		if ( $route['controller'] == 'account' && $route['action'] == 'login' ) {
			$this->view->layout = 'accountLogin';
		}
		if ( 
			$route['action'] == 'profile' || 
			$route['action'] == 'edit' || 
			$route['action'] == 'change' || 
			$route['action'] == 'buy' ||
			$route['action'] == 'update'
		) {
			$this->view->layout = 'account';
		}
		// $this->view->layout = 'account';

		$this->MAIN = new Main;
	}

	public function indexAction()
	{
		// редирект
		$this->view->redirect($this->SITE_URL . 'account/login');

		// $this->view->render(Config::get('NAME') . ' - Главная');
	}

	public function loginAction()
	{
		if ( isset($_SESSION['account']) ) {
			$this->view->redirect( $this->SITE_URL . 'account/profile/' . $_SESSION['account']['id']);
		}
		if ( !empty($_POST) ) {
			if ( !$this->model->authorization($_POST) ) {
				$this->view->message('error', $this->model->error);
			}
			$this->view->location($this->SITE_URL . 'account/profile/' . $_SESSION['account']['id']);
		}

		$vars = [
			'servers' => $this->MAIN->getAllServers(),
		];
		$this->view->render(Config::get('NAME') . ' - Авторизация', $vars);
	}

	public function profileAction()
	{
		if ( !isset($_SESSION['account']) ) {
			$this->view->redirect($this->SITE_URL . 'account/login');
		}

		$vars = [
			'server'	=> $this->model->getServerNameById($this->user_data['server_id']),
			'tariff'	=> $this->model->getTariffNameById($this->user_data['tarif_id']),
			'myserver' 	=> $this->model->getServerDataById($this->user_data['server_id']),
			'user_data' => $this->user_data,
		];
		$this->view->render($_SESSION['account']['username'], $vars);
	}

	public function editAction()
	{
		if ( !isset($_SESSION['account']) ) {
			$this->view->redirect($this->SITE_URL . 'account/login');
		}

		// срок привилегии истек
		if ( $this->user_data['expired'] != 0 && $this->user_data['expired'] < time() ) {
			$this->view->redirect($this->SITE_URL . 'account/profile/update');
		}

		// изменить данные
		if ( !empty($_POST) && isset($_POST['main-settings']) ) 
		{
			$SERVERS = new Servers;

			if ( $_POST['type'] == 'ac' ) {
				if ( !$SERVERS->checkSteamId($_POST['steamid']) ) {
					$this->view->message('error', 'Введите валидный SteamID или обратитесь к администрации');
				}
			}

			if ( !$this->model->changeSettings($_POST, $_SESSION['account']) ) {
				$this->view->message('error', $this->model->error);
			}
			$this->view->reload();
		}

		$vars = [
			'server'	=> $this->model->getServerNameById($this->user_data['server_id']),
			'tariff'	=> $this->model->getTariffNameById($this->user_data['tarif_id']),
			'myserver' 	=> $this->model->getServerDataById($this->user_data['server_id']),
			'user_data' => $this->user_data,
		];
		$this->view->render($_SESSION['account']['username'], $vars);
	}

	public function exitAction()
	{
		if( $this->model->authorizationExit($this->user_data['id']) ) {
			$this->view->redirect($this->SITE_URL);
		} else {
			die('Упс... Кажется у нас произошла ошибка выхода');
		}
	}

	public function changeAction()
	{
		if ( !isset($_SESSION['account']) ) {
			$this->view->redirect($this->SITE_URL . 'account/login');
		}

		if ( $this->user_data['tarif_id'] == 0 || $this->user_data == null ) {
			$this->view->redirect($this->SITE_URL . 'account');
		}

		if ( $this->user_data['expired'] == 0 || $this->user_data['expired'] < $this->time ) {
			$this->view->redirect($this->SITE_URL . 'account');
		}

		// срок привилегии истек
		if ( $_SESSION['account']['expired'] != 0 && $this->user_data['expired'] < time() ) {
			$this->view->redirect($this->SITE_URL . 'account/profile/update');
		}

		if ( !empty($_POST) ) {
			if ( $_POST['hidden'] == 0 ) {
				$this->view->message('error', 'Выберите привилегию');
				return false;
			}
			if ( $_SESSION['changePrivilege']['sqlTarif'] == false ) {
				$this->view->message('error', 'В тарифе должен быть выбор <b>30ти</b> дней!');
				return false;
			}
			if ( $_SESSION['changePrivilege']['daysLeftInDays'] < 1 ) {
				$this->view->message('error', 'Недостаточно средств на псевдо балансе для этой привилегии.<br>Вы можете продлить текущую привелегию в ЛК или купить другую на <a href="'.$this->SITE_URL.'">главной странице</a>.');
				return false;
			}
			if ( !$this->model->changePrivilege($_SESSION['changePrivilege'], $_SESSION['account']['id']) ) {
				$this->view->message('error', /*'Возникла ошибка, обратитесь к администрации!'*/$this->model->error);
				return false;
			}
			$this->view->location($this->SITE_URL . 'account/profile/change');
		}

		$vars = [
			'user_data' 	=> $this->user_data,
			'currentInfo'	=> $this->model->getInfoCurrentPrivilege($this->user_data['expired'], $this->user_data['tarif_id']),
			'server'		=> $this->model->getServerNameById($this->user_data['server_id']),
			'tariff'		=> $this->model->getTariffNameById($this->user_data['tarif_id']),
			'myserver' 		=> $this->model->getServerDataById($this->user_data['server_id']),
		];
		$this->view->render($_SESSION['account']['username'], $vars);
	}

	public function buyAction()
	{
		if ( !isset($_SESSION['account']) ) {
			$this->view->redirect($this->SITE_URL .'account/login');
		}

		if ( !empty($_POST) ) 
		{
			// промокод
			if ( isset($_POST['thisPromoCode']) ) 
			{
				if ( !$this->MAIN->checkStatusPromocode($_POST) ) {
					$this->view->message('error', $this->MAIN->error);
				}
				$this->view->message('success', 'Промокод успешно активирован!');
			}

			if ( !isset($_POST['server']) ) {
				$this->view->message('error', 'Выберите сервер!');
			}

			$this->view->location(strval($this->model->authorizedBuy( $_POST, $_SESSION['account'] )));
		}

		$vars = [
			'user_data' => $this->user_data,
			'servers' 	=> $this->MAIN->getAllServers(),
			'server'	=> $this->model->getServerNameById($this->user_data['server_id']),
			'tariff'	=> $this->model->getTariffNameById($this->user_data['tarif_id']),
			'myserver' 	=> $this->model->getServerDataById($this->user_data['server_id']),
			'promo_ser'	=> $this->MAIN->getAllServers(),
		];
		$this->view->render($_SESSION['account']['username'], $vars);
	}

	public function updateAction()
	{
		if ( !empty($_POST) ) 
		{
			$user = ( $this->user_data['flags'] == 'ac' ) ? $user = $this->user_data['steamid'] : $user = $this->user_data['nickname'];
			
			$post = [
				'type' => $this->user_data['flags'],
				'email' => $this->user_data['email'],
				'server' => $this->user_data['server_id'],
				'privilege' => $this->user_data['tarif_id'],
				'days' => $_POST['days'],
				'vk' => $this->user_data['vk'],
				'password' => $this->user_data['password'],
				'shop' => $_POST['shop'],
			];
			$this->view->location(strval($this->MAIN->letsGoPay($post, $user,  $_POST['updateUserTime'])));
		}
		$vars = [
			'user_data' => $this->user_data,
			'server'	=> $this->model->getServerNameById($this->user_data['server_id']),
			'tariff'	=> $this->model->getTariffNameById($this->user_data['tarif_id']),
			'myserver' 	=> $this->model->getServerDataById($this->user_data['server_id']),
			'pTime'		=> $this->model->getPrivilegeTime($this->user_data['tarif_id'], $this->user_data['server_id']),
			'htmlShops' => $this->MAIN->htmlSelectShops(),
			'disc'		=> Config::get('DISC'),
		];
		$this->view->render($_SESSION['account']['username'], $vars);
	}

	public function resetAction()
	{
		if ( !empty($_POST) ) 
		{
			if ( strlen($_POST['email']) < 3 || strlen($_POST['email']) > 30 ) {
				$this->view->message('error', 'Длина адреса почты должна быть от 3 до 30 символов');
				return false;
			}
			if ( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ) {
				$this->view->message('error', 'Адрес почты указан не верно');
				return false;
			}
			if ( !$this->model->resetPassword($_POST['email']) ) {
				$this->view->message('error', $this->model->error);
			}
			$this->view->message('success', 'Запрос на восстановление отправлен на Email');
		}
		$this->view->render($this->SITE_NAME . ' — Сброс пароля');
	}
}