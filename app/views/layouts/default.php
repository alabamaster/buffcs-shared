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

		<!-- jquery -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

		<title><?php echo $title; ?></title>
	</head>
	<body>
		<?php require_once 'navbar.php';?>
		<?php echo $content;?>

		<footer class="footer"></footer>
		<div id="mess"></div>

		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
		<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/bootstrap.min.js"></script>
	
		<?php if( $this->route['controller'] == 'admin' ):?>
		<!-- https://github.com/Johann-S/bs-custom-file-input -->
		<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
		<script>
			$(document).ready(function () {
				bsCustomFileInput.init()
			});
		</script>
		<?php endif;?>

		
		<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/main.js"></script>
		<?php if( $this->route['controller'] == 'main' && $this->route['action'] == 'buy' ):?>
		<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/mainBuy.js"></script>
		<?php endif;?>
	</body>
</html>