(function (root, factory) {
    var id = 'floor/shop_tesetuijian/floor';
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
            $('body').on('mouseover','#bookList2 .book_name a',function(){
                 var nameText=$(this).text();                
                 $(this).attr('title',nameText);
            });
            $('body').on('mouseover','#book_change_list  a.bt',function(){
                  $(this).siblings().removeClass('now');
                  $(this).addClass('now');
                   var aNum=$(this).index()+1;
                 $(this).parent().next('.text_box').find('.shop_book').hide();
                 $(this).parent().next('.text_box').find('.shop_book_item'+aNum).show(); 
            });
            //分页
            // var ulNumber = $('#book_change_list  .book_big_box ul').length;
            // $('.book_big_box ul').hide();
            // $('.book_big_box ul').eq(0).show();
           
        
    
        }
    };
    shopChangetit.init();
    return shopChangetit;
}));
