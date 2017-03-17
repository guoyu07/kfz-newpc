(function (root, factory) {
    var id = 'nav/shopleftbar/nav';
    //项目所有自定义模块都放在widgets对象下
    root.widgets || (root.widgets = {});
    if (typeof define === 'function' && define.amd) {
        // AMD. 注册模块
        //如果不需要将此模块暴露在widgets下，可使用如下写法
        //define('widgets/' + id, ['libs/jQuery'], factory);
        //如果想要将此模块暴露在全局变量widgets下
        define('widgets/' + id, ['libs/jQuery'], function ($) {
            //如果想要将此模块暴露在全局变量widgets下
            return (root.widgets[id] = factory($));
        });
    } else {
        //如果不需要将此模块暴露在widgets下，可使用如下写法
        //factory(root.jQuery);
        //如果想要将此模块暴露在全局变量widgets下
        root.widgets[id] = factory(root.jQuery);
    }
}(this, function ($) {
    var shopleftbar={
        init:function(){
            $('body').on('mouseover','#changeTitle a',function(){
                $('#changeTitle a').removeClass('now');
                $(this).addClass('now')
                var aNum= $(this).index();
                $('.change_text ul').hide();
                $('#changeTitleTxt ul').eq(aNum).show();
            });
             $('.change_text a').attr('title',function(){
               return $(this).text();
           })
        }
    };
    shopleftbar.init();
    return shopleftbar;
}));