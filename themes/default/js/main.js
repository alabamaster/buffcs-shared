document.addEventListener('DOMContentLoaded', () => 
{
	$('[data-toggle="tooltip"]').tooltip();

	const divMess = document.querySelector('#mess');

	const hideMess = (div, delay) => {
		setTimeout(()=> {
				div.classList.add('animated', 'fadeOut');
				setTimeout(()=> {
					div.innerHTML = '';
				}, 500);
			}, delay);
		div.classList.remove('animated', 'fadeOut');
	};

	const viewMessage = (title, div) => {
		div.innerHTML = `<div class="mess mess-ok animated flipInX">${title}</div>`;
	};

	$('form').submit(function(event) {
		if( $(this).attr('id') == 'no_ajax') return;

		let json;
		event.preventDefault();

		$.ajax({
			type: $(this).attr('method'),
			url: $(this).attr('action'),
			data: new FormData(this),
			contentType: false,
			cache: false,
			processData: false,

			success: function(result) {
				console.log(result);
				json = jQuery.parseJSON(result);
				
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
			},
			error: function() {
				console.log('js response error!');
			},
		});
	});
});