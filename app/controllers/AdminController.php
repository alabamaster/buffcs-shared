<?php 
namespace app\controllers;

// базовый контроллер подключается для всех контроллеров
use app\core\Controller;
use app\core\Config;
use app\core\View;

use app\models\Main;
use app\models\Privileges;

class AdminController extends Controller
{
	public  function indexAction()
	{
		// редирект
		$this->view->redirect($this->SITE_URL . 'admin/login');

		$this->view->render($this->SITE_NAME . ' - ACP');
	}

	public function loginAction()
	{
		if ( isset($_SESSION['admin']) ) $this->view->redirect($this->SITE_URL . 'admin/home');

		if ( !empty($_POST['username']) && !empty($_POST['password']) )
		{
			if ( !$this->model->checkDataLogin($_POST) ) 
			{
				$this->view->message('error', $this->model->error);
			}
			// $this->view->message('ok', 'vse oke');
			$this->model->sessionGo();
			// $_SESSION['admin'] = true;
			// $this->view->redirect('/admin/home');
			$this->view->location($this->SITE_URL . 'admin/home');
		}
		$this->view->render($this->SITE_NAME . ' - Login ACP');
	}

	public function homeAction()
	{
		if ( !isset($_SESSION['admin']) ) {
			$this->view->redirect($this->SITE_URL . 'admin/login');
		}
		$vars = [
			'stats' 		=> $this->model->sideBlockStats(),
			'model'			=> $this->model,
			'logs'			=> $this->model->viewBuyLogs(),
			'countLogs'		=> $this->model->countBuyLogs(),
		];
		$this->view->render($this->SITE_NAME . ' - Home ACP', $vars);
	}

	public function addprivilegesAction()
	{
		if ( !isset($_SESSION['admin']) ) {
			$this->view->redirect($this->SITE_URL . 'admin/login');
		}

		$C_PRIVILEGES = new Privileges;

		if ( !empty($_POST) ) 
		{
			if( isset($_POST['deleteAllPrivileges']) && $_POST['deleteAllPrivileges'] == 1 )
			{
				if ( !$C_PRIVILEGES->deleteAllPrivileges() ) {
					$this->view->message('error', $C_PRIVILEGES->error);
				}
				$this->view->location('/admin/addprivileges');
				// $this->view->reload(); // нужно добавить условие в JS
			}

			// удалить привилегию
			if ( isset($_POST['delete']) && $_POST['delete'] == 1 ) 
			{
				if ( !$C_PRIVILEGES->deletePrivilegeById($_POST['privilege_id'])  ) 
				{
					$this->view->message('error', $C_PRIVILEGES->error);
				}
				$this->view->location('/admin/addprivileges');
			}

			// изменить привилегию
			if ( isset($_POST['edit']) == 1 ) 
			{
				if ( !$C_PRIVILEGES->editPrivilege($_POST) ) 
				{
					$this->view->message('error', $C_PRIVILEGES->error);
				}
				$this->view->location('/admin/addprivileges');
			}

			if ( !$C_PRIVILEGES->validateAdd($_POST) ) {
				$this->view->message('error', $C_PRIVILEGES->error);
			}

			// работа с БД
			if ( !$C_PRIVILEGES->addprivilege($_POST, $_FILES) ) {
				$this->view->message('error', $C_PRIVILEGES->error);
			}
			$this->view->reload();
			// $this->view->message('success', 'ok');
		}

		$vars = [
			'model' => $this->model,
			'servers' => $this->model->getAllServers(),
			'privileges' => $this->model->getAllPrivileges(),
		];
		$this->view->render($this->SITE_NAME . ' - Add privileges', $vars);
	}

	public function adduserAction()
	{
		if ( !empty($_POST) ) 
		{
			$MAIN = new Main;

			$username = ($_POST['type'] == 'a') ? $_POST['nickname'] : $_POST['steamid'];

			if( 
				empty($username) || empty($_POST['password']) || empty($_POST['email']) || $_POST['server'] == 0 || 
				empty($_POST['access']) || $_POST['days'] == '' || empty($_POST['privilege'])
			) {
				$this->view->message('error', 'Заполните все поля с красной звёздочкой');
			}

			// проверка ника
			if ( $MAIN->userExist($_POST) ) {
				$this->view->message('error', 'Такой ник или стимайди уже занят');
			}
			// проверка почта
			if ( $MAIN->emailExist($_POST['email']) ) {
				$this->view->message('error', 'Такой Email уже занят');
			}
			// проверка вк
			if ( mb_strlen($_POST['vk']) > 0 ) {
				if ( $MAIN->vkExist($_POST['vk']) ) {
					$this->view->message('error', $MAIN->error);
				}
			}

			if( !$this->model->defaultAddUser($_POST) )
			{
				$this->view->message('error', $this->model->error);
			}
			$this->view->message('success', 'Игрок успешно добавлен в БД');
		}

		$vars = [
			'servers' => $this->model->getAllServers(),
		];
		$this->view->render($this->SITE_NAME . ' - Add user', $vars);
	}

	public function promoAction()
	{
		if ( !isset($_SESSION['admin']) ) {
			$this->view->redirect($this->SITE_URL . 'admin/login');
			$this->view->errorCode(403);
		}

		if (!empty($_POST)) {
			if ( isset($_POST['saveCode']) ) {
				if ( !$this->model->savePromoCode($_POST) ) {
					$this->view->message('error', $this->model->error);
				}
				$this->view->reload();
			}

			if ( $_POST['deleteCode'] == 1 ) {
				if ( !$this->model->deletePromoCode($_POST['code_id']) ) {
					$this->view->message('error', $this->model->error);
				}
				$this->view->reload();
			}
		}

		$vars = [
			'servers' => $this->model->getAllServers(),
			'codes' => $this->model->getAllPromocodes(),
			'model' => $this->model,
		];
		$this->view->render($this->SITE_NAME . ' - Promo', $vars);
	}

	public function infoprivilegesAction()
	{
		$C_PRIVILEGES = new Privileges;

		if ( !empty($_POST) ) 
		{
			if ( !$C_PRIVILEGES->saveAboutPrivilege($_POST) ) 
			{
				$this->view->message('error', $C_PRIVILEGES->error);
			}
			$this->view->reload();
		}

		$vars = [
			'servers' => $this->model->getAllServers(),
			'privileges' => $this->model->getAllPrivileges(),
		];
		$this->view->render($this->SITE_NAME . ' - Инфо привилегии', $vars);
	}

	public function amxadminsAction()
	{
		if ( !empty($_POST) ) 
		{
			// меняем флаги доступа
			if ( isset($_POST['changeAccess']) ) {
				if ( !$this->model->changeAccess($_POST) ) {
					$this->view->message('error', 'Ошибка обновления флагов доступа');
				}
				$this->view->reload();
			}

			if ( strlen($_POST['username']) < 3 || strlen($_POST['username']) > 30 ) {
				$this->view->message('error', 'Введите от 3 до 30 символов');
				return false;
			}
			if ( !$this->model->searchAmxadmins($_POST['username']) ) {
				$this->view->message('error', 'Игрок с такими данными не найден');
				return false;
			}

			return $this->view->adminAmxadmins($this->model->searchAmxadmins($_POST['username']));
		}
		$this->view->render($this->SITE_NAME . ' - Все игроки', $vars);
	}

	public function exitAction()
	{
		unset($_SESSION['admin']);
		$this->view->redirect($this->SITE_URL);
	}
}