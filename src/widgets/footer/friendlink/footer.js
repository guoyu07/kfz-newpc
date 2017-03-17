(function (root, factory) {
    var id = 'footer/friendlink/footer';
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
   var friends={
        friendsLink:function(){
            $('body').on('mouseover','.friend_link_tit a',function(){
               $('.friend_link_tit a').removeClass('now');
               $(this).addClass('now');
               var friendlink_num=$(this).index();
               $('.friend_link_list .f_box').hide();
               $('.friend_link_list .f_box').eq(friendlink_num).show();


            });
        }
       }
    friends.friendsLink();
    return friends;

}));
