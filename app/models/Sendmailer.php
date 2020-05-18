<?php 
namespace app\models;

require_once 'app/lib/PHPMailer/Exception.php';
require_once 'app/lib/PHPMailer/PHPMailer.php';
require_once 'app/lib/PHPMailer/SMTP.php';
require_once 'app/models/Main.php';
require_once 'app/core/Model.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

use app\core\Model;
use app\core\Config;
use app\lib\DB;
use PDO;
use app\models\Main;

class Sendmailer extends Model
{
	public $MAILER;
	public $MAIN_MODEL;

	public function __construct()
	{
		parent::__construct();

		$this->MAILER = new PHPMailer(true);
		$this->MAIN_MODEL = new Main;
	}

	public function supportMessage($post)
	{
		try {
			//Server settings
			$this->MAILER->CharSet = 'UTF-8';
			// $this->MAILER->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
			$this->MAILER->isSMTP(); // Send using SMTP
			$this->MAILER->Host       = Config::get('SMTP')['host']; // Set the SMTP server to send through
			$this->MAILER->SMTPAuth   = true; // Enable SMTP authentication
			$this->MAILER->Username   = Config::get('SMTP')['username']; // SMTP username
			$this->MAILER->Password   = Config::get('SMTP')['password']; // SMTP password
			$this->MAILER->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
			$this->MAILER->Port       = Config::get('SMTP')['port']; // TCP port to connect to

			//Recipients
			$this->MAILER->setFrom(Config::get('SMTP')['reply'], Config::get('NAME'));
			$this->MAILER->addAddress(Config::get('SMTP')['reply'], Config::get('NAME')); // Add a recipient
			$this->MAILER->addReplyTo($post['email'], 'Information');

			// Content
			$this->MAILER->isHTML(true); // Set email format to HTML
			$this->MAILER->Subject = Config::get('NAME') . ' — Support message';
			$this->MAILER->Body    = htmlspecialchars($post['message']) . '<br>Social link: ' . htmlspecialchars($post['socialLink']);
			$this->MAILER->AltBody = 'Откройте письмо в браузере с поддержкой HTML';

			$this->MAILER->send();
			// echo 'Message has been sent';
			// $_SESSION['support_messages'] = true;
			return true;
		} catch (Exception $e) {
			$this->error = "Message could not be sent. Mailer Error: {$this->MAILER->ErrorInfo}";
			// $this->error = $this->MAILER->ErrorInfo;
			return false;
		}
	}

	public function newPaySuccessMessage($pay_id)
	{
		$query = DB::run('SELECT * FROM `ez_buy_logs` WHERE `id` = ?', [ $pay_id ])->fetch(PDO::FETCH_ASSOC);

		try {
			if ( !$query ) {
				// $this->error = 'Ошибка при получении информации, письмо не было отправлено!';
				throw new Exception("Error receiving information, the letter was not sent!\n");
				return false;
			}
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}

		if ( $query['type'] == 'a' ) {
			$user_type = 'ник + пароль';
			$user_name = htmlspecialchars($query['nickname']);
		} else {
			$user_type = 'SteamID + пароль';
			$user_name = htmlspecialchars($query['steamid']);
		}

		$days = ($query['days'] == 0) ? $days = 'бесконечное ( ͡◉ ͜ʖ ͡◉)' : $days = $query['days'] . ' дн.';
		$vk = ($query['vk'] == null) ? $vk = 'нет' : '<a href="'.$query['vk'].'">'.htmlspecialchars($query['vk']).'</a>';

		$values = [
			'server' 	=> $this->MAIN_MODEL->getServerNameById($query['sid']),
			'user_type' => $user_type,
			'user_name' => $user_name,
			'password' 	=> htmlspecialchars($query['password']),
			'tariff' 	=> $this->MAIN_MODEL->getPrivilegeNameById($query['pid']),
			'days' 		=> $days,
			'vk' 		=> $vk,
			'email' 	=> $query['email'],
		];

		try {
			//Server settings
			$this->MAILER->CharSet = 'UTF-8';
			// $this->MAILER->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
			$this->MAILER->isSMTP(); // Send using SMTP
			$this->MAILER->Host       = Config::get('SMTP')['host']; // Set the SMTP server to send through
			$this->MAILER->SMTPAuth   = true; // Enable SMTP authentication
			$this->MAILER->Username   = Config::get('SMTP')['username']; // SMTP username
			$this->MAILER->Password   = Config::get('SMTP')['password']; // SMTP password
			$this->MAILER->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
			$this->MAILER->Port       = Config::get('SMTP')['port']; // TCP port to connect to

			//Recipients
			$this->MAILER->setFrom(Config::get('SMTP')['from'], Config::get('NAME'));
			$this->MAILER->addAddress($values['email'], Config::get('NAME')); // Add a recipient
			$this->MAILER->addReplyTo(Config::get('SMTP')['reply'], 'Information');

			// Content
			$this->MAILER->isHTML(true); // Set email format to HTML
			$this->MAILER->Subject = Config::get('NAME') . ' — Оплата прошла успешно!';
			$this->MAILER->Body    = '
				<div style="font-size:15px;background-color:#fff;border:2px solid #c7c7c7;border-radius:2px;padding:20px;line-height: 1.5;font-weight: 400;">
					⭐ Вы купили привилегию на сервере: <b>'.$values['server'].'</b> 🕹️<br>
					💀 Тип услуги: <b>'.$values['user_type'].'</b><br>
					🔪 Ваш никнейм: <b>'.$values['user_name'].'</b><br>
					🔑 Ваш пароль: <b>'.$values['password'].'</b><br>
					🔥 Привилегия: <b>'.$values['tariff'].'</b><br>
					📅 Кол-во дней: <b>'.$values['days'].'</b><br>
					- Ссылка на ВК: <b>'.$values['vk'].'</b>
					<hr>
					❤️ Спасибо за покупку! ( ͡ᵔ ͜ʖ ͡ᵔ )
				</div>';
			$this->MAILER->AltBody = 'Откройте письмо в браузере с поддержкой HTML';

			$this->MAILER->send();
			// echo "Message has been sent\n";
			return true;
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$this->MAILER->ErrorInfo}";
			return false;
		}
	}

	public function cronMessage()
	{
		$minus3days = $this->time - 86400 * 3; // 3days // минус 3 дня от текущей даты
		$plus3days = $this->time + 86400 * 3; // 3days // плюс 3 дня к текущей дате

		$sql = DB::run('SELECT `nickname`, `steamid`, `flags`, `created`, `expired`, `tarif_id`, `server_id`, `email` FROM `'.$this->DB['prefix'].'_amxadmins` `t1` JOIN `'.$this->DB['prefix'].'_admins_servers` `t2` WHERE (`expired` <= ? AND `expired` != 0 AND `expired` >= ?) AND `t1`.`id` = `t2`.`admin_id`', [ $plus3days, $this->time ])->fetchAll();

		if ( !empty($sql) ) 
		{
			$countMessages = 0;
			foreach ($sql as $row) 
			{
				if ( $row['flags'] == 'a' ) {
					$user_type = 'ник';
					$user_name = htmlspecialchars($row['nickname']);
				} else {
					$user_type = 'SteamID';
					$user_name = htmlspecialchars($row['steamid']);
				}
				$values = [
					'user_name' => $user_name,
					'user_type' => $user_type,
					'created'	=> date('d.m.Y в H:i', $row['created']),
					'expired'	=> date('d.m.Y в H:i', $row['expired']),
					'privilege'	=> $this->MAIN_MODEL->getPrivilegeNameById($row['tarif_id']),
					'server'	=> $this->MAIN_MODEL->getServerNameById($row['server_id']),
				];
				try {
					//Server settings
					$this->MAILER->CharSet = 'UTF-8';
					// $this->MAILER->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
					$this->MAILER->isSMTP(); // Send using SMTP
					$this->MAILER->Host       = Config::get('SMTP')['host']; // Set the SMTP server to send through
					$this->MAILER->SMTPAuth   = true; // Enable SMTP authentication
					$this->MAILER->Username   = Config::get('SMTP')['username']; // SMTP username
					$this->MAILER->Password   = Config::get('SMTP')['password']; // SMTP password
					$this->MAILER->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
					$this->MAILER->Port       = Config::get('SMTP')['port']; // TCP port to connect to

					//Recipients
					$this->MAILER->setFrom(Config::get('SMTP')['from'], Config::get('NAME'));
					$this->MAILER->addAddress($row['email'], Config::get('NAME')); // Add a recipient
					$this->MAILER->addReplyTo(Config::get('SMTP')['reply'], 'Information');

					// Content
					$this->MAILER->isHTML(true); // Set email format to HTML
					$this->MAILER->Subject = Config::get('NAME') . ' — ✉ Окончание привилегии ⛔';
					$this->MAILER->Body    = "
	<div style='font-size:15px;background-color:#fff;border:2px solid #c7c7c7;border-radius:2px;padding:20px;line-height: 1.5;font-weight: 400;'>
		<p style='margin:0;'>
			<b style='color:#e00000;'>Срок ваших привилегий подходит к концу!</b><hr>
			👤 Ваш {$values['user_type']}: <b>{$values['user_name']}</b><br>
			📅 Дата покупки: <b>{$values['created']}</b><br>
			📆 Дата окончания: <b>{$values['expired']}</b><br>
			🕹️ Сервер: <b>{$values['server']}</b><br>
			⚡ Привилегия: <b>{$values['privilege']}</b>
			<hr>
			⚠ Продлить привилегию или купить новую, Вы можете в <a href='".Config::get('SITEURL')."account/login'>личном кабинете</a>!
		</p>
	</div>";
					$this->MAILER->AltBody = 'Откройте письмо в браузере с поддержкой HTML';

					$this->MAILER->send();
					// echo 'Message has been sent';
				} catch (Exception $e) {
					$this->error = "Message could not be sent. Mailer Error: {$this->MAILER->ErrorInfo}";
					// $this->error = $this->MAILER->ErrorInfo;
					return false;
				}
				$countMessages++;
			}
			echo 'Number of messages sent: ' . $countMessages . "\n";
			return true;
		} else {
			echo 'Cron result: 0';
		}
	}

	public function resetPasswordMessage($email, $newpass)
	{
		try {
			//Server settings
			$this->MAILER->CharSet = 'UTF-8';
			// $this->MAILER->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
			$this->MAILER->isSMTP(); // Send using SMTP
			$this->MAILER->Host       = Config::get('SMTP')['host']; // Set the SMTP server to send through
			$this->MAILER->SMTPAuth   = true; // Enable SMTP authentication
			$this->MAILER->Username   = Config::get('SMTP')['username']; // SMTP username
			$this->MAILER->Password   = Config::get('SMTP')['password']; // SMTP password
			$this->MAILER->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
			$this->MAILER->Port       = Config::get('SMTP')['port']; // TCP port to connect to

			//Recipients
			$this->MAILER->setFrom(Config::get('SMTP')['from'], Config::get('NAME'));
			$this->MAILER->addAddress($email, Config::get('NAME')); // Add a recipient
			$this->MAILER->addReplyTo(Config::get('SMTP')['reply'], 'Information');

			// Content
			$this->MAILER->isHTML(true); // Set email format to HTML
			$this->MAILER->Subject = Config::get('NAME') . ' — Сброс пароля!';
			$this->MAILER->Body    = '
				<div style="font-size:15px;background-color:#fff;border:2px solid #c7c7c7;border-radius:2px;padding:20px;line-height: 1.5;font-weight: 400;">
					<b>Сброс пароля</b><hr>
					⚠ Вам пришло это письмо потому что на сайте <a href="'.Config::get('SITEURL').'">'.Config::get('SITEURL').'</a> вы воспользовались функцией сброса пароля,<br> если это были не вы, рекомендуется изменить почтовый адрес в своем аккаунте!<br>
					🔑 Новый пароль от аккаунта: <b>'.$newpass.'</b>
				</div>';
			$this->MAILER->AltBody = 'Откройте письмо в браузере с поддержкой HTML';

			$this->MAILER->send();
			return true;
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$this->MAILER->ErrorInfo}";
			return false;
		}
	}
}