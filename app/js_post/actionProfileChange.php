<?php 
session_start();
if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
	die('method error');
}

$cfg = require_once '../configs/main.php';
$db = require_once '../configs/db.php';
require_once 'db_class.php';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

use app\lib\DB;

$tarif_id 		= (int) $_POST['tarif_id'];
$user_id 		= (int) $_POST['user_id'];


function tarif_name($tid)
{
	$sql = DB::run('SELECT `name` FROM `ez_privileges` WHERE `id` = ?', [$tid])->fetch(PDO::FETCH_ASSOC);
	return $sql['name'];
}

$pseudo_balance	= (int)$_POST['pseudo_balance'];

// запросы в БД
$sql_tarif = DB::run('SELECT * FROM `ez_privileges_times` WHERE `pid` = ? AND `time` = ?', [ $tarif_id, 30 ])->fetch(PDO::FETCH_ASSOC);
$sql_user = DB::run('SELECT `created`, `expired` FROM `'.$db['prefix'].'_amxadmins` WHERE `id` = ?', [ $user_id ])->fetch(PDO::FETCH_ASSOC);

$_SESSION['changePrivilege']['sqlTarif'] = $sql_tarif;

if ( $sql_tarif === false ) {
	die('<div class="mess mess-error">В тарифе должен быть выбор <b>30ти</b> дней!</div>');
}

$newPriceOneDay 		= round($sql_tarif['price'] / $sql_tarif['time'], 1); // цена 1го дня
if($newPriceOneDay == 0) die('<div class="mess mess-warn">Выберите другую привилегию</div>'); // и че как это пофиксить лень разбираться мэйби потом
$newDaysLeft			= round($pseudo_balance / $newPriceOneDay, 0); // сколько осталось дней новой привилегии
$daysLeftInTimestamp 	= strtotime("+$newDaysLeft days"); // сколько дней новой привилегии осталось в timestamp

if ( $newDaysLeft < 1 ) {
	echo '
		<div class="mess mess-error">
			<p class="m-0" style="font-size:14px;">Недостаточно средств на псевдо балансе для этой привилегии.<br>
				Вы можете продлить текущую привелегию <a href="'.$cfg['SITEURL'].'account/profile/update">здесь</a> или купить другую <a href="'.$cfg['SITEURL'].'account/profile/buy">тут</a>.
			</p>
		</div>';
	exit();
}

// session
$_SESSION['changePrivilege']['daysLeftInTimestamp'] = $daysLeftInTimestamp;
$_SESSION['changePrivilege']['daysLeftInDays'] 		= $newDaysLeft;
$_SESSION['changePrivilege']['privilege_id'] 		= $tarif_id;
// var_dump($_SESSION['changePrivilege']);

echo '
	<div>
		<span>Расчет данных выбранной привилегии</span>
		<ul style="padding-left: 20px;">
			<li>'.tarif_name($tarif_id).'</li>
			<li>Останется <b>~ '.$newDaysLeft.' дн.</b></li>
		</ul>
	</div>
';