<?php 
use app\models\Main;
$MM = new Main;
?>
<div class="row mx-md-1">
	<div class="col-md-12 mt-2 px-md-1">
		<div class="box">
			<div class="row mx-md-n1">
				<div class="col-md-4 border-right px-md-1">
					<form action="<?=$this->SITE_URL?>account/profile/change" method="POST" class="mt-2 pr-2">
						<div style="margin-bottom: 10px;">
							<select class="form-control form-control-sm" id="tarif_id" name="tarif_id">
								<option value="0" selected disabled>Выберите привилегию</option>
								<?php foreach ($MM->getPrivilegesFromServerId($user_data['server_id']) as $row):?>
									<?php if($row['id'] == $user_data['tarif_id']) continue;?>
									<option value="<?=$row['id']?>"><?=$row['name']?></option>
								<?php endforeach;?>
							</select>
							<input type="hidden" name="uid" value="<?=$user_data['id']?>">
							<input type="hidden" name="hidden" id="hidden" value="0">
						</div>
						<button id="changeMe" type="submit" class="fc-button fc-button-orange">Изменить привилегию</button>
					</form>
				</div>
				<div class="col-md-4 border-right px-md-1">
					<div>
						<span>Текущая привилегия</span>
						<ul style="padding-left: 20px;">
							<li><?=$MM->getPrivilegeNameById($user_data['tarif_id'])?></li>
							<li>Остаётся <b>~ <?=$currentInfo['days_left']?> дн.</b></li>
							<li>Псевдо баланс <b><?=$currentInfo['p_balance']?> руб.</b></li>
						</ul>
					</div>
				</div>
				<div class="col-md-4" id="jsResponse">Расчет данных выбранной привилегии</div>
			</div>
		</div>
	</div>
</div>
<script>
	document.addEventListener('DOMContentLoaded', () => 
	{
		const submitChange 	= document.querySelector('#changeMe');
		const tarifSelect 	= document.querySelector('#tarif_id');
		const hidden 		= document.querySelector('#hidden');
		
		submitChange.addEventListener('click', () => {
			if ( tarifSelect.value == 0 ) {
				// tarifSelect.classList.add('is-invalid');
				return;
			} else {
				hidden.value = 1;
				// tarifSelect.classList.remove('is-invalid');
			}
		});
		// tarifSelect.addEventListener('click', () => {
		// 	if ( tarifSelect.value != 0 ) {
		// 		tarifSelect.classList.remove('is-invalid');
		// 		return;
		// 	}
		// });

		// выводим инфу
		tarifSelect.addEventListener('change', (e) => {
			const url = '<?=$this->SITE_URL?>' + 'app/js_post/actionProfileChange.php';
			let tarif_id = e.target.value;
			let tarif_expired = <?=$user_data['expired']?>;

			if ( tarif_expired == 0 ) {
				return false;
			}

			// const btnChangeTarif 	= document.querySelector('#goChangeTarif');
			const jsResponse 		= document.querySelector('#jsResponse');
			const user_id 			= <?=$user_data['id'];?>;
			const tarif_price 		= <?=$currentInfo['price'];?>;
			const pseudo_balance 	= <?=$currentInfo['p_balance'];?>;

			fetch(url, {
				method: 'POST',
				mode: 'cors',
				cache: 'no-cache',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'tarif_id=' + tarif_id + '&user_id=' + user_id + '&tarif_price=' + tarif_price + '&pseudo_balance=' + pseudo_balance,
			})
			.then( response =>  {
				if ( response.status == 200 ) {				
					return response.text();
				} else {
					return false;
				}
			} /*console.log(response)*/ )
			.then( response => {
				// console.log(response);
				jsResponse.innerHTML = response;
			});
		});
	});
</script>