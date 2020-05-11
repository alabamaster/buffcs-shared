<?php use app\core\Config;?>
<!DOCTYPE html>
<html lang="ru">
	<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Main css -->
	<link rel="stylesheet" href="<?=Config::get('SITEURL')?>themes/<?=Config::get('STYLE')?>/css/bootstrap.css">
	<link rel="stylesheet" href="<?=Config::get('SITEURL')?>themes/<?=Config::get('STYLE')?>/css/main.css">
	
	<!-- google fonts -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700|Roboto+Condensed:700&amp;amp;subset=cyrillic">

	<title>Error 403</title>
	</head>
	<body class="errors">
		<div class="container mt-2">
			<div class="row justify-content-center">
				<div class="col-md-8">
					<div class="card">
						<div class="card-header font-weight-bold">ERROR 404</div>
						<div class="card-body text-center">
							<h1 class="text-danger">Ошибка #404<br>Страница не найдена</h1>
						</div>
						<div class="card-footer">
							<a class="btn btn-primary btn-sm" href="<?=Config::get('SITEURL')?>">Вернуться на главную</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>