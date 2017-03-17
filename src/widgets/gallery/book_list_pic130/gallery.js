/*
 * @Author: zhaoxuan
 * @Date:   2016-12-15 09:51:54
 * @Last Modified by:   zhaoxuan
 * @Last Modified time: 2016-12-15 10:01:25
 */
(function (root, factory) {
    var id = 'gallery/book_list_pic108/gallery';
    //项目所有自定义模块都放在widgets对象下
    root.widgets || (root.widgets = {});
    if (typeof define === 'function' && define.amd) {
        // AMD. 注册匿名模块
        //如果不需要将此模块暴露在widgets下，可使用如下写法
        //define('widgets/' + id, ['libs/jQuery'],factory);
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
    var book_list_130={
        init:function(){           
             $('.book_list_130 .book_name a').attr('title',function(){
             return $(this).text();
           });           
            $('.book_list_130 .pic a').attr('title',function(){
             var nameText=$(this).parent().next('.book_name').text();
             return nameText; 
           });
           $('.book_list_130 .pic a img').attr('alt',function(){
             var nameText=$(this).parent().attr('title');
             return nameText; 
           });
        }
    };
    book_list_130.init();
    return book_list_130;
}));