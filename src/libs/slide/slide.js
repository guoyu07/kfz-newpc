/*
 如何使用:
 #配置文件
 runImg.setting = {
 el:'#banner-box',
 imgBox: '.img-box',
 times: 5000,
 slidebtn:'#banner-right-textButton',
 active:'active'
 };
 #运行
 runImg.run();
 #布局格式
 <div id="banner-box">
 *轮播图使用ul和li标签
 <ul class="img-box">
 <li><img src="668125.png" alt="" /></li>
 <li><img src="668127.png" alt="" /></li>
 <li><img src="687986.jpg" alt="" /></li>
 <li><img src="725814.png" alt="" /></li>
 <li><img src="731737.jpg" alt="" /></li>
 <li><img src="731738.png" alt="" /></li>
 <li><img src="731740.png" alt="" /></li>
 <li><img src="731741.png" alt="" /></li>
 </ul>
 *文字按钮使用ul和li标签
 <ul id="banner-right-textButton">
 <li class="col-sm-8 col-xs-8">题目八个字显示出</li>
 <li class="col-sm-8 col-xs-8">题目八个字显示出</li>
 <li class="col-sm-8 col-xs-8">题目八个字显示出</li>
 <li class="col-sm-8 col-xs-8">题目八个字显示出</li>
 <li class="col-sm-8 col-xs-8">题目八个字显示出</li>
 <li class="col-sm-8 col-xs-8">题目八个字显示出</li>
 <li class="col-sm-8 col-xs-8">题目八个字显示出</li>
 <li class="col-sm-8 col-xs-8">题目八个字显示出</li>
 </ul>p
 </div>
 */
(function(window){
    window.runImg = {
        setting: {                  //配置项
            el: null,               //最外层容器
            imgBox: null,           //图片容器
            times: 2000,            //默认轮播时间
            slidebtn: null,         //图文点击按钮
            active: null,           //图文按钮选中状态
            prevBtn: null,          //上一张
            nextBtn: null,          //下一张
            play: null,             //轮播函数
            move: null,             //轮播方法
            index: null,            //当前图片序号
            backgroundColor: false, //是否显示背景色
            prev: null,             //上一张图片序号
            next: null              //下一张图片序号
        },
        config: function(params){
            var that=this;
            $.each(params,function(key,value){
                that.setting[key]=value;
            })
        },
        run: function(){
            var that=this;
            that.autoPlay();
            that.background(0);
            that.next(that.setting.nextBtn);
            that.prev(that.setting.prevBtn);
            that.slide(that.setting.slidebtn,that.setting.imgBox,that.setting.active);
            that.mouseoverout(that.setting.imgBox);
            $(that.setting.slidebtn).show();
            $(that.setting.imgBox).find('li').hide();
            $(that.setting.imgBox).find('li').eq(0).stop(true,false).show();
            $(that.setting.slidebtn).find('li').eq(0).addClass(that.setting.active);
        },
        autoPlay: function(){
            var that=this;
            that.setting.index = 0;
            that.setting.next = 0;
            that.setting.move = function(){
                that.setting.next++;
                if(that.setting.next == $(that.setting.imgBox).find('li').length) {
                    that.setting.next = 0;
                }
                $(that.setting.imgBox).find('li').eq(that.setting.index).stop(true,false).fadeOut();
                $(that.setting.imgBox).find('li').eq(that.setting.next).stop(true,false).fadeIn();
                $(that.setting.slidebtn).find('li').removeClass(that.setting.active);
                $(that.setting.slidebtn).find('li').eq(that.setting.next).addClass(that.setting.active);
                that.background(that.setting.next);
                that.setting.index=that.setting.next;
            };
            that.setting.play = setInterval(that.setting.move,that.setting.times);
        },
        next: function(btn){
            var that = this;
            $(btn).on('click',function(){
                that.setting.move();
            })
        },
        prev: function(btn){
            var that=this;
            $(btn).on('click',function(){
                that.setting.next--;
                if(that.setting.next < 0) {
                    that.setting.next = $(that.setting.imgBox).find('li').length-1;
                }
                $(that.setting.imgBox).find('li').eq(that.setting.next).stop(true,false).fadeIn(300);
                $(that.setting.imgBox).find('li').eq(that.setting.index).stop(true,false).fadeOut(300);
                $(that.setting.slidebtn).find('li').removeClass(that.setting.active);
                $(that.setting.slidebtn).find('li').eq(that.setting.next).addClass(that.setting.active);
                that.background(that.setting.next);
                that.setting.index=that.setting.next;
            });
        },
        slide: function(btn,imgBox,active){
            var that = this;
            $(btn).find('li').hover(function(){
                var index = $(this).index();
                clearInterval(that.setting.play);
                if(index == that.setting.index){
                    return;
                }
                $(imgBox).find('li').eq(that.setting.index).stop(true,false).fadeOut();
                $(imgBox).find('li').eq(index).stop(true,false).fadeIn();
                $(btn).find('li').removeClass(active);
                $(btn).find('li').eq(index).addClass(active);
                that.background(index);
                that.setting.index=that.setting.next=index;
            },function(){
                that.setting.play = setInterval(that.setting.move,that.setting.times);
            })
        },
        mouseoverout: function(obj){
            var that = this;
            $(obj).hover(function(){
                clearInterval(that.setting.play);
            },function(){
                that.setting.play = setInterval(that.setting.move,that.setting.times);
            })
        },
        background: function(index){ //背景颜色
            var that=this;
            var bg=$(that.setting.imgBox).find('li').eq(index).attr('data-color');
            $(that.setting.imgBox).find('li').eq(index).css({background:bg});
        }
    };
    return window.runImg;
})(window);