$(document).ready(function() {

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    var code = urlParams.get('code');
    var shop_id = urlParams.get('shop_id');
    if (code !== null && shop_id !== null) {
        $('#connect').css('display', 'none');
        $('.disConnect').css('display', 'block');
        //Get code shop_id
        $('#code').val(code);
        $('#shop_id').val(shop_id);
    } else {
        $('#connect').css('display', 'block');
        $('.disConnect').css('display', 'none');
    }
});

function getToken() {
    var data = {
        code: $('#code').val(),
        shop_id: $('#shop_id').val(),
        partner_id: $('#partner_id').val(),
        partner_key: $('#partner_key').val(),
    }
    $.ajax({
        url: `/token`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
        data: data,
        success: function(res) {
            if (res.code === true) {
                $('#access_token').val(res.data.accessToken)
                $('#refresh_token').val(res.data.newRefreshToken)
                $('#expire_in').val(res.data.expired_time)
                $('#shop_name').val(res.data.shop_name)
                $.notify(res.message, "success");
            } else {
                $.notify(res.message, "error");
            }
        },
        error: function(err) {
            $.notify(err.message, "success");
        }
    });
}