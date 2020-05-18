<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<?php if ($model->checkUserIP($_SERVER["REMOTE_ADDR"])['exist'] == true) : ?>
					<div class="mess mess-error text-center font-weight-bold">
						Вы забанены, посмотреть <a href="<?= $this->SITE_URL ?>bans/ban<?= $model->checkUserIP($_SERVER["REMOTE_ADDR"])['bid'] ?>">подробнее</a>
					</div>
				<?php endif; ?>
				
				<div class="table-responsive">
					<h6 class="text-center text-muted d-block mb-0" id="loading">Идёт загрузка ...</h6>
					<table class="table table-hover d-none" style="font-size: 14px" id="table-data-bans">
						<thead>
							<tr>
								<th class="nosort border-0">Игрок</th>
								<th class="border-0">Админ</th>
								<?php if( $count_serv > 1 ){
									echo '<th class="border-0">Сервер</th>';
								}?>
								<th class="border-0">Причина</th>
								<th class="nosort border-0">Забанен</th>
								<th class="nosort border-0">Окончание</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($allBans as $row) : ?>
								<tr>
									<td>
										<div class="row">
											<div class="col text-truncate" style="max-width: 200px;">
												<a href="<?= $this->SITE_URL ?>bans/ban<?= $row['bid'] ?>"><?= htmlspecialchars($row['player_nick']) ?></a>
											</div>
										</div>
									</td>
									<td>
										<div class="row">
											<div class="col text-truncate" style="max-width: 250px;"><?=htmlspecialchars($row['admin_nick'])?></div>
										</div>
									</td>
									<?php if( $count_serv > 1 ){
									echo '
									<td>
										<div class="row">
											<div class="col text-truncate" style="max-width: 250px;">'.$SERVERS->getServerNameByIp($row['server_ip']).'</div>
										</div>
									</td>';
									}?>
									<td>
										<div class="row"><div class="col text-truncate" style="max-width: 200px;"><?=$row['ban_reason']?></div>
										</div>
									</td>
									<td>
										<div class="row"><div class="col text-truncate"><?=date('d.m.Y в H:i', $row['ban_created'])?></div>
										</div>
									</td>
									<td><?=$model->bansExpiredCalc($row['ban_created'], $row['expired'], $row['ban_length'], false)?></td>
								</tr>
								<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function() {
	$('#table-data-bans').on('init.dt',function() {
		$("#table-data-bans").removeClass('d-none').show();
		$('#loading').remove();
	});

	setTimeout(function(){
		$('#table-data-bans').DataTable({
			columnDefs: [{
				targets: 'nosort',
				orderable: false
			}],
			"aaSorting": [],
			"processing": true,
			"language": {
				"lengthMenu":		"Показать _MENU_ записей",
				"emptyTable":		"Данные отсутствуют в таблице",
				"info":				"Показано с _START_ по _END_ из _TOTAL_ записей",
				"infoFiltered":		"(фильтрация из _MAX_ записей)",
				"infoEmpty":		"Нет записей",
				"loadingRecords": 	"Loading...",
				"processing":		"Processing...",
				"search":			"Поиск",
				"zeroRecords":		"Не найдено подходящих записей",
				"paginate": {
					"first":		"Первая",
					"last":			"Последняя",
					"next":			"Следующая",
					"previous":		"Предыдущая"
				},
			},
			drawCallback: function () {
				$('#dtPluginExample_paginate ul.pagination').addClass("pagination-sm");
			},
		});
	}, 3000);
});
</script>