<?php use app\models\Account;?>
<?php if($myserver['arr_info']['gq_online']):?>
	<h6 class="font-weight-bold text-center text-truncate"><?=$myserver['arr_info']['hostname'];?></h6>
	<div class="row justify-content-center align-items-center" style="margin-bottom: -14px;">
		<div class="col-lg-5">
			<img class="img-thumbnail rounded d-block" src="<?=$myserver['map_url']?>" alt="Server map">
		</div>
		<div class="col-lg-7 text-truncate">
			<ul class="list-unstyled d-flex justify-content-center flex-column">
				<li class="border-bottom py-1">Карта <b><?=$myserver['arr_info']['gq_mapname'];?></b></li>
				<div class="text-truncate">
					<li class="border-bottom py-1">Следующая карта <b><?=$myserver['arr_info']['amx_nextmap'];?></b></li>
				</div>
				<li class="py-1">Игроков <b><?=$myserver['arr_info']['gq_numplayers'];?>/<?=$myserver['arr_info']['gq_maxplayers'];?></b></li>
				<li class="py-1">
					<a href="#" data-toggle="modal" data-target="#serverPlayers" class="fc-button fc-button-dark a-link-connect d-flex justify-content-center">Список игроков</a></li>
				<li class="py-1">
					<a class="fc-button fc-button-green a-link-connect d-flex justify-content-center" href="<?=$myserver['arr_info']['gq_joinlink']?>">Подключиться</a>
				</li>
			</ul>
		</div>
	</div>
	<!-- Modal -->
	<div class="modal fade" id="serverPlayers" tabindex="-1" role="dialog" aria-labelledby="players" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="players">Список игроков</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<?php if( empty($myserver['arr_players']) ):?>
						<div class="text-center font-weight-bold text-secondary">Нет игроков</div>
					<?php else:?>
					<div class="table-responsive">
						<table class="table table-hover table-sm">
							<thead>
								<tr>
									<th class="border-0" scope="col">#</th>
									<th class="border-0" scope="col">Ник</th>
									<th class="border-0" scope="col">Фраги</th>
									<th class="border-0" scope="col">В игре</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($myserver['arr_players'] as $row) {
									echo '<tr>
										<th scope="row">'.$row['id'].'</th>
										<td>'.htmlspecialchars($row['gq_name']).'</td>
										<td>'.$row['gq_score'].'</td>
										<td>'.Account::secToStrDate($row['gq_time']).'</td>
									<tr>';
								}?>
								</tr>
							</tbody>
						</table>
					</div>
					<?php endif;?>
				</div>
			</div>
		</div>
	</div>
<?php else:?>
<div class="mess mess-error">Сервер недоступен</div>
<?php endif;?>