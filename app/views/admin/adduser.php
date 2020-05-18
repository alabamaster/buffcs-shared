<?php require_once 'navbar.php'; ?>
<div class="container mt-2">
	<div class="row mx-md-n1">
		<div class="col-lg-5 px-md-1">
			<div class="box">
				<form action="<?=$this->SITE_URL?>admin/adduser" method="POST">
					<div class="row">
						<div class="col">
							<div class="form-group">
								<label>Тип</label>
								<select id="type" name="type" class="form-control form-control-sm">
									<option value="a">Ник + пароль</option>
									<option value="ac">SteamID + пароль</option>
								</select>
							</div>
						</div>
						<div class="col" id="userCol">
							<label>Ник <span class="text-danger">*</span></label>
							<input type="text" id="userName" name="nickname" class="form-control form-control-sm" required="">
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="form-group">
								<label>Пароль <span class="text-danger">*</span></label>
								<input type="text" id="password" name="password" class="form-control form-control-sm" required="">
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label>Почта <span class="text-danger">*</span></label>
								<input type="email" id="email" name="email" class="form-control form-control-sm" required="">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="form-group">
								<label>Сервер <span class="text-danger">*</span></label>
								<select id="server" name="server" class="form-control form-control-sm">
									<option value="0" selected="" disabled="">Выберите сервер</option>
									<?php foreach ($servers as $row):?>
										<option value="<?=$row['id']?>"><?=$row['hostname']?></option>
									<?php endforeach;?>
								</select>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label>Флаги доступа <span class="text-danger">*</span></label>
								<input type="text" id="access" name="access" class="form-control form-control-sm" required="">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="form-group">
								<label>Дни (0 - навсегда) <span class="text-danger">*</span></label>
								<input type="text" id="days" name="days" class="form-control form-control-sm" required="">
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label>Всем виден?</label>
								<select id="show" name="show" class="form-control form-control-sm">
									<option value="1" selected="">Да</option>
									<option value="0">Нет</option>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="form-group">
								<label>#ID привилегии <span class="text-danger">*</span></label>
								<input type="text" id="privilege" name="privilege" class="form-control form-control-sm" placeholder="Не пишите 0" required="">
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label>ICQ (поле icq в БД)</label>
								<input type="text" id="icq" name="icq" class="form-control form-control-sm">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label>Ссылка на VK <span class="text-danger">(без https://)</span></label>
						<input type="text" id="vk" name="vk" class="form-control form-control-sm">
					</div>
					<button type="submit" class="fc-button fc-button-blue" style="cursor: pointer;">Добавить</button>
				</form>
			</div>
		</div>
		<div class="col-lg-7 px-md-1">
			<div class="box text-truncate overflow-auto" style="max-height: 532px;">
				<p class="m-0">
					<div class="alert alert-warning text-wrap"><p class="m-0">Все поля кроме VK и ICQ обязательны для заполнения!</p></div>
					<div class="alert alert-warning text-wrap"><p class="m-0">#ID привилегии указывать цифрой, id показывает в <a href="<?=$this->SITE_URL?>admin/addprivileges">таблице</a></p></div>
					<div>
						<h6>Флаги доступа</h6>
						a - Иммунитет (не может быть кикнут / забанен и т.д)<br>
						b - Резервирование слотов (может использовать зарезервированные слоты)<br>
						c - Команда amx_kick<br>
						d - Команда amx_ban и amx_unban<br>
						e - Команда amx_slay и amx_slap<br>
						f - Команда amx_map<br>
						g - Команда amx_cvar (не все CVAR'ы доступны)<br>
						h - Команда amx_cfg<br>
						i - amx_chat и другие команды чата<br>
						j - amx_vote и другие команды голосований (Vote)<br>
						k - Доступ к изменению значения команды sv_password (через команду amx_cvar)<br>
						l - Доступ к amx_rcon и rcon_password (через команду amx_cvar)<br>
						m - Уровень доступа A (для иных плагинов)<br>
						n - Уровень доступа B<br>
						o - Уровень доступа C<br>
						p - Уровень доступа D<br>
						q - Уровень доступа E<br>
						r - Уровень доступа F<br>
						s - Уровень доступа G<br>
						t - Уровень доступа H<br>
						u - Основной доступ<br>
						z - Игрок (не администратор)
					</div>
				</p>
			</div>
		</div>
	</div>
</div>
<script>
	let type		= document.querySelector('#type');
	const userCol 	= document.querySelector('#userCol');

	type.addEventListener('change', () => {
		if ( type.value == 'a' ) {
			userCol.innerHTML = `
				<label>Ник <span class="text-danger">*</span></label>
				<input type="text" id="userName" name="nickname" class="form-control form-control-sm">
			`;
		} else {
			userCol.innerHTML = `
				<label>SteamID <span class="text-danger">*</span></label>
				<input type="text" id="userName" name="steamid" class="form-control form-control-sm">
			`;
		}
	});
</script>