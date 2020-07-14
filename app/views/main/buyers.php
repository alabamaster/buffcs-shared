<?php 
use app\core\Config;
use app\models\Main;
$main = new Main;
use app\lib\DB;
?>
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div class="box">
				
				<!-- filter -->
				<div class="row px-3">
					<div class="col-lg-6">
						<div class="form-group">
							<div class="row align-items-center">
								<div class="col-md-4">
									<button type="button" class="fc-button fc-button-blue w-100" id="clearFilter">Сбросить фильтр</button>
								</div>
								<div class="col-md-8">
									<select class="form-control form-control-sm" id="filterServer">
										<option disabled="" selected="">Фильтр по серверам</option>
										<?php foreach ($allServers as $row):?>
											<option value="<?=$row['id']?>"><?=$row['hostname']?></option>
										<?php endforeach;?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<div class="row align-items-center">
								<div class="col-md-10">
									<input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Поиск: Ник / SteamID">
								</div>
								<div class="col-md-2">
									<button type="button" id="goSearch" class="fc-button fc-button-blue">Поиск</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- filter //-->

				<?php if ( isset($_GET['search']) ):?>
					<div style="background-color:#e4efff;border-left:4px solid #50adff;padding: 4px 4px 4px 15px;color:#505050;font-size:13px;">
						<span>Поиск по запросу: <b><?=htmlspecialchars($_GET['search'])?></b>, совпадений: <b><?=$dataTotalRows?></b></span>
					</div>
				<?php endif;?>

				<div class="table-responsive <?php if($dataTotalRows == 0) echo 'd-none';?>">
					<table class="table table-hover">
						<thead>
							<tr>
							<?php if( Config::get('ICONS') == 1 ):?>
								<th class="text-center border-0 d-flex justify-content-center"><i class="fa fa-info"></i></th>
							<?php endif;?>
								<th class="border-0">Ник игрока</th>
							<?php if( $main->getCountServers() > 1 ):?>
								<th class="border-0">Сервер</th>
							<?php endif;?>
								<th class="border-0">Привилегия</th>
								<th class="border-0">Начало</th>
								<th class="border-0">Окончание</th>
								<th class="border-0"><i class="fa fa-vk"></i></th>
							</tr>
						</thead>
						<tbody class="tr-hover-effect">
						<?php 
							foreach (/*$pagination['answer']*/$data as $row):
								$expired = ($row['expired'] == 0) ? 'Никогда' : date('d.m.Y', $row['expired']);
								$tarif = ($row['tarif_id'] == null || $row['tarif_id'] == 0) ? 'Unknown' : $main->getPrivilegeNameById($row['tarif_id']);
								$bgred = ($row['expired'] < time() && $row['expired'] != 0) ? 'style="background-color: #fff9eb"' : '';
								$vk = ($row['vk'] != null) ? '<span class="text-info"><a href="https://'.htmlspecialchars($row['vk']).'" target="_blank"><i class="fa fa-vk"></i></a></span>' : '<span class="text-secondary"><i class="fa fa-vk"></i></span>';
						?>
							<tr <?=$bgred?>>
							<?php if(Config::get('ICONS') == 1):?>
								<td class="text-center">
									<?=$main->getIcon($row['tarif_id'])?>
								</td>
							<?php endif;?>
								<td data-toggle="tooltip">
									<div class="row">
										<div class="col text-truncate" style="max-width: 250px;">
											<?=htmlspecialchars($row['nickname'])?>
										</div>
									</div>
								</td>
							<?php if( $main->getCountServers() > 1 ):?>
								<td>
									<div class="row">
										<div class="col text-truncate" style="max-width: 250px;">
											<?=$main->getServerNameById($row['server_id']);?>
										</div>
									</div>
								</td>
							<?php endif;?>
								<td>
									<div class="row">
										<div class="col text-truncate" style="max-width: 250px;">
											<?=$tarif?>
										</div>
									</div>
								</td>
								<td data-toggle="tooltip" data-placement="left" data-boundary="window" title="<?=date('В H:i', $row['created'])?>"><?=date('d.m.Y', $row['created'])?></td>
								
								<?php if($row['expired'] != 0):?>
								<td data-toggle="tooltip" data-placement="left" data-boundary="window" title="<?=date('В H:i', $row['expired'])?>"><?=$expired;?></td>
								<?php else:?>
								<td><?=$expired;?></td>
								<?php endif;?>

								<td class="vk-icon">
									<?php if($row['vk'] != null):?>
									<span class="text-info">
										<a href="https://<?=htmlspecialchars($row['vk'])?>" target="_blank"><i class="fa fa-vk"></i></a>
									</span>
									<?php else:?>
									<span class="text-secondary"><i class="fa fa-vk"></i></span>
									<?php endif;?>
									
									<?php if(isset($_SESSION['admin'])):?>
									<button type="button" data-toggle="modal" data-target="#user<?=$row['id']?>" class="btn-class-none"><i class="fa fa-cog"></i></button>
									<!-- Modal user edit -->
									<div class="modal fade" id="user<?=$row['id']?>" tabindex="-1" role="dialog" aria-labelledby="user" aria-hidden="true">
										<div class="modal-dialog" role="document">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title" id="user">Изменить данные</h5>
													<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">&times;</span>
													</button>
												</div>
												<form action="#" class="m-1 d-flex bg-secondary p-1 rounded" method="POST">
													<div>
														<button type="submit" class="fc-button fc-button-orange"><i class="fa fa-trash"></i> Удалить</button>
														<input type="hidden" name="deleteUser" value="<?=$row['id']?>">
													</div>
													<div class="d-flex align-items-center">
														<span class="text-white pl-2">Удалить пользователя из БД навсегда</span>
													</div>
												</form>
												<form action="<?=$this->SITE_URL?>buyers" method="POST">
													<div class="modal-body" style="padding-top: 5px;padding-bottom: 5px;">
														<div class="mess mess-error text-wrap text-center">Будьте внимательны при изменение данных!</div>
														<div class="info">
															<div class="row mb-1 border-bottom pb-2 pt-1 align-items-center">
																<div class="col-md-3">ID</div>
																<div class="col-md-9"><?=$row['id']?></div>
															</div>
															<div class="row mb-1 border-bottom pb-2 pt-1 align-items-center">
																<div class="col-md-3">Показывать</div>
																<div class="col-md-9">
																	<select name="show" class="form-control form-control-sm">
																		<?php if($row['ashow'] == 1):?>
																			<option value="0">Скрыть</option>
																			<option value="1" selected="">Показывать</option>
																		<?php else:?>
																			<option value="0" selected="">Скрыть</option>
																			<option value="1">Показывать</option>
																		<?php endif;?>
																	</select>
																</div>
															</div>
															<div class="row mb-1 border-bottom pb-2 pt-1 align-items-center">
																<div class="col-md-3">Ник</div>
																<div class="col-md-9"><input class="form-control form-control-sm" type="text" name="nickname" value="<?=htmlspecialchars($row['nickname'])?>"></div>
															</div>
															<div class="row mb-1 border-bottom pb-2 pt-1 align-items-center">
																<div class="col-md-3">SteamID</div>
																<div class="col-md-9"><input class="form-control form-control-sm" type="text" name="steamid" value="<?=htmlspecialchars($row['steamid'])?>"></div>
															</div>
															<div class="row mb-1 border-bottom pb-2 pt-1 align-items-center">
																<div class="col-md-3">Сервер</div>
																<div class="col-md-9"><?=$main->getServerNameById($row['server_id'])?></div>
															</div>
															<div class="row mb-1 border-bottom pb-2 pt-1 align-items-center">
																<div class="col-md-3">Привилегия</div>
																<div class="col-md-9"><?=$main->getPrivilegeNameById($row['tarif_id'])?></div>
															</div>
															<div class="row mb-1 border-bottom pb-2 pt-1 align-items-center">
																<div class="col-md-3">Email</div>
																<div class="col-md-9"><input class="form-control form-control-sm" type="text" name="email" value="<?=htmlspecialchars($row['email'])?>"></div>
															</div>
															<div class="row mb-1 border-bottom pb-2 pt-1 align-items-center">
																<div class="col-md-3">VK</div>
																<div class="col-md-9"><input class="form-control form-control-sm" type="text" name="vk" value="<?=htmlspecialchars($row['vk'])?>"></div>
															</div>
															<div class="row mb-1 border-bottom pb-2 pt-1 align-items-center">
																<div class="col-md-3">Тип</div>
																<div class="col-md-9">
																	<?php if($row['flags'] == 'a'):?>
																		<select name="type" class="form-control form-control-sm">
																			<option value="a" selected="">Ник + пароль</option>
																			<option value="ac">SteamID + пароль</option>
																		</select>
																	<?php else:?>
																		<select name="type" class="form-control form-control-sm">
																			<option value="ac" selected="">SteamID + пароль</option>
																			<option value="a">Ник + пароль</option>
																		</select>
																	<?php endif;?>
																</div>
															</div>
															<div class="row mb-1 border-bottom pb-2 pt-1 align-items-center">
																<div class="col-md-3">Сервер</div>
																<div class="col-md-9">
																	<select name="server" class="form-control form-control-sm">
																		<?php foreach ( $allServers as $s_val ):?>
																			<?php if ( $s_val['id'] == $row['server_id'] ):?>
																				<option value="<?=$s_val['id']?>" selected=""><?=$s_val['hostname']?></option>
																			<?php else:?>
																				<option value="<?=$s_val['id']?>"><?=$s_val['hostname']?></option>
																			<?php endif;?>
																		<?php endforeach;?>
																	</select>
																</div>
															</div>
															<div class="row mb-1 border-bottom pb-2 pt-1 align-items-center">
																<div class="col-md-3">Привилегия</div>
																<div class="col-md-9">
																	<?php $sql=DB::run('SELECT `name` FROM `ez_privileges` WHERE `id` = ?', [$row['tarif_id']])->fetch(PDO::FETCH_ASSOC);?>
																	
																	<select name="privilege" class="form-control form-control-sm">
																		<?php if(!$sql):?>
																			<option value="<?=$row['tarif_id']?>" selected="">Unknown</option>
																		<?php endif;?>
																		<?php foreach ( $allPrivileges as $p_val ):?>
																			<?php if ( $p_val['id'] == $row['tarif_id'] ):?>
																				<option value="<?=$p_val['id']?>" selected=""><?=$p_val['name']?></option>
																			<?php else:?>
																				<option value="<?=$p_val['id']?>"><?=$p_val['name']?></option>
																			<?php endif;?>
																		<?php endforeach;?>
																	</select>
																</div>
															</div>
														</div>
													</div>
													<input type="hidden" name="user_id" value="<?=$row['id']?>">
													<div class="modal-footer p-1">
														<button type="button" class="fc-button fc-button-dark" data-dismiss="modal">Закрыть</button>
														<button type="submit" class="fc-button fc-button-green"><i class="fa fa-floppy-o"></i> Сохранить</button>
													</div>
												</form>
											</div>
										</div>
									</div>
									<!-- // Modal user edit -->
									<?php endif;?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
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
	const filterServer 	= document.querySelector('#filterServer');
	const urlPage 		= window.location.href;
	const clearFilter 	= document.querySelector('#clearFilter');
	const btnSearch 	= document.querySelector('#goSearch');
	const mainUrl 		= <?php echo json_encode($this->SITE_URL)?>;

	const getParameterByName = (name) => {
		let match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
		return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
	}

	const server 		= getParameterByName('server');
	const search 		= getParameterByName('search');

	btnSearch.addEventListener('click', () => {
		const search = document.querySelector('#searchInput');;

		if ( search.value.length < 3 || search.value.length > 20 ) {
			search.classList.add('is-invalid');
			return false;
		} else {
			search.classList.remove('is-invalid');
			search.classList.add('is-valid');
		}

		if ( getParameterByName('search') !== null ) // в юрл уже есть поиск
		{
			const urlPage 	= mainUrl + 'buyers';
			const urlEdit 	= urlPage.split( '?' )[0]; // очищаем юрл от параметров ?...
			const urlNew 	= `${urlEdit}?search=${search.value}`; // добавляем сервер в юрл
			// console.log(urlPage, urlEdit, urlNew);
			window.location.href = urlNew; // редирект
		} else { // в юрл еще нет сервер ид
			const urlStart 	= mainUrl + 'buyers';
			const urlEdit 	= urlStart.split( '?' )[0];
			const urlNew 	= `${urlEdit}?search=${search.value}`
			// console.log(urlStart, urlEdit, urlNew);
			window.location.href = urlNew; // редирект
		}
	});

	// filterServer.addEventListener('change', (e) => 
	// {
	// 	if ( getParameterByName('server') !== null ) // в юрл уже есть сервер ид
	// 	{
	// 		const urlPage 	= mainUrl + 'buyers';
	// 		const urlEdit 	= urlPage.split( '?' )[0]; // очищаем юрл от параметров ?...
	// 		const urlNew 	= `${urlEdit}?server=${e.target.value}`; // добавляем сервер в юрл
	// 		// console.log(urlPage, urlEdit, urlNew);
	// 		window.location.href = urlNew; // редирект
	// 	} else { // в юрл еще нет сервер ид
	// 		const urlStart 	= mainUrl + 'buyers';
	// 		const urlEdit 	= urlStart.split( '?' )[0];
	// 		const urlNew 	= `${urlEdit}?server=${e.target.value}`
	// 		// console.log(urlStart, urlEdit, urlNew);
	// 		window.location.href = urlNew; // редирект
	// 	}
	// });

	filterServer.addEventListener('change', (e) => {
		let searchParams = new URLSearchParams(window.location.search);

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

	clearFilter.addEventListener('click', () => {
		window.location.href = mainUrl + 'buyers';
	});

	document.addEventListener('DOMContentLoaded', () => {
		let images = document.querySelectorAll('img');

		for (let i = 0; i < images.length; i++) {
			images[i].onerror = function() {
				// this.style.display='none';
				this.setAttribute('src', './unknown.png');
			}
		}
	});
</script>
