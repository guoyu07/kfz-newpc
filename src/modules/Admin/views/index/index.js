;(function($) {
    $('#dd').datebox({
        required: true,
        formatter: function(date) {
            var y = date.getFullYear();
            var m = date.getMonth() + 1;
            var d = date.getDate();
            return y+'-'+m+'-'+d;
        }
    });
    $('#side-menu').metisMenu();
})(jQuery);
