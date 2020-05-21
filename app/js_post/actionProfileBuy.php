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

function discount($cost, $discount)
{
	$a = $cost / 100 * $discount;
	return $cost - $a;
}
function serverIp($ip, $port)
{
	if ( $port == '27015' ) return $ip;
	return $ip . ':' . $port;
}

$case = (int) $_POST['case'];

$fk = $cfg['FK']; // freekassa array
$disc = $cfg['DISC']; // discount array

/*
	case 1 - сервер > привилегия
	case 2 - привилегия > срок пирвилегии
	case 3 - мониторинг
	case 4 - описание привилегии
	case 5 - промокод
*/

switch ($case) {
	case '1': // сервер > привилегия
		$server_id = (int) $_POST['server_id'];

		if ( $server_id === 0 ) {
			// $view->message('error', 'Выберите сервер');
			exit(json_encode([ 'status' => 'error', 'message' => 'Выберите сервер' ]));
		}

		$sql = DB::run('SELECT * FROM `ez_privileges` WHERE `active` = 1 AND `sid` = ? ORDER BY `id`', [$server_id])->fetchAll();

		echo '<option value="0">Выберите привилегию</option>';
		foreach ($sql as $row) {
			if ( $row['id'] == $_SESSION['account']['tarif_id'] && $_SESSION['account']['expired'] > time() ) continue;
			echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
		}
		break;

	case '2': // привилегия > срок пирвилегии
		$privilege_id = (int) $_POST['privilege_id'];

		if ( $privilege_id === 0 ) {
			// $view->message('error', 'Выберите срок');
			exit(json_encode([ 'status' => 'error', 'message' => 'Выберите срок' ]));
		}

		$sql = DB::run('SELECT * FROM `ez_privileges_times` WHERE `pid` = ? ORDER BY `price`', [$privilege_id])->fetchAll();

		foreach ($sql as $row) {
			$date = ( $row['time'] == 0 ) ? 'Навсегда' : $row['time'] . ' дн.';
			$price = ($disc['active'] == 1) ? discount($row['price'], $disc['discount']) : $row['price'];
			echo '<option value="'.$row['time'].'">'.$date.' - '.$price.' руб.</option>';
		}
	break;

	case '3': // мониторинг
		require_once '../lib/SourceQuery.php';
		$server_id = (int)$_POST['server_id'];
		// префикс исправить
		$sql = DB::run('SELECT `id`, `address` FROM `'.$db['prefix'].'_serverinfo` WHERE `id` = ? LIMIT 1', [ $server_id ])->fetch(PDO::FETCH_ASSOC);
		list($ip, $port) = explode(":", $sql['address']);

		// ПОФИКСИТЬ ЧАСЫ В СПИСКЕ ИГРОКОВ
		$sq = new SourceQuery($ip, $port);
		$info  = $sq->getInfos();
		$players = $sq->getPlayers();

		if ( !$info ) 
		{
			$info = array(
				'map' => '-/-', 
				'name' => 'Сервер недоступен', 
			);
			$displayNone = 'class="d-none"';
		} else {
			$displayNone = '';
		}

		// map images
		$url = "https://image.gametracker.com/images/maps/160x120/cs/" .$info['map']. ".jpg";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, true);   
		curl_setopt($ch, CURLOPT_NOBODY, true);    
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.4");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);


		$mapimage = ($httpcode == 200) ? '<img id="mapImg" style="max-width: 136px;" class="mr-2 rounded" src="'.$url.'">' : '<img id="mapImg" style="max-width: 136px;" class="mr-2 rounded" src="https://image.gametracker.com/images/maps/160x120/nomap.jpg">';

		?>
			<div class="mess mess-ok d-flex animated zoomIn">
				<div <?=$displayNone?>>
					<?=$mapimage?>
				</div>
				<div class="d-flex flex-column text-truncate">
					<div><h6 class="m-0"><?=@$info['name']?></h6></div>
					<div <?=$displayNone?> style="font-size: 14px;">
						<i class="fa fa-picture-o" aria-hidden="true"></i> Карта <b><?=@$info['map']?></b>
					</div>
					<div <?=$displayNone?> style="font-size: 14px;">
						<i class="fa fa-user-o" aria-hidden="true"></i> Игроков <b><?=@$info['players']?>/<?=@$info['places']?></b>
					</div>
					<div <?=$displayNone?> style="font-size: 14px;">
						<i class="fa fa-steam" aria-hidden="true"></i> 
						<a href="steam://connect/<?=@$info['ip']?>:<?=@$info['port']?>"><?=@serverIp(@$info['ip'], @$info['port'])?></a>
					</div>
					<div <?=$displayNone?> style="font-size: 14px;">
						<i class="fa fa-users" aria-hidden="true"></i> <a href="#" data-toggle="modal" data-target="#players">Список игроков</a>
					</div>
				</div>
			</div>
			<div class="modal fade" id="players" tabindex="-1" role="dialog" aria-labelledby="players" aria-hidden="true">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="players"><?=$info['name']?></h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<!-- table -->
							<div class="table-responsive mb-0">
								<table class="table table-sm table-bordered">
								<thead>
									<tr>
										<td>Ник</td>
										<td>Фраги</td>
										<td>Время в игре</td>
									</tr>
								</thead>
								<tbody>
									<?php 
									foreach ($players as $row) {
										$timeInGame = floor($row['time'] / 60) % 60;
										echo '
										<tr>
											<td>'.$row['name'].'</td>
											<td>'.$row['score'].'</td>
											<td>'.$timeInGame.' мин.</td>
										</tr>
										';
									}
									?>
								</tbody>
								</table>
								</div>
							<!-- table // -->
						</div>
					</div>
				</div>
			</div>
		<?php
	break;

	case '4': // описание привилегии
		$privilege_id = (int)$_POST['privilege_id'];
		$sql = DB::run('SELECT * FROM `ez_editor` WHERE `pid` = ?', [$privilege_id])->fetch(PDO::FETCH_ASSOC);

		if ( !empty($sql) ):?>
			<div class="mess mess-info mt-2 animated zoomIn" style="font-size: 14px;">
				<?php echo $sql['content'];?>
			</div>
		<?php else:?>
			<div class="mess mess-error mt-2 animated zoomIn">Описание не заполнено</div>
		<?php endif;
	break;
}