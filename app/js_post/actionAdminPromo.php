<?php 
require_once '../configs/main.php';
require_once '../configs/db.php';
require_once 'db_class.php';

use app\lib\DB;

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
	die('method error');
}

$case = (int)$_POST['case'];

switch ($case) {
	case '1':
		$server_id = (int)$_POST['server_id'];
		$sql = DB::run('SELECT `id`, `name` FROM `ez_privileges` WHERE `sid` = ? ORDER BY `id`', [$server_id]);
		
		if ( $sql->rowCount() > 0 ) {
			echo '<option value="0" selected disabled>Выберите привилегию</option>';
			foreach ($sql = $sql->fetchAll() as $row) {
				echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
			}
		} else {
			echo '<option value="0" selected disabled>Нет привилегий для этого сервера</option>';
		}
	break;

	case '2':
		$code_id = (int)$_POST['code_id'];

		try {
			DB::run('DELETE FROM `ez_promo_codes` WHERE `id` = ?', [ $code_id ]);
		} catch (Exception $e) {
			echo 'Error:' . $e->getMessage();
		}
		echo '<div class="mess mess-ok" style="font-size: 15px;display: flex;align-items: center;">Промокод был успешно удалён, обновите страницу</div>';
	break;

	case '3':
		# code...
		break;
}