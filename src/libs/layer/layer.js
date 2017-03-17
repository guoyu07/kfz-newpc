(function(window){
    //提示框插件
    window.layer = {
        element: {
            box: $('.layer-content'),       //最外层的容器
            slide: $('.layer-slide'),       //单个容器
            region: $('.layer-region'),     //鼠标移动的区域
            inner: $('.layer-inner')
        },
        run: function(){
            var that=this;
            that.mouseover();
            that.mousemove();
            that.mouseout();
        },
        mouseover: function(){
            var that=this;
            that.element.box.find('.layer-slide').bind('mouseover',function(e){
                that.index=$(this).index();
            })
        },
        mousemove: function(){
            var that=this;
            var dx,dy;
            that.element.box.find('.layer-region').bind('mousemove',function(e){
                e=e||window.event;
                var _this=this;
                dx=that.offset(e).offsetX;
                dy=that.offset(e).offsetY;
                that.t=setTimeout(function(){
                    var cx=that.offset(e).offsetX;
                    var cy=that.offset(e).offsetY;
                    if(cx==dx && cy==dy){
                        var rightL = (that.windowWidth()-that.element.box.find('.layer-slide').eq(that.index).offset().left)-that.element.box.find('.layer-region').width();
                        $(_this).find('.layer-inner').show();
                        if(e.clientX+that.element.inner.width()+50>=that.windowWidth()){
                            $(_this).find('.layer-inner').css({right:-rightL+20,top:cy+20,left:'initial'});
                        }else {
                            $(_this).find('.layer-inner').css({left:cx+10,top:cy+20,right:'initial'});
                        }
                    }
                },500);
            });
        },
        mouseout: function(){
            var that=this;
            that.element.box.find('.layer-region').bind('mouseout',function(e){
                clearTimeout(that.t);
                $(this).find('.layer-inner').hide();
            })
        },
        offset: function(e){
            return {
                offsetX: e.offsetX || e.layerX,
                offsetY: e.offsetY || e.layerY
            }
        },
        windowWidth: function(){
            if (window.innerWidth){
                return window.innerWidth;
            }else if ((document.body) && (document.body.clientWidth)){
                return document.body.clientWidth;
            }
        }
    };
    return window.layer;
})(window)