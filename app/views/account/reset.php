<div class="container">
	<div class="row d-flex justify-content-center">
		<div class="col-md-6 bg-white-rbg rounded">
			<div class="box">
				<h3 class="text-center">Сброс пароля</h3>
				<form action="<?=$this->SITE_URL?>account/reset" method="POST" autocomplete="off">
					<div class="form-group">
						<input name="email" class="form-control" type="email" placeholder="Email привязанный к аккаунту">
						<small class="text-muted">На указанный Email будет отправлен новый пароль</small>
					</div>
					<button type="submit" class="fc-button fc-button-orange btn-block" name="goResetPassword">Сбросить</button>
				</form>
			</div>
		</div>
	</div>
</div>