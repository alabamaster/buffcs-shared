<?php use app\models\Account;?>
<div class="row mx-md-1">
	<div class="col-md-7 mt-2 px-md-1">
		<div class="box" style="padding-bottom: 19px;">
			<form action="<?=$this->SITE_URL?>account/profile/update" method="POST">
				<div class="form-group">
					<label>Выберите время</label>
					<select name="days" class="form-control">
						<?php foreach ($pTime as $row):
							$price = ($disc['active'] == 1) ? Account::discount($row['price'], $disc['discount']) : $row['price'];
						?>
							<option value="<?=$row['time']?>"><?=Account::timeName($row['time'])?> - <?=$price?> руб.</option>
						<?php endforeach;?>
					</select>
				</div>
				<div class="form-group">
					<label>Касса</label>
					<select name="shop" class="form-control">
						<div class="form-group"><?=$htmlShops?></div>
					</select>
				</div>
				<input type="hidden" name="updateUserTime" value="1">
				<div class="d-flex align-items-center justify-content-center">
					<button class="fc-button fc-button-blue btn-block">Продлить</button>
				</div>
			</form>
		</div>
	</div>
	<div class="col-md-5 mt-2 px-md-1">
		<div class="box">
			<?php require_once 'side_mon.php'; ?>
		</div>
	</div>
</div>
