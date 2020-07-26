<!DOCTYPE html>
<html lang="ru">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="Content-Security-Policy" content="object-src 'none'; script-src 'self' 'unsafe-inline'">

		<!-- Bootstrap CSS v4.4.1 -->
		<link rel="stylesheet" href="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/css/bootstrap.css">

		<!-- animate v3.7.2 -->
		<link rel="stylesheet" href="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/css/animate.min.css">

		<!-- Google Fonts -->
		<link rel="stylesheet" href="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/css/googleFonts.css">

		<!-- Font Awesome Icons -->
		<link rel="stylesheet" href="https://use.fontawesome.com/64ff6e1601.css">

		<link rel="stylesheet" href="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/css/main.css">
		<link rel="icon" type="image/png" sizes="16x16" href="<?=$this->SITE_URL?>favicon-16x16.png">

		<!-- jquery v3.4.1 -->
		<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/jquery.min.js"></script>

		<title><?php echo $title; ?></title>
	</head>
	<body>
		<?php require_once 'navbar.php';?>
		<?php echo $content;?>

		<footer class="footer"></footer>
		<div id="mess"></div>

		<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/popper.min.js"></script>
		<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/bootstrap.min.js"></script>
	
		<?php if( $this->route['controller'] == 'admin' ):?>
		<!-- https://github.com/Johann-S/bs-custom-file-input -->
		<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/bs-custom-file-input.min.js"></script>
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