<div class="container">
	<div class="row">
		<div class="col-md-8 mx-auto">
			<div class="box">
				<div class="text-center">
					<h3 class="text-success"><!-- Payment was successful! -->Оплата прошла успешно!</h3>
					<div class="d-flex align-content-start" style="flex-direction: column;">
						<div><p class="m-0">Номер заказа: <b><?=$data['id']?></b></p></div>
					<?php if($data['core_id'] != 3):?>
						<div><p class="m-0">Привилегия: <b><?=$data['tariff']?></b></p></div>
						<div><p class="m-0">Окончание: <b><?=$data['expired']?></b></p></div>
						<?php if($data['core_id'] == 1):?>
						<div><p class="m-0 text-muted">Мы отправили Вам письмо на почту с Вашими данными!</b></p></div>
						<?php endif;?>
					<?php endif;?>
						<div><p class="mb-0 mt-3 text-dark font-weight-bold">GL & HF</b></p></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>