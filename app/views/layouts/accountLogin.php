<!DOCTYPE html>
<html lang="ru">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"> -->
		<link rel="stylesheet" href="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/css/bootstrap.css">

		<!-- animate -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css">

		<!-- Google Fonts -->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700|Roboto+Condensed:700&amp;amp;subset=cyrillic">

		<!-- Font Awesome Icons -->
		<link rel="stylesheet" href="https://use.fontawesome.com/64ff6e1601.css">

		<link rel="stylesheet" href="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/css/main.css">
		<link rel="icon" type="image/png" sizes="16x16" href="<?=$this->SITE_URL?>favicon-16x16.png">

		<title><?php echo $title; ?></title>
	</head>
	<body>
		<?php require_once 'navbar.php';?>
		<div class="container">
			<div class="row d-flex justify-content-center">
				<div class="col-md-6 bg-white-rbg rounded">
					<div class="box">
						<h3 class="text-center">Авторизация</h3>
						<!-- <div id="mess" class="mb-1"></div> -->
						<form action="<?=$this->SITE_URL?>account/login" method="POST" autocomplete="off">
							<div class="form-group">
								<select name="server" id="server" class="form-control">
									<option value="0" selected="" disabled="">Выберите сервер</option>
									<?php foreach ($servers as $row):?>
										<option value="<?=$row['id']?>"><?=$row['hostname']?></option>
									<?php endforeach;?>
								</select>
							</div>
							<div class="form-group">
								<select name="type" id="type" class="form-control">
									<option value="a">Ник + пароль</option>
									<option value="ac">SteamID + пароль</option>
								</select>
							</div>
							<div class="form-group">
								<input name="username" class="form-control" type="text" placeholder="Ник / SteamID" minlength="2" maxlength="30">
							</div>
							<div class="form-group" style="position: relative;">
								<input id="password" name="password" class="form-control" type="password" placeholder="Пароль" minlength="2" maxlength="30">
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

		<footer class="footer"></footer>
		<div id="mess"></div>
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<!-- <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script> -->
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script> -->
		<script src="<?=$this->SITE_URL?>/themes/<?=$this->SITE_STYLE?>/js/bootstrap.min.js"></script>
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
		<script src="<?=$this->SITE_URL?>/themes/<?=$this->SITE_STYLE?>/js/main.js"></script>
	</body>
</html>