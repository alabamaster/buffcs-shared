document.addEventListener('DOMContentLoaded', () => {
	const boxNick 			= document.querySelector('#boxNick');
	const boxSteam 			= document.querySelector('#boxSteam');
	const boxNickForSteam 	= document.querySelector('#boxNickForSteam');
	const typeAccess 		= document.querySelector('#type');
	let user 				= document.querySelector('#nickname');
	
	document.querySelector('#checkboxRules').required = true;

	const url = 'app/js_post/actionBuy.php';
	const server 	= document.querySelector('#server');
	const privilege = document.querySelector('#privilege');
	const days 		= document.querySelector('#days');
	const divMess 	= document.querySelector('#mess');
	const email 	= document.querySelector('#email');
	let fetchStatus = true;

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
		div.innerHTML = `<div class="mess mess-error animated flipInX">${title}</div>`;
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
		})
		.then( response => {
			strDivForResponse.innerHTML = response;
		})
	};

	server.addEventListener('change', (e) => {
		let server_id = e.target.value;
		customFetch(url, 'case=1&server_id=' + server_id, privilege);
		customFetch(url, 'case=3&server_id=' + server_id, document.querySelector('#server-mon'));
		days.innerHTML = '<option value="-1" selected="" disabled="">Выберите срок</option>';
	});

	privilege.addEventListener('change', (e) => {
		let privilege_id = e.target.value;
		customFetch(url, 'case=2&privilege_id=' + privilege_id, days);
		customFetch(url, 'case=4&privilege_id=' + privilege_id, document.querySelector('#about-privilege'));
		document.querySelector('#mess-info').innerHTML = '';
		document.querySelector('#mess-info').classList.remove('mb-2');
	});
});