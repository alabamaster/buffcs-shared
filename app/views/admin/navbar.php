<div class="container">
	<nav class="navbar navbar-expand-md navbar-dark p-2 bg-secondary rounded">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarAdmin">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item">
					<a class="nav-link" href="<?=$this->SITE_URL?>admin/home">Главная</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?=$this->SITE_URL?>admin/addprivileges">Привилегии</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?=$this->SITE_URL?>admin/adduser">Добавить юзера</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?=$this->SITE_URL?>admin/promo">Промо</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?=$this->SITE_URL?>admin/infoprivileges">Инфо привилегии</a>
				</li>
				<li class="nav-item">
					<a class="nav-link text-warning" href="<?=$this->SITE_URL?>admin/exit">Выйти</a>
				</li>
			</ul>
		</div>
	</nav>
</div>