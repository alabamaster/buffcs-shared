<?php require_once 'navbar.php'; ?>
<div class="container mt-2">
	<div class="row mx-md-n1">
		<div class="col-md-12 px-md-1">
			<div class="box">
				<div class="alert alert-info">Поиск по всем игрокам в базе денных</div>
					<form action="#" method="POST" class="w-100" id="no_ajax">
						<div class="d-flex justify-content-between">
							<div class="form-group w-100 mr-2">
								<input name="username" id="username" type="text" class="form-control" placeholder="Ник / SteamID">
							</div>
							<div class="form-group">
								<button class="btn btn-info mb-1" type="submit" id="btnSearch" style="width: 150px;">Поиск</button>
							</div>
						</div>
					</form>
				<div id="amxadmins"></div>
			</div>
		</div>
	</div>
</div>
<script>
	// search form
	$('#no_ajax').submit(function(event) {
		let json;
		event.preventDefault();

		if ( event.target.id != 'no_ajax' ) {
			return false;
		}

		$.ajax({
			type: $(this).attr('method'),
			url: $(this).attr('action'),
			data: new FormData(this),
			contentType: false,
			cache: false,
			processData: false,

			beforeSend: function() {
				document.querySelector('#btnSearch').innerHTML = '<i class="fa fa-refresh fa-spin fa-fw"></i>';
				document.querySelector('#amxadmins').innerHTML = '<div class="d-flex justify-content-center mt-4"><i class="fa fa-spinner fa-2x fa-spin fa-fw"></i></div>';
			},

			success: function(result) {
				json = jQuery.parseJSON(result);
				document.querySelector('#username').value = '';
				document.querySelector('#btnSearch').textContent = 'Поиск';
				document.querySelector('#amxadmins').textContent = '';
				
				if(json.url) {
					window.location.href = json.url;
				} else if (json.reload) {
					document.location.reload(true);
				} else if (json.adminAmxadmins) {
					let data = json.data;
					const divAmxadmins = document.querySelector('#amxadmins');

					data.forEach(item => {
						const adminCard = document.createElement('div');
						const adminCardBody = document.createElement('div');

						const createdTime = new Date(item.created * 1000).toLocaleString("ru-RU");
						let expiredTime;

						if ( item.expired == 0 ) {
							expiredTime = 'Никогда';
						} else {
							expiredTime = new Date(item.expired * 1000).toLocaleString("ru-RU");
						}

						adminCard.append(adminCardBody);

						adminCard.classList.add('card');
						adminCard.classList.add('my-1');
						adminCardBody.classList.add('card-body');

						adminCardBody.innerHTML = `
							<form action="#" method="POST">
								<div class="row align-items-center">
									<div class="col-md-3"><b>username (#id ${item.id})</b> <br>${item.username}</div>
									<div class="col-md-3">
										<b>access</b> <br>
										<input type="text" value="${item.access}" class="form-control form-control-sm" id="access${item.id}" name="access${item.id}">
									</div>
									<div class="col-md-1"><b>flags</b> <br>${item.flags}</div>
									<div class="col-md-2"><b>created</b> <br>${createdTime}</div>
									<div class="col-md-2"><b>expired</b> <br>${expiredTime}</div>
									<div class="col-md-1"><button onclick="save(this)" id="${item.id}" class="btn btn-success btn-sm" type="button">Save</button></div>
								</div>
							</form>
						`;

						divAmxadmins.append(adminCard);
					})
				} else {
					alert(json.status + ' - ' + json.message);
				}
			},
			error: function() {
				console.log('js -> submit search form: response error!');
			},
		});
	});

	// update access
	const save = e => {
		// console.log(e.id);

		let getAccess = document.querySelector('#access' + e.id);
		let newAccess = getAccess.value;
		let uid = e.id;

		let json;

		fetch('#', {
			method: 'POST',
			mode: 'cors',
			cache: 'no-cache',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'changeAccess=1&uid=' + uid + '&newAccess=' + newAccess,
		})
		.then( response =>  {
			if ( response.status == 200 ) {
				return response.text();
			} else {
				return false;
			}
		})
		.then( response => {
			json = JSON.parse(response);
			if (json.reload) {
				document.location.reload();
			} else if (json.status)  {
				alert(json.status + ' - ' + json.message);
			} else {
				console.log('js -> save: response json error');
			}
		})
	};
</script>