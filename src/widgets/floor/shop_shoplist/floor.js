(function (root, factory) {
    var id = 'floor/shop_shoplist/floor';
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
            $('body').on('mouseover','#moreListTit3 a',function(){
                $('#moreListTit3 a').removeClass('now');
                $(this).addClass('now');
                var aNum=$(this).attr('item');
                $('#shop_shoplist .shoplist_box').hide();
                $('#shop_shoplist').find('.shoplist_item'+aNum).show();
            });
            $('body').on('mouseover','#shop_shoplist .book_name a',function(){
                 var nameText=$(this).text();                
                 $(this).attr('title',nameText);
            });
            $('body').on('mouseover','#shop_shoplist .shop_name_list a',function(){
                $(this).parent().find('a').removeClass('now');
                $(this).addClass('now');
                
                var aNum=$(this).index()+1;
                $(this).parent().next('.text_box').find('.shop_book').hide();
                $(this).parent().next('.text_box').find('.shop_book_item'+aNum).show();
            });
            $('#shop_shoplist  .shoplist_box').find('ul:odd').addClass('bg-gray');
        }
    };
    shopChangetit.init();
    return shopChangetit;
}));
