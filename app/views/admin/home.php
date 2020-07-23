<?php require_once 'navbar.php'; ?>
<div class="container mt-2">
	<div class="row mx-md-n1">
		<div class="col-lg-8 px-md-1">
			<div class="box">
				<div class="buy-logs">
					<h6>Лог покупок (последние 50)</h6>
					<small class="text-muted">Кол-во записей сейчас: <b><?=$countLogs?></b></small>
					<div class="table-responsive" style="max-height: 500px;">
						<table class="table table-sm table-hover" style="font-size: 14px;">
							<thead class="bg-secondary text-white">
								<tr>
									<td class="font-weight-bold">Ник/SteamId</td>
									<td class="font-weight-bold text-center">Дата</td>
									<td class="font-weight-bold text-center">Дни</td>
									<td class="font-weight-bold text-center">Статус</td>
									<td></td>
								</tr>
							</thead>
							<tbody>
							<?php 
								foreach ($logs as $row):
									$username = ($row['type'] == 'a') ? htmlspecialchars($row['nickname']) : htmlspecialchars($row['steamid']);
									$typeBuy = ($row['type'] == 'a') ? 'Ник + пароль' : 'SteamID + пароль';
									$buyStatus = ($row['buy_status'] == 1) ? '<span class="badge badge-success">Оплачено</span>' : '<span class="badge badge-dark">Ожидание</span>';
									$days = ($row['days'] == 0) ? 'Навсегда' : htmlspecialchars($row['days']);
							?>
								<tr>
									<td><div class="d-inline-block text-truncate" style="position: absolute;"><?=$username?></div></td>
									<td class="text-center"><?= date('d.m.Y в H:i', $row['created']) ?></td>
									<td class="text-center"><?= $days ?></td>
									<td class="text-center"><?= $buyStatus ?></td>
									<td class="text-center"><a href="#" data-toggle="modal" data-target="#moreInfo<?=$row['table_id']?>">Подробнее</a></td>
								</tr>
						<!-- Modal -->
						<div class="modal fade" id="moreInfo<?=$row['table_id']?>" tabindex="-1" role="dialog" aria-labelledby="MoreInfo" aria-hidden="true">
						<div class="modal-dialog" role="document">
						<div class="modal-content">
						<div class="modal-header" style="padding: 10px;">
						<h5 class="modal-title" id="MoreInfo">
							<div><?=$username?></div><div class="m-l-3" style="font-size: 14px;"><?=$buyStatus?></div>
						</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
						</div>
						<div class="modal-body" style="font-size: 14px;">
						<ul class="list-group list-group-flush">
							<li class="list-group-item p-1">
								<div class="font-weight-bold">Действие</div><p class="m-0">
									<?php if ($row['buy_type'] == 1):?>
										Покупка привилегии
									<?php elseif ($row['buy_type'] == 2):?>
										Покупка привилегии через ЛК
									<?php elseif ($row['buy_type'] == 3):?>
										Продление привилегии через ЛК
									<?php endif;?>
								</p>
							</li>
							<li class="list-group-item p-1">
								<div class="font-weight-bold">ID покупки</div><p class="m-0"><?=$row['id']?></p>
							</li>
							<li class="list-group-item p-1">
								<div class="font-weight-bold">Сервер</div><p class="m-0"><?=$model->getServerNameById($row['sid'])?></p>
							</li>
							<li class="list-group-item p-1">
								<div class="font-weight-bold">Привилегия</div><p class="m-0"><?=$model->getPrivilegeNameById($row['pid'])?></p>
							</li>
							<li class="list-group-item p-1">
								<div class="font-weight-bold">Касса</div><p class="m-0"><?=$row['shop']?></p>
							</li>
							<li class="list-group-item p-1">
								<div class="font-weight-bold">Браузер</div><p class="m-0" style="font-size: 12px;"><?=$row['browser']?></p>
							</li>
							<li class="list-group-item p-1">
								<div class="font-weight-bold">IP</div><p class="m-0"><?=$row['ip']?></p>
							</li>
							<li class="list-group-item p-1">
								<div class="font-weight-bold">VK</div><p class="m-0"><?=htmlspecialchars($row['vk'])?></p>
							</li>
							<li class="list-group-item p-1">
								<div class="font-weight-bold">Email</div><p class="m-0"><?=htmlspecialchars($row['email'])?></p>
							</li>
							<li class="list-group-item p-1">
								<div class="font-weight-bold">Дата покупки</div><p class="m-0"><?=date('d.m.Y в H:i', $row['created'])?></p>
							</li>
							<li class="list-group-item p-1">
								<div class="font-weight-bold">Тип</div><p class="m-0"><?=$typeBuy?></p>
							</li>
						</ul>
						</div>
						</div>
						</div>
						</div>
						<!-- // Modal -->
								<?php endforeach;?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-4 px-md-1">
			<div class="box">
				<ul class="list-group">
					<li class="list-group-item d-flex justify-content-between align-items-center">
						Активных игроков
						<span class="badge badge-primary badge-pill"><?=$stats['activeUsers']?></span>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						Не активных игроков
						<span class="badge badge-primary badge-pill"><?=$stats['expiredUsers']?></span>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						Всего привилегий
						<span class="badge badge-primary badge-pill"><?=$stats['countPrivileges']?></span>
					</li>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						Всего серверов
						<span class="badge badge-primary badge-pill"><?=$stats['countServers']?></span>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>