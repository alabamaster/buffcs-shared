<?php 
use app\core\Config;
use app\models\Account;
use app\models\Main;
$MAIN = new Main;
?>
<!DOCTYPE html>
<html lang="ru">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<meta http-equiv="Content-Security-Policy" content="object-src 'none'; script-src 'self' 'unsafe-inline'">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/css/bootstrap.css">

		<!-- animate v3.7.2 -->
		<link rel="stylesheet" href="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/css/animate.min.css">

		<!-- Google Fonts -->
		<link rel="stylesheet" href="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/css/googleFonts.css">

		<!-- Font Awesome Icons -->
		<link rel="stylesheet" href="https://use.fontawesome.com/64ff6e1601.css">

		<link rel="stylesheet" href="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/css/main.css">
		<link rel="icon" type="image/png" sizes="16x16" href="<?=$this->SITE_URL?>favicon-16x16.png">

		<title><?php echo $title; ?></title>
	</head>
	<body>
		<?php require_once 'navbar.php';?>
		<div class="container">
			<div class="row mx-md-1">
				<div class="col-md-12 px-md-1">
					<div class="box p-0">
						<div class="main-profile">
							<div class="avatar">
								<img src="<?php echo $this->SITE_URL.'themes/'.$this->SITE_STYLE.'/img/no-avatar.jpg'?>" alt="Avatar" width="153" height="153" class="rounded-circle">
							</div>
							<div class="profile-info">
								<div class="main-info">
									<div class="username">
										<div class="d-inline-flex">
											<span class="mr-1 d-flex align-items-center"><?=$MAIN->getIcon($user_data['tarif_id'])?></span>
											<span><?=htmlspecialchars($user_data['username'])?></span>
										</div>
									</div>
									<ul class="ul-profile-info text-truncate">
										<li class="li-profile-info">
											<div class="li-title">#ID</div>
											<div class="li-data"><?=$user_data['id'];?></div>
										</li>
										<li class="li-profile-info">
											<div class="li-title">Почта</div>
											<div class="li-data"><?=htmlspecialchars($user_data['email']);?></div>
										</li>
										<?php if($user_data['vk']):?>
										<li class="li-profile-info">
											<div class="li-title">ВКонтакте</div>
											<div class="li-data"><?=htmlspecialchars($user_data['vk']);?></div>
										</li>
										<?php endif;?>
									</ul>
								</div>
								<div class="info-desc">
									<ul class="ul-profile-info text-truncate">
										<li class="li-profile-info">
											<div class="li-title">Привилегия</div>
											<div class="li-data"><?=$tariff;?></div>
										</li>
										<li class="li-profile-info">
											<div class="li-title">Сервер</div>
											<div class="li-data"><?=$server;?></div>
										</li>
										<li class="li-profile-info">
											<div class="li-title">Начало</div>
											<div class="li-data" data-toggle="tooltip" data-placement="bottom" title="<?=date('В H:i', $user_data['created']);?>">
												<?=date('d.m.Y', $user_data['created']);?>
											</div>
										</li>
										<li class="li-profile-info">
											<div class="li-title">Окончание</div>
											<div class="li-data" data-toggle="tooltip" data-placement="bottom" title="<?=date('В H:i', $user_data['expired']);?>">
												<?=Account::expired_time($user_data['expired']);?>
											</div>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="profile-footer">
							<div class="nav-footer">
								<a class="link-profile" href="<?=$this->SITE_URL?>account/profile/exit">
									<i class="fa fa-sign-out" aria-hidden="true"></i> Выйти
								</a>
								<a class="link-profile" href="<?=$this->SITE_URL?>account/profile/<?=$user_data['id']?>">
									<i class="fa fa-user-o" aria-hidden="true"></i> Профиль
								</a>
								<?php if ( $user_data['expired'] == 0 || $user_data['expired'] > time() ):?>
								<a class="link-profile" href="<?=$this->SITE_URL?>account/profile/edit">
									<i class="fa fa-cog" aria-hidden="true"></i> Настройки
								</a>
								<?php if( $user_data['tarif_id'] != 0 && $user_data != null ):?>
								<a class="link-profile" href="<?=$this->SITE_URL?>account/profile/change">
									<i class="fa fa-exchange" aria-hidden="true"></i> Изменить
								</a>
								<?php endif;?>
								<?php endif;?>
								<a class="link-profile" href="<?=$this->SITE_URL?>account/profile/buy">
									<i class="fa fa-usd" aria-hidden="true"></i> Купить новую
								</a>
								<a class="link-profile" href="<?=$this->SITE_URL?>account/profile/update">
									<i class="fa fa-clock-o" aria-hidden="true"></i> Продлить
								</a>
							</div>
						</div>
					</div>
				</div>
				<?php if($user_data['expired'] < time() && $user_data['expired'] != 0):?>
				<div class="col-md-12 mt-2 px-md-1">
					<div class="box p-2 d-flex align-items-center">
						<p class="p-0 m-0 d-inline-flex align-items-center">
							<span class="text-danger mr-3 ml-2"><i class="fa fa-clock-o fa-2x"></i></span>
							<span>Срок привилегии истёк, <a href="<?=$this->SITE_URL?>account/profile/update">продлите</a> текущую или <a href="<?=$this->SITE_URL?>account/profile/buy">купите</a> новую!</span>
						</p>
					</div>
				</div>
				<?php endif;?>
			</div>
			<?php echo $content;?>
		</div>

		<footer class="footer"></footer>
		<div id="mess"></div>

		<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/popper.min.js"></script>

		<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/jquery.min.js"></script>

		<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/bootstrap.min.js"></script>

		<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/main.js"></script>
		
		<?php if( $_SESSION['authorization'] === true ):?>
			<script src="<?=$this->SITE_URL?>themes/<?=$this->SITE_STYLE?>/js/jquery.countdown.min.js"></script>
			<script>
				$(function () {
					$('#clock').countdown('<?=date('Y/m/d H:i', $user_data['expired'])?>', function(event) {
						$(this).html(event.strftime(` 
							<div class="d-flex flex-wrap justify-content-center">
								<div class="holder m-2">
									<span class="h4 font-weight-bold">%D</span> дн
								</div>
								<div class="holder m-2">
									<span class="h4 font-weight-bold">%H</span> час
								</div>
								<div class="holder m-2">
									<span class="h4 font-weight-bold">%M</span> мин
								</div>
								<div class="holder m-2">
									<span class="h4 font-weight-bold">%S</span> сек
								</div>
							</div>
						`));
					});
				});
			</script>
		<?php endif;?>

	</body>
</html>