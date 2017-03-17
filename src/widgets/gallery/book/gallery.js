/*
 * @Author: zhaoxuan
 * @Date:   2016-12-15 09:51:54
 * @Last Modified by:   zhaoxuan
 * @Last Modified time: 2016-12-15 10:01:25
 */
(function (root, factory) {
    var id = 'gallery/book/gallery';
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
    var gallery = {
        element: {
            window: $(window),
            layerSlide: $('.layer-slide'),
        },
        run: function(){
            var that = this;
            layer.run();
            that.adapt();
        },
        adapt: function(){
            var that = this;
            var wW = that.element.window.width();
            function size(w){
                for(var i = 0; i <= that.element.layerSlide.length; i++){
                    that.element.layerSlide.eq(i).css({height:'236px',overflow:'visible'});
                    if(w >= 1200){
                        if(i>=12){
                            that.element.layerSlide.eq(i).css({height:0,overflow:'hidden'});
                        }
                    }else {
                        if(i>=10){
                            that.element.layerSlide.eq(i).css({height:0,overflow:'hidden'});
                        }
                    }
                };
            };
            size(wW);
            $(window).resize(function(){
                var wWs = that.element.window.width();
                size(wWs);
            })
        }
    };
    gallery.run();
}));