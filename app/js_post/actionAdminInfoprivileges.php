<?php 
$cfg = require_once '../configs/main.php';
$db = require_once '../configs/db.php';
require_once 'db_class.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use app\lib\DB;
use app\lib\SourceQuery;

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
	die('method error');
}

session_start();

$case = (int) $_POST['case'];

$disc = $cfg['DISC']; // discount array

/*
	case 1 - сервер > привилегия
	case 2 - привилегия > опасание
*/

switch ($case) {
	case '1': // сервер > привилегия
		$server_id = (int) $_POST['server_id'];

		if ( $server_id === 0 ) {
			// $view->message('error', 'Выберите сервер');
			exit(json_encode([ 'status' => 'error', 'message' => 'Выберите сервер' ]));
		}

		$sql = DB::run('SELECT * FROM `ez_privileges` WHERE `sid` = ? AND `active` = 1 ORDER BY `id`', [ $server_id ])->fetchAll();

		echo '<option value="0" selected="" disabled="">Выберите привилегию</option>';
		foreach ($sql as $row) {
			echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
		}
	break;

	case '2': // привилегия > описание
		$privilege_id = (int) $_POST['privilege_id'];

		if ( $privilege_id === 0 ) {
			// $view->message('error', 'Выберите сервер');
			exit(json_encode([ 'status' => 'error', 'message' => 'Выберите сервер' ]));
		}

		$sql = DB::run('SELECT * FROM `ez_editor` WHERE `pid` = ?', [ $privilege_id ])->fetch(PDO::FETCH_ASSOC);

		// if($sql) {
		// 	echo '<p>'.$sql['content'].'</p>';
		// }
		if($sql) {
			echo '
				<div class="form-group">
					<label for="editor">Описание привилегии</label>
					<textarea name="editor" id="editor" cols="30" rows="15" class="form-control">'.htmlspecialchars($sql['content']).'</textarea>
				</div>';
		} else {
			echo '
				<div class="form-group">
					<label for="editor">Описание привилегии</label>
					<textarea name="editor" id="editor" cols="30" rows="15" class="form-control">Ваш HTML</textarea>
				</div>';
		}
	break;
}