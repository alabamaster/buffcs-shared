<div class="container">
	<div class="row mx-md-n1">
		<div class="col-lg-5 px-md-1">
			<div class="box">
				<form action="<?=$this->SITE_URL?>buy" class="p-2" autocomplete="off" method="POST">

					<!-- type and email  -->
					<div id="row-to-block" class="row">
						<div class="col">
							<div class="form-group">
								<label><i class="fa fa-key"></i> Тип</label>
								<select name="type" id="type" class="form-control form-control-sm">
									<option value="a">Ник + пароль</option>
									<option value="ac">SteamID + пароль</option>
								</select>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label><i class="fa fa-envelope-o"></i> Email</label>
								<input id="email" name="email" type="email" class="form-control form-control-sm" placeholder="На него придут данные" minlength="5" maxlength="30" data-toggle="tooltip" data-placement="top" title="Вводите настоящий Email! Если Вы забудете пароль, он понадобится" required="">
							</div>
						</div>
					</div>

					<!-- username and password -->
					<div id="row-to-block" class="row">
						<div class="col">
							<div class="form-group">
								<div id="label" class="d-inline-block" style="margin-bottom: 0.5rem;">
									<i class="fa fa-user-o"></i> Ник 
								</div>
								<input name="nickname" id="userName" data-toggle="tooltip" data-placement="top" title="Ваш ник в игре" type="text" class="form-control form-control-sm" placeholder="A-Z, a-z, 0-9" required="">
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label><i class="fa fa-lock"></i> Пароль</label>
								<input name="password" id="password" data-toggle="tooltip" data-placement="top" title="Придумайте пароль для активации привилегии" type="text" class="form-control form-control-sm" required="" minlength="3" maxlength="20" placeholder="A-Z, a-z, 0-9">
							</div>
						</div>
					</div>

					<!-- server and privileges  -->
					<div id="row-to-block" class="row">
						<div class="col">
							<div class="form-group">
								<label><i class="fa fa-server"></i> Сервер</label>
								<select name="server" id="server" class="form-control form-control-sm">
									<option value="0" selected="" disabled="">Выберите сервер</option>
									<?php foreach ($servers as $row):?>
										<option value="<?=$row['id']?>"><?=$row['hostname']?></option>
									<?php endforeach;?>
								</select>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label><i class="fa fa-star-half-o"></i> Привилегия</label>
								<select name="privilege" id="privilege" class="form-control form-control-sm">
									<option value="0" selected="" disabled="">Выберите привилегию</option>
								</select>
							</div>
						</div>
					</div>

					<!-- time and shop  -->
					<div id="row-to-block" class="row">
						<div class="col">
							<div class="form-group">
								<label><i class="fa fa-clock-o"></i> Срок</label>
								<select name="days" id="days" class="form-control form-control-sm">
									<option value="-1" selected="" disabled="">Выберите срок</option>
								</select>
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label><i class="fa fa-money"></i> Способ оплаты</label>
								<select name="shop" id="shop" class="form-control form-control-sm">
									<?=$htmlShops?>
								</select>
							</div>
						</div>
					</div>

					<!-- vk  -->
					<div class="form-group">
						<label><i class="fa fa-vk"></i> Вконтакте</label>
						<input name="vk" id="vk" type="text" class="form-control form-control-sm" data-toggle="tooltip" data-placement="top" title="Ссылка на VK без https://" maxlength="40" placeholder="vk.com/player1337" minlength="8" maxlength="40">
					</div>

					<?php if(!isset($_SESSION['authorization'])):?>
					<!-- promocode  -->
					<div class="row align-items-center">
						<div class="col-sm-8">
							<input name="promoCode" id="code" type="text" class="form-control form-control-sm" placeholder="Промокод">
						</div>
						<div class="col-sm-4">
							<button id="checkPromoCode" type="button" class="fc-button fc-button-green">Проверить</button>
						</div>
					</div>
					
					<!-- checkbox rules -->
					<div class="row my-2">
						<div class="col-sm-8">
							<div class="custom-control custom-checkbox mr-sm-2">
								<input type="checkbox" class="custom-control-input" id="checkboxRules" name="checkboxRules">
								<label class="custom-control-label" for="checkboxRules">С <a href="<?=$urlRules?>" target="_blank">правилами</a> ознакомлен</label>
							</div>
						</div>
						<!-- <div class="col-sm-4 d-flex justify-content-center">
							<a href="#" class="promo-link" data-toggle="modal" data-target="#promoCode"><i class="fa fa-angle-double-right"></i> Промокод</a>
						</div> -->
					</div>

					<input type="hidden" name="thisBuyForm" value="1">
					<button type="submit" class="fc-button fc-button-blue btn-block goPay" name="goPay">Оплатить</button>
					<?php else:?>
						<div class="mess mess-info text-wrap mt-3">Купить новую привилегию в <a href="<?=$this->SITE_URL?>account/profile/buy">личном кабинете</a></div>
					<?php endif;?>
				</form>
			</div>
		</div>
		<div class="col-lg-7 px-md-1" id="two-block">
			<div class="box">
				<?php if($disc['active'] == 1 && $disc['mess_animated'] == 1):?>
				<div class="mess mess-warn mb-0 animated infinite bounce delay-1s">
					<p class="m-0">Сейчас действует скидка <span class="font-weight-bold">на все привилегии в <?=$disc['discount']?>%</span>, успей купить!</p>
				</div>
				<?php elseif($disc['active'] == 1 && $disc['mess_animated'] == 2):?>
				<div class="mess mess-warn mb-2">
					<p class="m-0">Сейчас действует скидка <span class="font-weight-bold">на все привилегии в <?=$disc['discount']?>%</span>, успей купить!</p>
				</div>
				<?php endif;?>
				<div id="mess-info" class="mb-2">
					<?=$infoBlock?>
				</div>
				<div id="server-mon"></div>
				<div id="about-privilege"></div>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
	const type 		= document.querySelector('#type');
	const label 	= document.querySelector('#label');
	const userName 	= document.querySelector('#userName');
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

	<?php if(!isset($_SESSION['authorization'])):?>
	document.querySelector('#checkboxRules').required = true;
	
	btnCheckPromo.addEventListener('click', () => {
		let server	= document.querySelector('#server');
		let tariff	= document.querySelector('#privilege');
		let code	= document.querySelector('#code');

		customFetch('<?=$this->SITE_URL?>buy', `thisPromoCode=${code.value}&server=${server.value}&tariff=${tariff.value}`);
	});
	<?php endif;?>

	type.addEventListener('change', () => 
	{
		if ( type.value == 'a' ) {
			label.innerHTML = '<i class="fa fa-user-o"></i> Ник ';
			userName.name = 'nickname';
			userName.placeholder = 'player1337';
			userName.minlength = 3;
			userName.maxlength = 32;
		} else if ( type.value == 'ac' ) {
			label.innerHTML = '<i class="fa fa-steam"></i> SteamID ';
			userName.name = 'steamid';
			userName.placeholder = 'STEAM_0:123456789';
			userName.minlength = 5;
			userName.maxlength = 25;
		} else {
			alert('type error');
			return false;
		}
	});

	document.querySelector('#server').addEventListener('change', () => {
		document.querySelector('#about-privilege').innerHTML = '';
	});
});
</script>
