<?php use app\models\Main;
$MAIN = new Main;
?>
<div class="row mx-md-1">
	<div class="col-lg-6 mt-2 px-md-1">
		<div class="box">
			<form action="<?=$this->SITE_URL?>account/profile/buy" class="p-2" autocomplete="off" method="POST">
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Сервер</label>
							<select name="server" id="server" class="form-control form-control-sm">
								<option value="0" selected="" disabled="">Выберите сервер</option>
								<?php foreach ($servers as $row):?>
									<option value="<?=$row['id']?>"><?=$row['hostname']?></option>
								<?php endforeach;?>
							</select>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label>Привилегия</label>
							<select name="privilege" id="privilege" class="form-control form-control-sm">
								<option value="0">Выберите привилегию</option>
							</select>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label>Срок</label>
							<select name="days" id="days" class="form-control form-control-sm">
								<option value="-1">Выберите срок</option>
							</select>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group mb-0">
							<label>Касса</label>
							<select name="shop" id="shop" class="form-control form-control-sm">
								<?=$MAIN->htmlSelectShops()?>
							</select>
						</div>
					</div>
				</div>


				<!-- promocode  -->
				<div class="row align-items-center">
					<div class="col-lg-9">
						<input name="promoCode" id="code" type="text" class="form-control form-control-sm" placeholder="Промокод">
					</div>
					<div class="col-lg-3"><button id="checkPromoCode" type="button" class="fc-button fc-button-green">Проверить</button></div>
				</div>

				<div class="d-flex justify-content-between mt-2 mb-2">
					<div class="custom-control custom-checkbox mr-sm-2">
						<input type="checkbox" class="custom-control-input" id="checkboxRules" required="">
						<label class="custom-control-label" for="checkboxRules">С <a href="#">правилами</a> ознакомлен</label>
					</div>
				</div>
				<button type="submit" class="fc-button fc-button-blue btn-block goPay" id="goPay" name="goPay">Оплатить</button>
			</form>
		</div>
	</div>
	<div class="col-lg-6 mt-2 px-md-1">
		<div class="box" style="height: auto;">
			<div id="desc">
				<h6>Здесь вы можете купить другую привилегию</h6>
				<p>
					Стоит отметить:<br>
					<ul>
						<li>Новая привилегию перебьет старую</li>
						<li>
							Дни не добавляются <br><i>(если у вас осталось 5 дней и вы купите новую привилегию на 30 дней, у вас будет 30 дней, а не 35)</i>
						</li>
						<li>Продлить текущую привилегию можно <a href="/account/profile/update">здесь</a></li>
					</ul>
				</p>
			</div>
			<div id="server-mon"></div>
			<div id="about-privilege" style="overflow: auto;white-space: normal;max-height: 260px;margin-top: 10px;"></div>
		</div>
	</div>
</div>
<!-- modal promocode -->
<div class="modal fade" id="promoCode" tabindex="-1" role="dialog" aria-labelledby="promoCode" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="promoCode">Использовать промокод</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">×</span>
				</button>
			</div>
			<form action="/buy" method="POST" autocomplete="off">
				<div class="modal-body">
						<div id="checkPromoCode"></div>
						<div class="d-flex">
							<div class="number" style="font-size: 26px;margin-right: 15px;">1</div>
							<div class="form-group w-100">
								<select id="promoServer" name="promoServer" class="form-control">
									<option value="0" selected="" disabled="">Выберите сервер</option>
									<<?php foreach ($promo_ser as $row):?>
										<option value="<?=$row['id']?>"><?=$row['hostname']?></option>
									<?php endforeach;?>
								</select>
							</div>
						</div>

						<div class="d-flex">
							<div class="number" style="font-size: 26px;margin-right: 15px;">2</div>
							<div class="form-group w-100">
								<select class="form-control" id="promoPrivilege" name="promoPrivilege">
									<option value="0" selected="" disabled="">Выберите привилегию</option>
								</select>
							</div>
						</div>

						<div class="d-flex">
							<div class="number" style="font-size: 26px;margin-right: 15px;">3</div>
							<div class="form-group w-100" id="hiddenInputPromo">
								<input id="thisPromoCode" name="thisPromoCode" type="text" class="form-control" placeholder="Введите промокод">
							</div>
						</div>
				</div>
				<div class="modal-footer p-1">
					<button type="button" class="fc-button fc-button-dark" data-dismiss="modal">Закрыть</button>
					<button name="enterPromoCode" type="submit" class="fc-button fc-button-green">Подтвердить</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- modal promocode //  -->
<script>
	const btnPay 	=  document.querySelector('#goPay');
	const desc 		= document.querySelector('#desc');

	let server 		= document.querySelector('#server');
	let privilege 	= document.querySelector('#privilege');
	let days 		= document.querySelector('#days');

	document.querySelector('#checkboxRules').required = true;

	// selects in promo modal window
	document.querySelector('#promoServer').addEventListener('change', () => {
		$.ajax({
			type: 'POST',
			url: '<?=$this->SITE_URL?>app/js_post/actionProfileBuy.php',
			data: 'case=1&server_id='+document.querySelector('#promoServer').value,
			success: function(data){
				$("#promoPrivilege").html(data);
			},
			error: function() {
				console.log('ajax server error');
			}
		});
	});

	server.addEventListener('change', () => {
		desc.innerHTML = '';
		$.ajax({
			type: 'POST',
			url: '<?=$this->SITE_URL?>app/js_post/actionProfileBuy.php',
			data: 'case=1&server_id='+server.value,
			success: function(data){
				$("#privilege").html(data);
			},
			error: function() {
				console.log('ajax server error');
			}
		});

		$.ajax({
			type: 'POST',
			url: '<?=$this->SITE_URL?>app/js_post/actionProfileBuy.php',
			data: 'case=3&server_id='+server.value,
			success: function(data){
				$("#server-mon").html(data);
			},
			error: function() {
				console.log('ajax server error');
			}
		});

		days.innerHTML = '<option value="-1">Выберите срок</option>';
	});

	privilege.addEventListener('change', () => {
		$.ajax({
			type: 'POST',
			url: '<?=$this->SITE_URL?>app/js_post/actionProfileBuy.php',
			data: 'case=2&privilege_id='+privilege.value,
			success: function(data){
				$("#days").html(data);
			},
			error: function() {
				console.log('ajax privilege error');
			}
		});

		$.ajax({
			type: 'POST',
			url: '<?=$this->SITE_URL?>app/js_post/actionProfileBuy.php',
			data: 'case=4&privilege_id='+privilege.value,
			success: function(data){
				$("#about-privilege").html(data);
			},
			error: function() {
				console.log('ajax case4 error');
			}
		});
	});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
	const btnCheckPromo = document.querySelector('#checkPromoCode');
	const divMess 	= document.querySelector('#mess');
	let json;

	const hideMess = (div, delay) => {
		setTimeout(()=> {
				div.classList.add('animated', 'fadeOut');
				setTimeout(()=> {
					div.innerHTML = '';
				}, 500);
			}, delay);
		div.classList.remove('animated', 'fadeOut');
	};

	const customFetch = (strUrl, strBody) => {
		fetch(strUrl, {
			method: 'POST',
			mode: 'cors',
			cache: 'no-cache',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: strBody,
		})
		.then( response =>  {
			if ( response.status == 200 ) {
				return response.text();
			} else {
				return false;
			}
		})
		.then( response => {
			json = jQuery.parseJSON(response);
			
			if(json.url) {
				window.location.href = json.url;
			} else if (json.reload) {
				document.location.reload(true);
			} else {
				if ( json.status == 'success' ) {
					divMess.innerHTML = `<div class="mess-v2 mess-v2-ok animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				} else if( json.status == 'error' ) {
					divMess.innerHTML = `<div class="mess-v2 mess-v2-error animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				} else if( json.status == 'warning' ) {
					divMess.innerHTML = `<div class="mess-v2 mess-v2-warn animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				} else {
					divMess.innerHTML = `<div class="mess-v2 mess-v2-info animated flipInX">${json.message}</div>`;
					hideMess(divMess, 5000);
				}
			}
		})
	};

	btnCheckPromo.addEventListener('click', () => {
		let server	= document.querySelector('#server');
		let tariff	= document.querySelector('#privilege');
		let code	= document.querySelector('#code');

		customFetch('<?=$this->SITE_URL?>account/profile/buy', `thisPromoCode=${code.value}&server=${server.value}&tariff=${tariff.value}`);
	});
});
</script>