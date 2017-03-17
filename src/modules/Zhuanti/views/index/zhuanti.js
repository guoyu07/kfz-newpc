(function (root, factory) {
    root.widgets || (root.widgets = {});
    if (typeof define === 'function' && define.amd) {
        // AMD. 注册匿名模块
        define(['widgets/banner/full/banner'],['widgets/nav/shopleftbar/nav'], factory);
    } else {
        factory(root.widgets['banner/full/banner'],root.widgets['nav/shopleftbar/nav']);
    }
}(this, function (runImg) {
    
    // category.config({id: 'category',scroll: true}).run();
    runImg.run();
    var index_left={
        init:function(){
           $(window).scroll( function() {
                 var div_height = $('.shopLeftbar').height();
                 var change_fixed_height = $('.change_fixed')[0].offsetTop;
                 var scrollTop_height = $('body')[0].scrollTop;
                 var wind_height = $(window).height();
                 var normol_top = $('.content-left')[0].offsetTop;
                 var left_width = $('.content-left')[0].offsetLeft;
                                  
                 if(change_fixed_height > wind_height){
                     var change_height = change_fixed_height - scrollTop_height ;                
                        if(scrollTop_height > normol_top && change_height +205 <= wind_height){
                             $('.shopLeftbar').css({position:"fixed",top:"auto",left:left_width,bottom:"205px",height:div_height});    
                        }
                       else{
                            $('.shopLeftbar').css({position:"relative",top:"0px",left:"0px",height:div_height});
                       }                    
                 }
                 else{
                      var change_height = scrollTop_height + wind_height -change_fixed_height;
                      if(scrollTop_height > normol_top && change_height > 205){
                      $('.shopLeftbar').css({position:"fixed",top:"auto",left:left_width,bottom:"205px",height:div_height}); 
                     }
                     else{
                       $('.shopLeftbar').css({position:"relative",top:"0px",left:"0px",height:div_height});
                    }
                    
                 }                  
            } );
        }

    } ;
    index_left.init();
    return index_left;
  
    
}));