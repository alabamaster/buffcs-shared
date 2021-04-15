<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				<?php if ($model->checkUserIP($_SERVER["REMOTE_ADDR"])['exist'] === true) : ?>
				<div class="mess mess-error text-center font-weight-bold">
					Вы забанены, посмотреть <a href="<?= $this->SITE_URL ?>bans/ban<?= $model->checkUserIP($_SERVER["REMOTE_ADDR"])['bid'] ?>">подробнее</a>
				</div>
				<?php endif; ?>

				<!-- <form action="#"> -->
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<div class="row">
									<div class="col-lg-4">
										<button type="button" class="fc-button fc-button-blue" id="clearFilter">Сбросить фильтр</button>
									</div>
									<div class="col-lg-8">
										<select class="form-control form-control-sm" id="server">
											<option selected="" disabled="">Фильтр по серверам</option>
											<?php foreach ($allServers as $row):?>
                                    <?php $selected = (isset($_GET['server']) && $_GET['server'] == $row['id']) ? 'selected=""' : '' ?>
                                    <option value="<?=$row['id']?>" <?=$selected?>><?=$row['hostname']?></option>
                                 <?php endforeach;?>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<form action="#" method="GET">
									<div class="row">
										<div class="col-lg-9">
											<input type="text" id="search" class="form-control form-control-sm" placeholder="Ник / SteamID">
										</div>
										<div class="col-lg-3">
											<button type="button" id="goSearch" class="fc-button fc-button-blue">Поиск</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				<!-- </form> -->

				<?php if ( isset($_GET['search']) ):?>
					<div style="background-color:#e4efff;border-left:4px solid #50adff;padding: 4px 4px 4px 15px;color:#505050;font-size:13px;">
						<span>Поиск по запросу: <b><?=htmlspecialchars($_GET['search'])?></b>, совпадений: <b><?=$dataTotalRows?></b></span>
					</div>
				<?php endif;?>

				<div class="table-responsive <?php if($dataTotalRows == 0) echo 'd-none';?>">
					<table class="table table-hover" style="font-size: 14px">
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
							<?php foreach ($data as $row):?>
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
										<div class="col text-truncate" style="max-width: 220px;"><?=htmlspecialchars($row['admin_nick'])?></div>
									</div>
								</td>
								<?php if( $count_serv > 1 ):?>
								<td><?=$SERVERS->getServerNameByIp($row['server_ip']);?></td>
								<?php endif;?>
								<td>
									<div class="row">
										<div class="col text-truncate" style="max-width: 200px;"><?=$row['ban_reason']?></div>
									</div>
								</td>
								<td><?=date('d.m.Y в H:i', $row['ban_created'])?></td>
								<td><?=$model->bansExpiredCalc($row['ban_created'], $row['expired'], $row['ban_length'], false)?></td>
							</tr>
						<?php endforeach;?>
						</tbody>
					</table>
				</div>
            
            <?php if($dataTotalRows == 0):?>
            <p class="text-center pt-4">Нет данных</p>
            <?php endif;?>
				
            <div class="row align-items-center <?php if($dataTotalRows == 0) echo 'd-none';?>">
					<div class="col-md-9">
						<?=$paginator?>
					</div>
					<div class="col-md-3 d-flex justify-content-end">
						<span>Всего записей: <?=$dataTotalRows;?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	const selectServer 	= document.querySelector('#server');
	const inputSearch 	= document.querySelector('#search');
	const clearFilter 	= document.querySelector('#clearFilter');
	const btnSearch 	= document.querySelector('#goSearch');
	const mainUrl = <?php echo json_encode($this->SITE_URL)?>;

	const getParameterByName = (name) => {
		let match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
		return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
	}

	const server 		= getParameterByName('server');
	const search 		= getParameterByName('search');

	selectServer.addEventListener('change', (e) => {
		const searchParams = new URLSearchParams(window.location.search);
		const curPage = getParameterByName('page');

		if ( curPage > 1 ) {
			searchParams.set('page', 1);
		}

		if ( server === null ) {
			searchParams.append('server', e.target.value);
			let newParams = searchParams.toString();
			window.location.href = '?' + newParams;
		} else {
			searchParams.set('server', e.target.value);
			searchParams.set('page', 1);
			let newParams = searchParams.toString();
			window.location.href = '?' + newParams;
		}
	});

	btnSearch.addEventListener('click', () => {
		const search = document.querySelector('#searchInput');;

		if ( inputSearch.value.length < 3 || inputSearch.value.length > 20 ) {
			inputSearch.classList.add('is-invalid');
			return false;
		} else {
			inputSearch.classList.remove('is-invalid');
			inputSearch.classList.add('is-valid');
		}

		if ( getParameterByName('search') !== null ) // в юрл уже есть поиск
		{
			const urlPage 	= mainUrl + 'bans?page=1&';
			const urlEdit 	= urlPage.split( '?' )[0]; // очищаем юрл от параметров ?...
			const urlNew 	= `${urlEdit}?search=${inputSearch.value}`; // добавляем сервер в юрл
			// console.log(urlPage, urlEdit, urlNew);
			window.location.href = urlNew; // редирект
		} else { // в юрл еще нет сервер ид
			const urlStart 	= mainUrl + 'bans?page=1';
			const urlEdit 	= urlStart.split( '?' )[0];
			const urlNew 	= `${urlEdit}?search=${inputSearch.value}`
			// console.log(urlStart, urlEdit, urlNew);
			window.location.href = urlNew; // редирект
		}
	});

	clearFilter.addEventListener('click', () => {
		// window.location.href = window.location.origin + '/bans?page=1';
      window.location.href = window.location.origin + window.location.pathname + '?page=1';
	});

</script>