(function (root, factory) {
    var id = 'floor/shop_lowprice/floor';
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
    //TODO 在此编写模块js代码, 注意, 不要忘了 return
    var shopChangetit={
        init:function(){
            $('body').on('mouseover','#moreListTit2 a',function(){
                $('#moreListTit2 a').removeClass('now');
                $(this).addClass('now');
                var aNum=$(this).attr('item');
                $('#bookList2 .booklist_box').hide();
                $('#bookList2').find('.booklist_item'+aNum).show();
            });
            $('#bookList2 .book_name a').attr('title',function(){
             return $(this).text();
           });
           $('#bookList2 .pic a').attr('title',function(){
             var nameText=$(this).parent().next('.book_name').text();
             return nameText; 
           });
           $('#bookList2 .pic a img').attr('alt',function(){
             var nameText=$(this).parent().attr('title');
             return nameText; 
           });
        }
    };
    shopChangetit.init();
    return shopChangetit;
}));
