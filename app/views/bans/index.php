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
								<th class="border-0">Игрок</th>
								<th class="border-0">Админ</th>
								<th class="border-0">Причина</th>
								<th class="border-0">Забанен</th>
								<th class="border-0">Окончание</th>
								<th class="border-0">#ID</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($allBans as $row) : ?>
								<tr>
									<td><a href="<?= $this->SITE_URL ?>bans/ban<?= $row['bid'] ?>"><?= htmlspecialchars($row['player_nick']) ?></a></td>
									<td><?=htmlspecialchars($row['admin_nick'])?></td>
									<td><?=$row['ban_reason']?></td>
									<td><?=date('d.m.Y в H:i', $row['ban_created'])?></td>
									<td><?=$model->bansExpiredCalc($row['ban_created'], $row['expired'], $row['ban_length'], false)?></td>
									<td><?=$row['bid']?></td>
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
			"order": [5, 'desc'],
			"processing": true,
			'language' : {
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
			"preDrawCallback": function() {
				$('#table-data-bans tbody td').addClass("blurry");
				$('#table-data-bans tbody').fadeOut(600);
			},
			"drawCallback": function() {
				$('#table-data-bans tbody td').addClass("blurry");
				$('#table-data-bans tbody').fadeIn(800);
				setTimeout(function(){
					$('#table-data-bans tbody td').removeClass("blurry");
				},600);
			}
		});

		// pagination
		$('.pagination').addClass('pagination-sm');
	}, 3000);
});
</script>