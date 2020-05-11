$(document).ready(function()
{
	// account/login
	$('#auth').on('click', function(){
		var username = $('#username').val().trim();
		var password = $('#password').val();

		$.ajax({
			url: '../../../app/views/form/account_login.php',
			type: 'POST',
			cache: false,
			data: {
				'username':username, 'password':password, 
			},
			dataType: 'html',
			beforeSend: function() {
				$('#auth').prop('disabled', true);
			},
			success: function(data) {
				$('#auth').prop('disabled', false);
				$("#status").html(data);
			},
			error: function() {
				alert('ajax error: #auth');
			}
		});
	});
});