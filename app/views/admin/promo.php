<?php require_once 'navbar.php'; ?>
<div class="container mt-2">
	<div class="row mx-md-n1">
		<div class="col-lg-4 px-md-1">
			<div class="box">
				<form class="form" action="<?=$this->SITE_URL?>admin/promo" method="POST">
					<div class="form-group">
						<select class="form-control form-control-sm" id="selectServer" name="selectServer">
							<option value="0" selected disabled>Выберите сервер</option>
							<?php foreach ($servers as $row):?>
								<option value="<?=$row['id']?>"><?=$row['hostname']?></option>
							<?php endforeach;?>
						</select>
					</div>
					<div class="form-group w-100">
						<select class="form-control form-control-sm" id="selectTarif" name="selectTarif">
							<option value="0" selected disabled>Выберите привилегию</option>
						</select>
					</div>
					<div class="form-group">
						<label>Кол-во дней действия промокода</label>
						<input name="countDays" id="countDays" type="text" class="form-control form-control-sm" required="">
					</div>
					<div class="form-group">
						<label>Кол-во раз использования промокода</label>
						<input name="countUse" id="countUse" type="text" class="form-control form-control-sm" required="">
						<small class="text-muted">Чтобы код действовал неограниченное кол-во раз, укажите <b>-1</b></small>
					</div>
					<div class="form-group">
						<label>Скидка промокода</label>
						<input name="codeDiscount" id="codeDiscount" type="text" class="form-control form-control-sm" required="">
						<small class="text-muted">Указывать только целое число, <b>без знака %</b><br>Скидка промокода и глобальная складываются вместе!</small>
					</div>
					<div class="d-flex justify-content-between align-items-center">
						<div class="form-group w-100">
							<label>Промокод</label>
							<div id="divCode">
								<input name="inputCode" id="inputCode" type="text" class="form-control form-control-sm" minlength="3" maxlength="32" placeholder="От 3 до 32 символов (a-z, A-Z, 0-9)" required="">
							</div>
						</div>
					</div>
					<input type="hidden" name="saveCode" value="1">
					<button name="genPromocode" id="genPromocode" type="button" class="fc-button fc-button-blue">Случайный промокод</button>
					<button type="submit" id="saveCode" class="fc-button fc-button-green" style="cursor: pointer;">Сохранить промокод</button>
				</form>
			</div>
		</div>
		<div class="col-lg-8 px-md-1">
			<div class="box">
				<div id="codeResult"></div>
				<div class="table-responsive">
					<table class="table table-sm table-hover" style="font-size: 12px">
						<thead class="bg-secondary text-white">
							<tr>
								<td class="font-weight-bold border-0">Привилегия</td>
								<td class="font-weight-bold border-0">Сервер</td>
								<td class="font-weight-bold border-0">Код</td>
								<td class="font-weight-bold border-0">Скидка</td>
								<td class="font-weight-bold border-0">Дата</td>
								<td class="font-weight-bold border-0">Кол-во</td>
								<td class="font-weight-bold border-0"></td>
							</tr>
							</thead>
							<tbody>
								<?php foreach ($codes as $row):?>
								<tr>
									<td>
										<div class="row">
											<div class="col text-truncate" style="max-width: 200px;">
												<?php echo $model->getPrivilegeNameById($row['pid']);?>
											</div>
										</div>
									</td>
									<td>
										<div class="row">
											<div class="col text-truncate" style="max-width: 200px;">
												<?php echo $model->getServerNameById($row['sid']);?>
											</div>
										</div>
									</td>
									<td style="font-size: 14px;">
										<button onclick="copyCode(this.value)" style="border: none;background-color: transparent;" value="<?=$row['code']?>"><i class="fa fa-eye" aria-hidden="true"></i></button>
									</td>
									<td><?=$row['discount']?>%</td>
									<td style="font-size: 14px;">
										<span data-toggle="tooltip" data-placement="right" title="<?php echo date('d.m.Y', $row['dateCreated']) . ' - ' . date('d.m.Y', $row['dateExpired']);?>"><i class="fa fa-calendar" aria-hidden="true"></i></span>
									</td>
									<td><?=$row['count_use']?></td>
									<td style="font-size: 14px;">
										<button id="deleteCode" onclick="deleteCode(this.value)" class="text-danger" title="Удалить" style="border: none;background-color: transparent;" value="<?=$row['id']?>"><i class="fa fa-trash-o" aria-hidden="true"></i>
										</button>
									</td>
								</tr>
								<?php endforeach;?>
							</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	const btnCreateCode = document.querySelector('#genPromocode');

	let server 		= document.querySelector('#selectServer');
	let privilege 	= document.querySelector('#selectTarif');

	const createCode = (length) => {
		let result           = '';
		let characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		let charactersLength = characters.length;
		for ( let i = 0; i < length; i++ ) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	};

	const copyCode = (e) => {
		alert('Promocode: ' + e);
	};

	const customFetch = (strUrl, strBody, strDivForResponse) => {
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
		} /*console.log(response)*/ )
		.then( response => {
			// console.log(response);
			strDivForResponse.innerHTML = response;
			// json = jQuery.parseJSON(response);
			
			// if(json.url) {
			// 	window.location.href = json.url;
			// } else if(json.reload) {
			// 	document.location.reload(true);
			// } else {
			// 	strDivForResponse.innerHTML = `<div class="mb-2 mess-v2 mess-v2-ok animated flipInX">${json.message}</div>`;
			// }
		});
	};

	server.addEventListener('change', (e) => {
		customFetch('<?=$this->SITE_URL?>app/js_post/actionAdminPromo.php', 'case=1&server_id='+server.value, privilege);
	});

	btnCreateCode.addEventListener('click', () => {
		const inputCode = document.querySelector('#inputCode');
		const divCode = document.querySelector('#divCode');
		let randomCode = createCode(5);

		divCode.innerHTML = `<input name="inputCode" id="inputCode" type="text" class="form-control form-control-sm" minlength="3" maxlength="32" placeholder="От 3 до 32 символов (a-z, A-Z, 0-9)" value="${randomCode}" required="">`;
		// inputCode.setAttribute('value', '');
		// inputCode.setAttribute('value', createCode(12));
	});

	const deleteCode = (e) => {
		const divCodeResult = document.querySelector('#mess');
		// customFetch('/admin/promo', 'deleteCode=1&code_id='+e, divCodeResult);
		fetch('<?=$this->SITE_URL?>admin/promo', {
			method: 'POST',
			mode: 'cors',
			cache: 'no-cache',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: `deleteCode=1&code_id=${e}`,
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
			}
			if (json.reload) {
				document.location.reload(true);
			}
		});
	};
</script>