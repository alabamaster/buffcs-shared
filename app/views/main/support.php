<div class="container">
	<div class="row mx-md-n1">
		<div class="col-md-6 m-auto px-md-1">
			<div class="box">
				<div class="d-flex justify-content-center flex-column">
					<h4 class="d-flex justify-content-center mt-3">Напишите нам на почту</h4>
					<form action="<?=$this->SITE_URL?>support" method="POST">
						<div class="form-group">
							<label><i class="fa fa-envelope-o"></i> Ваш Email</label>
							<input name="email" type="email" class="form-control" required="">
						</div>
						<div class="form-group">
							<label><i class="fa fa-link"></i> Ссылка на любую Вашу соц. сеть</label>
							<input name="socialLink" type="text" class="form-control">
						</div>
						<div class="form-group">
							<label><i class="fa fa-pencil"></i> Сообщение</label>
							<textarea name="message" class="form-control" cols="5" minlength="10" maxlength="200" required=""></textarea>
							<small class="text-muted">От 10 до 200 символов</small>
						</div>
						<div class="d-flex justify-content-between">
							<button class="fc-button fc-button-green" type="submit">Отправить</button>
							<a href="<?=$vkGroup?>" target="_blank" class="a-link-connect text-white"><button class="fc-button fc-button-blue" type="button">Группа Вконтакте</button></a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>