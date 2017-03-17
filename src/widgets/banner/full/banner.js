(function (root, factory) {
    var id = 'banner/full/banner';
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
    //轮播图
    var banner = {
        element: {
            prevBtn: $('#prev-btn'),
            nextBtn: $('#next-btn')
        },
        run: function(){
            var that = this;
            that.runImg();
            that.showAction(); //当轮播图只有一张的时候只所有按钮都不显示
            that.showBtn();
        },
        showAction: function(){  
            var count=$('#count').val();
            if(count<=1){
                $('.banner-slider').css({display:'none'});
            }
        },
        showBtn:function(){
            $('#banner-box').hover(function(){
                banner.element.prevBtn.stop(true,false).fadeIn('fast');
                banner.element.nextBtn.stop(true,false).fadeIn('fast');
            },function(){
                banner.element.prevBtn.stop(true,false).fadeOut('fast');
                banner.element.nextBtn.stop(true,false).fadeOut('fast');
            })
        },
        runImg: function(){
            var value=$('#showType').val() == 0 ? '#banner-right-textButton' : '#banner-right-button';
            runImg.config({
                el: '#banner-box',
                imgBox: '.img-box',
                times: 5000,
                slidebtn: value,
                active: 'active',
                prevBtn: '#prev-btn',
                nextBtn: '#next-btn'
            });
            runImg.run();
        }
    };
    return banner;
}));