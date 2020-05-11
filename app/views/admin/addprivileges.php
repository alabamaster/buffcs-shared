<?php 
use app\core\Config;
require_once 'navbar.php'; 
?>
<div class="container mt-2">
	<?php if(Config::get('ICONS') == 1):?>
		<?php if( !is_writable('icons') ):?>
		<div class="row mx-md-n1">
			<div class="col px-md-1 mb-2">
				<div class="mess mess-error text-wrap">Папка "<b>icons</b>" не доступна для записи, установите права "<b>777</b>"!</div>
			</div>
		</div>
		<?php endif;?>
	<?php endif;?>
	<div class="row mx-md-n1" <?php if( !is_writable('icons') ) echo 'style="pointer-events: none;filter: blur(1px);"';?>>
		<div class="col-lg-5 px-md-1">
			<div class="box">
				<form action="<?=$this->SITE_URL?>admin/addprivileges" method="POST" enctype="multipart/form-data">
					<div class="form-group">
						<label><i class="fa fa-at" aria-hidden="true"></i> Название привилегии</label>
						<input type="text" class="form-control" id="pri_name" name="pri_name" placeholder="VIP account" required="">
					</div>
					<div class="form-group">
						<label> <i class="fa fa-keyboard-o" aria-hidden="true"></i> Сервер</label>
						<select class="form-control" id="server" name="server">
							<option value="0" disabled="" selected="">Выберите сервер</option>
							<?php foreach ($servers as $row):?>
								<option value="<?=$row['id']?>"><?=$row['hostname']?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="form-group">
						<label><i class="fa fa-unlock-alt" aria-hidden="true"></i> Флаги доступа</label>
						<input type="text" class="form-control" id="access" name="access" placeholder="abcdefghijklmnopqrstu" pattern="^[a-z]+$" required="">
					</div>

					<div class="form-group">
						<label><i class="fa fa-calendar" aria-hidden="true"></i> Время</label>
						<input type="text" class="form-control" id="time" name="time" placeholder="Время через запятую без пробелов" pattern="^(?:\d+,)*\d+$" required="">
					</div>
					<div class="form-group">
						<label><i class="fa fa-rub" aria-hidden="true"></i> Цена</label>
						<input type="text" class="form-control" id="price" name="price" placeholder="Цена через запятую без пробелов" pattern="^(?:\d+,)*\d+$" required="">
					</div>
					<?php if(Config::get('ICONS') == 1):?>
					<div class="custom-file">
						<input type="file" class="custom-file-input" name="privilegeIcon" id="privilegeIcon" required="">
						<label class="custom-file-label" for="privilegeIcon" data-browse="Выбрать файл">Иконка привилегии</label>
						<small class="text-muted">Размер 16х16 в формате png</small>
					</div>
					<?php endif;?>
					<div class="custom-control custom-checkbox mt-2">
						<input type="checkbox" class="custom-control-input" name="active" id="active" value="0">
						<label class="custom-control-label" for="active">Првилегия доступна для покупки</label>
					</div>
					<button id="add" type="submit" class="fc-button fc-button-blue btn-block mt-2">Добавить</button>
				</form>
			</div>
		</div>
		<div class="col-lg-7 px-md-1">
			<div class="box" style="height: 589px;">
				<div class="d-flex align-items-center justify-content-between">
					<div class="mb-2">
						<div class="mr-3"><i class="fa fa-trash-o text-danger" aria-hidden="true"></i> удалить</div>
						<div class="mr-3"><i class="fa fa-pencil" aria-hidden="true"></i> редактировать</div>
					</div>
					<div class="buttons">
						<div><button class="fc-button fc-button-orange" id="deleteAllPrivileges"><i class="fa fa-trash-o"></i> Удалить все привилегии и иконки</button></div>
					</div>
				</div>
				<div class="table-responsive" style="height: 523px;">
					<table class="table table-sm table-hover" style="font-size: 12px;">
						<thead>
							<tr class="bg-secondary text-white">
								<td class="font-weight-bold border-top-0">Сервер</td>
								<td class="font-weight-bold border-top-0">Привилегия</td>
								<td class="text-center font-weight-bold border-top-0">Флаги</td>
								<td class="text-center font-weight-bold border-top-0">Покупка</td>
								<td class="text-center font-weight-bold border-top-0">#</td>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($privileges as $row):
								$server = $model->getServerNameById($row['sid']);
								$priv = $model->getPrivilegeNameById($row['id']);
							?>
							<tr>
								<td data-toggle="tooltip" data-placement="top" data-boundary="window" title="<?=$model->getServerNameById($row['sid'])?>">
									<div class="row">
										<div class="col text-truncate" style="max-width: 220px;"><?=$server?></div>
									</div>
								</td>
								<td data-toggle="tooltip" data-placement="top" data-boundary="window" title="<?=$model->getPrivilegeNameById($row['id'])?> [id: <?=$row['id']?>]">
									<div class="row">
										<div class="col text-truncate" style="max-width: 220px;"><?=$priv?></div>
									</div>
								</td>
								<td class="text-center" data-toggle="tooltip" data-placement="top" data-boundary="window" title="<?=$row['access']?>"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></td>
								<td class="text-center">
									<?php if($row['active'] == 1):?>
									<i class="fa fa-check text-success" aria-hidden="true"></i>
									<?php else:?>
									<i class="fa fa-times text-danger" aria-hidden="true"></i>
									<?php endif;?>
								</td>
								<td class="text-center d-flex" style="font-size: 14px;">
									<button id="edit" class="btn-class-none pr-1 pl-1" data-toggle="modal" data-target="#exampleModal-<?=$row['id']?>">
										<i class="fa fa-pencil" aria-hidden="true" title="Редактировать"></i>
									</button>
									<button class="btn-class-none pr-1 pl-1" data-toggle="modal" data-target="#deletePrivilege_<?=$row['id']?>">
										<i class="fa fa-trash-o text-danger" aria-hidden="true" title="Удалить"></i>
									</button>
								</td>
							</tr>
							<!-- Modal -->
							<div class="modal fade" id="exampleModal-<?=$row['id']?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
							  <div class="modal-dialog" role="document">
							    <div class="modal-content">
							      <div class="modal-header">
							        <h5 class="modal-title" id="exampleModalLabel">Изменить привилегию</h5>
							        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
							          <span aria-hidden="true">&times;</span>
							        </button>
							      </div>
							      <div class="modal-body">
						        	<form>
										<div class="form-group">
											<label class="col-form-label">Сервер</label>
											<input type="text" class="form-control" disabled="" value="<?=$model->getServerNameById($row['sid'])?>">
										</div>
										<div class="form-group">
											<label class="col-form-label">Название</label>
											<input type="text" class="form-control" id="p_name_<?=$row['id']?>" value="<?=$model->getPrivilegeNameById($row['id'])?>">
										</div>
										<div class="form-group">
											<label class="col-form-label">Флаги доступа</label>
											<input type="text" class="form-control" id="p_access_<?=$row['id']?>" value="<?=$row['access']?>">
										</div>
										<div class="form-group">
											<label>Првилегия доступна для покупки</label>
											<select class="form-control" id="p_active_<?=$row['id']?>">
												<?php if($row['active'] == 1):?>
													<option value="0">Нет</option>
													<option value="1" selected="">Да</option>
												<?php else:?>
													<option value="0" selected="">Нет</option>
													<option value="1">Да</option>
												<?php endif;?>
											</select>
										</div>
									<?php if(Config::get('ICONS') == 1):?>
										<div class="form-group">
											<label>Иконка привилегии</label>
											<img width="16px" class="d-block" src="<?=Config::get('SITEURL')?>icons/<?=$row['icon_img']?>" alt="Privilege icon">
										</div>
									<?php endif;?>
									</form>
							      </div>
							      <div class="modal-footer p-1">
							        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
							        <button type="button" class="btn btn-primary" onclick="editPrivilege(this.id)" id="<?=$row['id']?>">Сохранить</button>
							      </div>
							    </div>
							  </div>
							</div>
							<!-- // Modal -->
							<!-- Modal delete privilege -->
							<div class="modal fade" id="deletePrivilege_<?=$row['id']?>" tabindex="-1" role="dialog" aria-labelledby="Delete Privilege" aria-hidden="true">
								<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="exampleModalLabel">Удалить привилегию?</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
										</button>
									</div>
								<div class="modal-body">
									<div class="form-group">
										<label>Название</label>
										<input type="text" class="form-control" disabled="" value="<?=$model->getPrivilegeNameById($row['id'])?>">
									</div>
									<div class="form-group">
										<label>Сервер</label>
										<input type="text" class="form-control" disabled="" value="<?=$model->getServerNameById($row['sid'])?>">
									</div>
								<?php if(Config::get('ICONS') == 1):?>
									<div class="form-group">
										<label>Иконка привилегии</label>
										<img width="16px" class="d-block" src="<?=Config::get('SITEURL')?>icons/<?=$row['icon_img']?>" alt="Privilege icon">
									</div>
								<?php endif;?>
								</div>
								<div class="modal-footer p-1">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
								<button type="button" class="btn btn-danger" id="delete" onclick="deletePrivilege(this.value)" value="<?=$row['id']?>">Да, удалить</button>
								</div>
								</div>
								</div>
							</div>
							<!-- // Modal delete privilege -->
							<?php endforeach;?>
						</tbody>
					</table>
				</div>	
			</div>
		</div>
	</div>
</div>
<div id="mess-delete"></div>
<script>
	const active 	= document.querySelector('#active');
	const divMess = document.querySelector('#mess-delete');
	const deleteAll = document.querySelector('#deleteAllPrivileges');

	active.addEventListener('click', () => {
		if ( active.checked === true ) {
			active.value = 1;
		} else {
			active.value = 0;
		}
		// console.log(active.value);
	});

	deleteAll.addEventListener('click', () => {
		fetch('<?=Config::get('SITEURL')?>admin/addprivileges', {
			method: 'POST',
			mode: 'cors',
			cache: 'no-cache',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'deleteAllPrivileges=1',
		})
		.then( response =>  {
			if ( response.status == 200 ) {				
				return response.text();
			} else {
				return false;
			}
		})
		.then( response => {
			json = jQuery.parseJSON(response);
			if(json.url) {
				window.location.href = json.url;
			}
		});
	});

	const deletePrivilege = (e) => {
		fetch('<?=$this->SITE_URL?>admin/addprivileges', {
			method: 'POST',
			mode: 'cors',
			cache: 'no-cache',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'delete=1&privilege_id='+e,
		})
		.then( response =>  {
			if ( response.status == 200 ) {				
				return response.text();
			} else {
				return false;
			}
		} /*console.log(response)*/ )
		.then( response => {
			// console.log(response);
			// jsResponse.innerHTML = response;
			json = jQuery.parseJSON(response);
			// console.log(json);
			if(json.url) {
				window.location.href = json.url;
			} else {
				if ( json.status == 'success' ) {
					divMess.innerHTML = `<div class="mb-2 mess-v2 mess-v2-ok animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				} else if( json.status == 'error' ) {
					divMess.innerHTML = `<div class="mb-2 mess-v2 mess-v2-error animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				} else if( json.status == 'warning' ) {
					divMess.innerHTML = `<div class="mb-2 mess-v2 mess-v2-warn animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				} else {
					divMess.innerHTML = `<div class="mb-2 mess-v2 mess-v2-info animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				}
			}
		});
	};

	const editPrivilege = (e) => {
		let p_name 		= document.querySelector('#p_name_' + e).value;
		let p_access 	= document.querySelector('#p_access_' + e).value;
		let p_active 	= document.querySelector('#p_active_' + e).value;

		fetch('<?=$this->SITE_URL?>admin/addprivileges', {
			method: 'POST',
			mode: 'cors',
			cache: 'no-cache',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: `edit=1&privilege_id=${e}&p_name=${p_name}&p_access=${p_access}&p_active=${p_active}`,
		})
		.then( response =>  {
			if ( response.status == 200 ) {				
				return response.text();
			} else {
				return false;
			}
		}
		)
		.then( response => {
			json = jQuery.parseJSON(response);
			if(json.url) {
				window.location.href = json.url;
			} else {
				if ( json.status == 'success' ) {
					divMess.innerHTML = `<div class="mb-2 mess-v2 mess-v2-ok animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				} else if( json.status == 'error' ) {
					divMess.innerHTML = `<div class="mb-2 mess-v2 mess-v2-error animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				} else if( json.status == 'warning' ) {
					divMess.innerHTML = `<div class="mb-2 mess-v2 mess-v2-warn animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				} else {
					divMess.innerHTML = `<div class="mb-2 mess-v2 mess-v2-info animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				}
			}
		});
	}
</script>