<div class="row mx-md-1">
	<div class="col-md-12 mt-2 px-md-1">
		<div class="card">
			<div class="card-header"><span class="font-weight-bold">Основные данные</span></div>
			<div class="card-body">
				<form action="<?=$this->SITE_URL?>account/profile/edit" method="POST" class="mt-2" autocomplete="off">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label>Email</label>
								<input minlength="3" maxlength="40" type="email" class="form-control" name="email" id="email" value="<?=htmlspecialchars($user_data['email'])?>">
							</div>
							<div class="form-group">
								<label>Пароль</label>
									<input minlength="3" maxlength="30" type="text" class="form-control" name="password" id="password" placeholder="Оставьте пустым или введите новый">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>ВКонтакте</label>
								<div data-toggle="tooltip" data-html="true" title="Например: <br>vk.com/player1337 <br>vk.com/id1337 <br><b>Важно: <span class='text-danger'>без https://</span></b>">
									<input minlength="7" maxlength="40" type="text" class="form-control" name="vk" id="vk" value="<?=htmlspecialchars($user_data['vk'])?>">
								</div>		
							</div>
							<div class="form-group">
								<label>Аватар</label>
								<div class="custom-file">
									<input type="file" class="custom-file-input" name="avatar" id="avatar" disabled="">
									<label class="custom-file-label" for="avatar">Выберите файл</label>
									<div class="invalid-feedback">invalid custom file feedback</div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Тип</label>
								<select name="type" id="type" class="form-control">
									<!-- <option value="0" selected="">Не менять</option> -->
									<?php if($user_data['flags'] == 'a'):?>
									<option value="a" selected="">Ник + пароль</option>
									<option value="ac">SteamID + пароль</option>
									<?php else:?>
									<option value="a">Ник + пароль</option>
									<option value="ac" selected="">SteamID + пароль</option>
									<?php endif;?>
								</select>
							</div>
							<div class="form-group">
								<?php if($user_data['flags'] == 'a'):?>
								<div id="label" class="d-inline-block" style="margin-bottom: 0.5rem;">Ник</div>
									<input minlength="3" maxlength="30" type="text" class="form-control" name="nickname" id="userName" value="<?=$user_data['username']?>">
								<?php else:?>
										<div id="label" class="d-inline-block" style="margin-bottom: 0.5rem;">SteamID</div>
									<input minlength="5" maxlength="30" type="text" class="form-control" name="steamid" id="userName" value="<?=$user_data['username']?>">
								<?php endif;?>
							</div>
						</div>
					</div>
					<input type="hidden" name="main-settings">
					<div class="card-footer text-muted" style="margin: -5px -20px -20px -20px;">
						<button type="submit" class="fc-button fc-button-orange">Обновить данные</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
	const type = document.querySelector('#type');
	const label = document.querySelector('#label');
	const userName = document.querySelector('#userName');
	const typePHP = '<?=$user_data['flags']?>';
	const usernamePHP = '<?=$user_data['username']?>';

	type.addEventListener('change', () => 
	{
		if ( type.value == 'a' ) {
			label.innerHTML = `Ник`;
			userName.name = 'nickname';
			userName.placeholder = 'player1337';
			userName.minlength = 3;
			userName.maxlength = 32;
			if ( type.value == typePHP ) {
				userName.value = usernamePHP;
			} else {
				userName.value = '';
			}
		} else if ( type.value == 'ac' ) {
			label.innerHTML = `SteamID`;
			userName.name = 'steamid';
			userName.placeholder = 'STEAM_0:123456789';
			userName.minlength = 5;
			userName.maxlength = 25;
			if ( type.value == typePHP ) {
				userName.value = usernamePHP;
			} else {
				userName.value = '';
			}
		}
	});
});
</script>