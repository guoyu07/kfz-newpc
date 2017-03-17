(function($) {
    $('#button').on('click', function() {
        var data = {};
        data.username = $("#username").val();
        data.password = $("#userpwd").val();

        console.log(data);

        $.ajax({
            url: '/admin/login/login',
            data: data,
            dataType: 'json',
            type: 'post',
            success: function(res) {
                if (res.status == 1) {
                    location.href = '/admin/index/index';
                } else {
                    alert(res.message || '错了！');
                }
            }
        });
    });
})(jQuery);