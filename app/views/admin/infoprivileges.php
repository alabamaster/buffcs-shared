<?php require_once 'navbar.php'?>
<div class="container mt-2">
	<div class="row mx-md-n1">
		<div class="col-md-12 px-md-1">
			<div class="box">
				<div class="mess mess-info text-wrap mb-3">
					<p class="m-0">Используйте любой онлайн HTML редактор для оформления описания<br>Например <a href="https://html5-editor.net" target="_blank">этот</a>, скопируйте HTML(левый блок) с того сайта и вставьте в блок ниже</p>
				</div>

				<form action="<?=$this->SITE_URL?>admin/infoprivileges" method="POST">
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Сервер</label>
								<select name="server" id="server" class="form-control">
									<option value="0" selected="" disabled="">Выберите сервер</option>
									<?php foreach ($servers as $row):?>
										<option value="<?=$row['id']?>"><?=$row['hostname']?></option>
									<?php endforeach;?>
								</select>
							</div>
							<div class="form-group">
								<label>Привилегия</label>
								<select name="privilege" id="privilege" class="form-control">
									<option value="0" selected="" disabled="">Выберите привилегию</option>
									<?php foreach ($privileges as $row):?>
										<option value="<?=$row['id']?>"><?=$row['name']?></option>
									<?php endforeach;?>
								</select>
							</div>
						</div>
						<div class="col-lg-8">
							<div id="text">
								<div class="form-group">
									<label>Описание привилегии</label>
									<textarea name="editor" id="editor" cols="30" rows="13" class="form-control"></textarea>
								</div>
							</div>
						</div>
					</div>
					<button type="submit" id="btnSave-0" class="fc-button fc-button-green">Сохранить</button>
				</form>
			</div>
		</div>
	</div>
</div>
<script src="https://cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
<script>
	addEventListener('DOMContentLoaded', () => {
		const server = document.querySelector('#server');
		const privilege = document.querySelector('#privilege');
		const btnSave = document.querySelector('#btnSave');
		const divMess = document.querySelector('#mess');
		let editor = document.querySelector('#editor');

		// CKEDITOR.replace('editor');

		server.addEventListener('change', () => {
			fetch('<?=$this->SITE_URL?>app/js_post/actionAdminInfoprivileges.php', {
				method: 'POST',
				mode: 'cors',
				cache: 'no-cache',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'case=1&server_id=' + server.value,
			})
			.then( response =>  {
				if ( response.status == 200 ) {
					return response.text();
				} else {
					return false;
				}
			})
			.then( response => {
				privilege.innerHTML = response;
			})
		});

		privilege.addEventListener('change', () => {
			fetch('<?=$this->SITE_URL?>app/js_post/actionAdminInfoprivileges.php', {
				method: 'POST',
				mode: 'cors',
				cache: 'no-cache',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'case=2&privilege_id=' + privilege.value,
			})
			.then( response =>  {
				if ( response.status == 200 ) {
					return response.text();
				} else {
					return false;
				}
			})
			.then( response => {
				document.querySelector('#text').innerHTML = response;
			})
		});
	});
</script>