$(document).ready(function() {
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
                json = jQuery.parseJSON(result);
                // console.log(json);
                if(json.url) {
                    window.location.href = json.url;
                } else {
                    alert(json.status + ' - ' + json.message);
                }
            },
            error: function() {
                console.log('js response error!');
            },
        });
    });
});