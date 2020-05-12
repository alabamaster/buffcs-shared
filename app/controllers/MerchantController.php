<?php 
namespace app\controllers;

use app\core\Controller;
use app\core\Config;
use app\core\View;

require_once 'app/merchantModels/Freekassa.php';
require_once 'app/merchantModels/Robokassa.php';
require_once 'app/merchantModels/Unitpay.php';
require_once 'app/lib/unitpay/UnitPay.php';

use app\merchantModels\Freekassa;
use app\merchantModels\Robokassa;
use app\merchantModels\UnitpayModel;
use app\lib\UnitPay;

class MerchantController extends Controller
{
	private $FK;
	private $RK;
	private $UP;

	function __construct()
	{
		$this->FK = new Freekassa;
		$this->RK = new Robokassa;
		$this->UP = new UnitpayModel;

		if ( isset($_SESSION['authorization']) ) {
			$this->arr = $_SESSION['account'];
			$this->user_data = $this->model->getUserData($_SESSION['account']['username'], $_SESSION['account']['password']);
		}
	}

	public function freekassaAction()
	{
		if ( !isset($_POST['us_core_id']) ) die('core id not found');

		switch ( $_POST['us_core_id'] ) 
		{
			case '1': // обычная покупка
				$this->FK->checkPay($_POST);
			break;

			case '2': // покупа авторизованного юзера
				$this->FK->checkAuthPay($_POST);
			break;

			case '3': // продление авторизованного юзера
				$this->FK->checkAuthPay($_POST);
			break;

			case 'unban':
				$this->FK->unBan($_POST);
			break;
		}
	}

	public function robokassaAction()
	{
		if ( !isset($_POST['shp_core_id']) ) die('core id not found');

		switch ( $_POST['shp_core_id'] ) 
		{
			case '1': // обычная покупка
				$this->RK->checkPay($_POST);
			break;

			case '2': // покупа авторизованного юзера
				$this->RK->checkAuthPay($_POST);
			break;

			case '3': // продление авторизованного юзера
				$this->RK->checkAuthPay($_POST);
			break;

			case 'unban':
				$this->FK->unBan($_POST);
			break;
		}
	}

	public function unitpayAction()
	{
		if ( $_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['params'])) {
			die('method error');
		}

		$get_core_id = explode('.', $_GET['params']['account']); // [0] - pay_id // [1] - core_id
		$core_id = 'up_core_id=' . $get_core_id[1];

		if ( isset($up_core_id) ) die('core id not found');

		$unitPay = new UnitPay(Config::get('UP')['domain'], Config::get('UP')['secretKey']);
		try {
			// Validate request (check ip address, signature and etc)
			$unitPay->checkHandlerRequest();

			list($method, $params) = array($_GET['method'], $_GET['params']);
			switch ($method) 
			{
				// Just check order (check server status, check order in DB and etc)
				case 'check':
					print $unitPay->getSuccessHandlerResponse('Check Success. Ready to pay.');
				break;

				// Method Pay means that the money received
				case 'pay':
					// Please complete order
					switch ( $get_core_id[1] )
					{
						case 1: // обычная покупка
							$this->UP->checkPay($_GET, $core_id);
						break;

						case 2: // покупа авторизованного юзера
							$this->UP->checkAuthPay($_GET, $core_id);
						break;

						case 3: // продление авторизованного юзера
							$this->UP->checkAuthPay($_GET, $core_id);
						break;
					}

					print $unitPay->getSuccessHandlerResponse('Pay Success');
				break;

				// Method Error means that an error has occurred.
				case 'error':
					// Please log error text.
					print $unitPay->getSuccessHandlerResponse('Error logged');
				break;

				// Method Refund means that the money returned to the client
				case 'refund':
					// Please cancel the order
					print $unitPay->getSuccessHandlerResponse('Order canceled');
				break;
			}
		// Oops! Something went wrong.
		} catch (Exception $e) {
			print $unitPay->getErrorHandlerResponse($e->getMessage());
		}
	}

	public function interkassaAction()
	{
		die('ok');
		if ( !isset($_POST['us_core_id']) ) die('core id not found');

		switch ( $_POST['us_core_id'] ) 
		{
			case '1': // обычная покупка
				$this->FK->checkPay($_POST);
			break;

			case '2': // покупа авторизованного юзера
				$this->FK->checkAuthPay($_POST);
			break;

			case '3': // продление авторизованного юзера
				$this->FK->checkAuthPay($_POST);
			break;
		}
	}

	public function webmoneyAction()
	{
		die('this webmoney action');
	}

	public function qiwiAction()
	{
		die('this qiwi action');
	}
	public function yandexmoneyAction()
	{
		die('this yandexmoney action');
	}
}