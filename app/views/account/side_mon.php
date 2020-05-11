<?php use app\models\Account;?>
<?php if($myserver):?>
	<h6 class="font-weight-bold text-center text-truncate"><?=$myserver['arr_info']['name'];?></h6>
	<div class="row justify-content-center align-items-center" style="padding-bottom: 17px;">
		<div class="col-md-auto">
			<img class="img-thumbnail rounded d-block" src="<?=$myserver['map_url']?>" alt="Server map">
		</div>
		<div class="col-md-auto">
			<ul class="list-unstyled d-flex justify-content-center flex-column">
				<li class="border-bottom py-1">Карта <b><?=$myserver['arr_info']['map'];?></b></li>
				<li class="py-1">Игроков <b><?=$myserver['arr_info']['players'];?>/<?=$myserver['arr_info']['places'];?></b></li>
				<li class="py-1">
					<a href="#" data-toggle="modal" data-target="#serverPlayers" class="fc-button fc-button-dark a-link-connect d-flex justify-content-center">Список игроков</a></li>
				<li class="py-1">
					<a class="fc-button fc-button-green a-link-connect d-flex justify-content-center" href="steam://connect/<?=$myserver['arr_info']['ip'];?>:<?=$myserver['arr_info']['port'];?>">Подключиться</a>
				</li>
			</ul>
		</div>
	</div>
<?php else:?>
<div class="mess mess-warn">Сервер недоступен</div>
<?php endif;?>
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
				<?php if( empty($myserver['arr_players'][0]) ):?>
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
									<td>'.htmlspecialchars($row['name']).'</td>
									<td>'.$row['score'].'</td>
									<td>'.Account::secToStrDate($row['time']).'</td>
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