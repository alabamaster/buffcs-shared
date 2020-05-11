<div class="container">
	<div class="row d-flex justify-content-center">
		<div class="col-md-6 bg-white-rbg rounded">
			<div class="box">
				<h3 class="text-center">Авторизация</h3>
				<form action="<?=$this->SITE_URL?>account/login" method="POST" autocomplete="off">
					<div class="form-group">
						<input name="username" class="form-control" type="text" placeholder="Логин или Email">
					</div>
					<div class="form-group" style="position: relative;">
						<input id="password" name="password" class="form-control" type="password" placeholder="Пароль">
						<button id="showPassword" type="button" class="btn-class-none"><i id="eye" class="fa fa-eye"></i></button>
					</div>
					<div class="d-flex justify-content-between align-items-center">
						<div>
							<button type="submit" class="fc-button fc-button-green" name="goAuth">Войти</button>
						</div>
						<a class="link-profile mr-0" href="<?=$this->SITE_URL?>account/reset">Забыли пароль?</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
	const showPass 	= document.querySelector('#showPassword');
	const eye 		= document.querySelector('#eye');
	let inputPass 	= document.querySelector('#password');
	
	showPass.addEventListener('click', () => {
		if ( inputPass.type === 'password' ) {
			inputPass.type = 'text';
			eye.classList.remove('fa-eye');
			eye.classList.add('fa-eye-slash');
		} else {
			inputPass.type = 'password';
			eye.classList.remove('fa-eye-slash');
			eye.classList.add('fa-eye');
		}
	});
</script>