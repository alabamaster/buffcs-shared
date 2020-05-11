<nav class="navbar navbar-expand-lg nav-bg navbar-dark fixed-top box p-2 rounded-0" style="box-shadow: none;">
	<a class="navbar-brand ml-5 mr-5" href="<?=$this->SITE_URL?>"><?=$this->SITE_NAME?></a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
	<span class="navbar-toggler-icon"></span>
	</button>

	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item">
				<a class="n-link" href="<?=$this->SITE_URL?>buy"><i class="fa fa-rub"></i> Покупка</a>
			</li>
			<li class="nav-item">
				<a class="n-link" href="<?=$this->SITE_URL?>buyers"><i class="fa fa-user-o"></i> Покупатели</a>
			</li>
			<li class="nav-item">
				<a class="n-link" href="<?=$this->SITE_URL?>bans"><i class="fa fa-wheelchair"></i> Банлист</a>
			</li>
			<li class="nav-item">
				<a class="n-link" href="<?=$this->SITE_URL?>account/login"><i class="fa fa-home"></i> Личный кабинет</a>
			</li>
		<?php if(isset($_SESSION['admin'])):?>
			<li class="nav-item">
				<a class="n-link" href="<?=$this->SITE_URL?>admin"><i class="fa fa-cogs"></i> Админ</a>
			</li>
		<?php endif;?>
			<li class="nav-item">
				<a class="n-link" href="<?=$this->SITE_URL?>support"><i class="fa fa-life-ring"></i> Поддержка</a>
			</li>
		</ul>
	</div>
	<?php if($this->route['controller'] == 'main' && $this->route['action'] == 'buy'):?>
	<span id="fk-banner"><a href="//www.free-kassa.ru"><img src="https://www.free-kassa.ru/img/fk_btn/16.png" title="Бесплатный видеохостинг"></a></span>
	<?php endif;?>
</nav>