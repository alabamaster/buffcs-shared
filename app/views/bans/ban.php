<link rel="stylesheet" href="<?=$this->SITE_URL?>themes/default/css/flag-icon.css">
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div class="box" style="position: relative;">
				<div class="ban-head">
					<div class="text-center font-weight-bold"><h2><?= $data['player_nick'] ?></h2></div>
					<div class="text-center font-weight-normal"><h6><?= $data['server_name'] ?></h6></div>
				</div>
				<div class="ban-buttons">
					<!-- button buy unban -->
					<?php if ( !$model->bansExpiredCalc($data['ban_created'], $data['expired'], $data['ban_length'], true) ):?>
					<div class="dropdown" data-toggle="tooltip" data-placement="top" title="Бан будет снят в течении 15ти минут">
						<button class="fc-button fc-button-green dropdown-toggle" type="button" id="buyUnban" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fa fa-usd" aria-hidden="true"></i> Купить разбан (<?php echo $ban_price .' '. $currency;?>)
						</button>
						<div class="dropdown-menu" aria-labelledby="buyUnban" style="background-color: ##F2F2F3 !important">
							<div class="text-center">Способы оплаты</div>
							<div class="dropdown-divider"></div>
							<?php if($fk_active == 1):?>
								<button class="dropdown-item" id="freekassa" onclick="goPayUnban(this.id)">Freekassa</button>
							<?php endif;?>
							<?php if($rk_active == 1):?>
								<button class="dropdown-item" id="robokassa" onclick="goPayUnban(this.id)">Robokassa</button>
							<?php endif;?>
							<?php if($up_active == 1):?>
								<button class="dropdown-item" id="unitpay" onclick="goPayUnban(this.id)">UnitPay</button>
							<?php endif;?>
						</div>
					</div>
					<?php endif;?>
					<!-- button buy unban // -->

					<!-- button unban // -->
					<?php if(isset($_SESSION['admin']) && @$_SESSION['admin'] === true):?>
					<div class="admin-buttons">
						<?php if ( !$model->bansExpiredCalc($data['ban_created'], $data['expired'], $data['ban_length'], true) ):?>
							<form action="<?=$this->SITE_URL?>bans/ban<?=$data['bid']?>" method="POST">
								<input type="hidden" name="ban_id" value="<?=$data['bid']?>">
								<button class="fc-button fc-button-orange d-block" type="submit"><i class="fa fa-gavel" aria-hidden="true"></i> Разбанить</button>
							</form>
						<?php endif;?>
					</div>
					<?php endif;?>
					<!-- button unban // -->
				</div>
				<hr>
				<div class="row">
					<div class="col-lg-6">
						<ul class="list-group list-group-flush">
							<li class="list-group-item">Срок <b><?=$model->bansExpiredCalc($data['ban_created'], $data['expired'], $data['ban_length'])?></b></li>
							<li class="list-group-item">Дата бана <b><?=date('d.m.Y в H:i', $data['ban_created'])?></b></li>
							<li class="list-group-item">Забанил <b><?=htmlspecialchars($data['admin_nick'])?></b></li>
							<li class="list-group-item">Причина <b><?=$data['ban_reason']?></b></li>
						</ul>
					</div>
					<div class="col-lg-6 ban-col-two">
						<ul class="list-group list-group-flush">
							<li class="list-group-item">IP <b><?php echo $ip = ($ban_cfg['hide_ip'] == 1) ? '***' : $data['player_ip']?></b></li>
							<li class="list-group-item">SteamID <b><?php echo $steam = ($ban_cfg['hide_id'] == 1) ? '***' : $data['player_id']?></b></li>
							<li class="list-group-item">Киков <b><?php echo $kicks = ($data['ban_kicks'] == 0) ? 0 : $data['ban_kicks']?></b></li>
							<li class="list-group-item d-flex align-items-center">
								<?php $arr = $SERVERS->getGeoIP($data['player_ip']);
								echo '<div>'.ucfirst($arr['name']).'</div>';
								echo '<div class="flag-icon flag-icon-'.$arr['code'].' ml-2"></div>';
								?>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	const customFetchJson = (strUrl, strBody) => {
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
			} else if(json.reload) {
				document.location.reload(true);
			} else {
				divMess.innerHTML = `<div class="mess-v2 mess-v2-info animated flipInX">${json.message}</div>`;
			}
		})
	};

	const goPayUnban = (e) => {
		customFetchJson('<?=$this->SITE_URL?>bans/ban<?=$data['bid']?>', 'buyUnban=1&bid=<?=$data['bid']?>&shop='+e);
	}
</script>