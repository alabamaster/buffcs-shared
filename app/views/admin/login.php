<div class="container">
	<div class="row">
		<div class="col-md-6 offset-md-3">
			<div class="box">
				<h3 class="h3 mb-3 font-weight-normal text-center">Авторизуйтесь</h3>
				<form method="POST" action="<?=$this->SITE_URL?>admin/login" autocomplete="off">
					<div class="form-group">
						<label>Логин</label>
						<input type="text" name="username" id="username" class="form-control" required="">
					</div>
					<div class="form-group mb-1">
						<label>Пароль</label>
						<input type="password" name="password" id="password" class="form-control" required="">
						<div class="custom-control custom-checkbox mr-sm-2 mt-2 mb-0">
							<input class="custom-control-input" type="checkbox" id="customCheck" onchange="document.getElementById('password').type = this.checked ? 'text' : 'password'">
							<label class="custom-control-label" for="customCheck">Показать пароль</label>
						</div>
					</div>
					<button class="fc-button fc-button-blue btn-block" name="submitBtnLogin" id="submitBtnLogin" type="submit">Войти</button>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
	const divMess = document.querySelector('#mess');
	const url = document.querySelector('form').getAttribute('action');

	let name = document.querySelector('#username').value;
	let pass = document.querySelector('#password').value;

	fetch(url, {
		method: 'POST',
		mode: 'cors',
		cache: 'no-cache',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
		},
		body: 'name='+name+'&pass='+pass,
	})
	.then( response =>  {
		if ( response.status == 200 ) {
			return response.text();
		} else {
			return false;
		}
	} /*console.log(response)*/ )
	.then( response => {
		console.log(response);
		return;
		// divMess.innerHTML = response;

		let json;
		json = jQuery.parseJSON(response.json);
		if ( json.status == 'ok' ) {
			divMess.innerHTML = 'agaggaga';
		} else if ( json.status == 'error' ) {
			alert('ошибка');
		} else {
			alert('что-то другое');
		}
	});
</script>