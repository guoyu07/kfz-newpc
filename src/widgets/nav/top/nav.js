(function (root, factory) {
    var id = 'nav/top/nav';
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
   var nav={
        init: function () {
            this.topBar();
            this.loginCheck();
        },
       //购物车列表加载
       showCart:function(){
           var that = this;
           if(this.hasUpdatedCart){
               return;
           }
           if(this.isGetingCartlist){
               return;
           }


           // 调用jsonp的接口获取购物车数量
           var timestamp = new Date().getTime();
           var url = "http://neibushop.kongfz.com/book/shopcart/listCart?_=" + timestamp + "&callback=JSON_CALLBACK";
           this.isGetingCartlist = true;
           $.ajax({
               dataType:"JSONP",
               jsonp:"callback",//请求自动带上callback参数，callback值为jsonpCallback的值
               type:"GET",
               url:url,//接口服务器地址
               data:{},//请求数据
               success:function(res){
                   //成功执行
                   console.log(res);
                   if(res.status == true) {
                	 //购物车里有商品
                       if(res.data.length > 0){
                           that.renderCart(res);
                           that.hasUpdatedCart = true;
                       }
                        else{
                           $('#topbar_cart .tan_box').addClass("full_cart");
                           $('#topbar_cart #login_empty_cart').show();
                       }
                   }
               },
               error:function(e){
                   //失败执行
                     
               },
               complete:function(){
                   that.isGetingCartlist = false;
               }
           });
           
//           $.get("/data/widgets/nav/cart.json",function(res){
//               //购物车里有商品
//              if(res.data.length > 0){
//                  that.renderCart(res);
//                  that.isGetingCartlist = false;
//                  that.hasUpdatedCart = true;
//              }
//               else{
//                  $('#topbar_cart .tan_box').addClass("full_cart");
//                  $('#topbar_cart #login_empty_cart').show();
//              }
//           });
       },
       //渲染购物车列表
       renderCart:function(res){
//           var cartNum=res.number;
    	   var cartNum = res.data.length;
           if(cartNum=="0"){
               $('#topbar_cart .tan_box').addClass("full_cart");
               $('#topbar_cart #login_empty_cart').show();
           }
           else if(cartNum>0){
               $('#topbar_cart .tan_box').addClass("full_cart");
               $('#topbar_cart #login_full_cart').show();
               $('#login_full_cart .product_list').empty();
               $.each(res.data,function(i,json){
                   if(i>=5) {
                       $('#leave_number').show();
                       $('#leave_number .num').text(cartNum-5);
                       return false;
                   }
                   var str= "<div class='pos' cartid='"+json.id+"' shopid='"+json.shopId+"' itemid='"+json.itemId+"'><a href='"+json.url+"' class='clearfix product'>"
                   str +="<div class='f_left pic m_r5'><img src='"+json.imgUrl+"'/></div>"
                   str +="<div class='f_left name'>"+json.itemName+"</div>"
                   str +="<div class='text_box'>"
                   str +="<div class='orange'>"+json.price+"</div>"
                   str +="</div>"
                   str +="</a>"
                   str +="<span class='del'>删除</span></div>"
                   $('#login_full_cart .product_list').append(str);
               })
           }
           $('#topbar_cart .cart_number').text(cartNum);
       },
       
       getUnreadMessageNum:function() {
    	   // 调用jsonp的接口获取未读消息数量
    	   var timestamp = new Date().getTime();
    	   var url = "http://neibumessage.kongfz.com//Interface/User/initNotice?_=" + timestamp + "&appName=WEB_KFZ_WEB_NOTICE&callback=JSON_CALLBACK";
    	   $.ajax({
               dataType:"JSONP",
               jsonp:"callback",//请求自动带上callback参数，callback值为jsonpCallback的值
               type:"GET",
               url:url,//接口服务器地址
               data:{},//请求数据
               success:function(res){
                   //成功执行
                   console.log(res);
                   if(res.status==true && res.result.unreadNum > 0){
                       $('.top_bar .message_no').hide();
                       $('.top_bar .message_has').show();
                       var xiaoxiNum=res.result.unreadNum;
                       $('.top_bar .xiaoxi_number').text(xiaoxiNum)
                   }
               },
               error:function(e){
                   //失败执行
                     
               }
           });
       },
       
       addItem:function(shopId, itemId) {
    	   var that = this;
    	   //加入购物车
    	   var timestamp = new Date().getTime();
    	   var url = "http://neibubook.kongfz.com/book/shopcart/addJsonp?_=" + timestamp + "&shopId=" + shopId + "&itemId=" + itemId + "&num=1&callback=JSON_CALLBACK";
    	   $.ajax({
               dataType:"JSONP",
               jsonp:"callback",//请求自动带上callback参数，callback值为jsonpCallback的值
               type:"GET",
               url:url,//接口服务器地址
               data:{},//请求数据
               success:function(res){
                   //成功执行
                   console.log(res);
                   if(res.status){
                	   $('#add_cart').hide();
                       that.hasUpdatedCart = false;
                       that.showCart();
                   } else {
                	   //添加失败
                	   
                   }
               },
               error:function(e){
                   //失败执行
                     
               }
           });
       },

       loginCheck:function(){
           var that = this;
           $.get("/data/widgets/nav/loginCheck.json",function(res){
            //已登录状态
            if(res.status && res.isLogin == 1){
                //用户名
                var topbarUsername=res.nickname;
                $('.top_bar li.has_login').show();
                $('.top_bar li.has_login a.name').text(topbarUsername);

                //我的消息
                that.getUnreadMessageNum();
//                $.get("/data/widgets/nav/xiaoxi.json",function(res){
//
//                    if(res.status=="true" && res.Number > 0){
//                        $('.top_bar .message_no').hide();
//                        $('.top_bar .message_has').show();
//                        var xiaoxiNum=res.Number;
//                        $('.top_bar .xiaoxi_number').text(xiaoxiNum)
//                    }
//                });

                //购物车

                $('.top_bar li#topbar_cart').mouseenter(function(){
                    that.showCart();
                    //点删除键
                    $('body').on('click','#login_full_cart .del',function(){
                        var carIdName=$(this).parent().attr('cartid');    //购物车中商品的唯一编号
                        var itemId = $(this).parent().attr('itemid');
                        var shopId = $(this).parent().attr('shopid');
                        //删除购物车商品
                        that.delCartItem(carIdName);
//                        $.post('carIdName','',function(res){
//                            if(res.status){
//                                that.hasUpdatedCart = false;
//                                that.showCart();
//                            }
//                        });
                        $('#add_cart').html("<div class=\"more_box m_t8\"><a href=\"javascript:;\" id=\"cart_back_del\" >误删了？点击这里找回商品</a></div>");
                        $('#add_cart').show();
                        //购物车撤销，向服务器提交添加商品
                        $('#cart_back_del').click(function(){
                        	//加入购物车
                        	that.addItem(shopId, itemId);
//                            $.post('carIdName','',function(res){
//                              if(res.status){
//                                  that.hasUpdatedCart = false;
//                                  that.showCart();
//                              }
//                            });
                        });
                    });
                }).mouseleave(function() {
                    $('#topbar_cart .tan_box').hide();
                    $(this).removeClass('now');
                    $('#add_cart').empty();
                })
                //卖家中心
                $('.top_bar li#topbar_seller').mouseenter(function(){
                    var topbarUserType =res.userType;
                    if(topbarUserType.indexOf('shopkeeper')>-1){
                        $('.top_bar .hasOpenShop').show();
                    }
                    else{
                        $('.top_bar .noOpenShop').show();
                        $(this).find('.tan_box').addClass('w_89')
                    }
                    if(topbarUserType.indexOf('auctioneer')>-1){
                        $('.top_bar .hasOpenPm').show();
                    }
                    else{
                        $('.top_bar .noOpenPm').show();
                        $(this).find('.tan_box').addClass('w_89')
                    }
                });

            }   //未登录状态
               else{
                //用户名
                $('.top_bar li.no_login').show();
                //购物车
                $('#topbar_cart .tan_box').addClass('cart');
                $('#topbar_cart #no_login_cart').show();
                //卖家中心
                $('.top_bar #topbar_seller .noOpenShop').show();
                $('.top_bar #topbar_seller .noOpenPm').show();
                $('.top_bar #topbar_seller').find('.tan_box').addClass('w_89')
            }
           });
       },
       
       delCartItem:function(itemId) {
    	   var that = this;
    	   // 调用jsonp的接口删除购物车商品
    	   var timestamp = new Date().getTime();
    	   var url = "http://neibushop.kongfz.com/book/shopcart/delCartItemAjax?_=" + timestamp + "&cartIds=" + itemId;
    	   $.ajax({
               dataType:"JSONP",
               jsonp:"callback",//请求自动带上callback参数，callback值为jsonpCallback的值
               type:"GET",
               url:url,//接口服务器地址
               data:{},//请求数据
               success:function(res){
                   //成功执行
                   console.log(res);
                   if(res.status == true) {
                	   that.hasUpdatedCart = false;
                       that.showCart();
                   }
               },
               error:function(e){
                   //失败执行
                     
               }
           }); 
       },

       topBar:function(){
           $('.top_bar').delegate( 'li', 'mouseenter',function () {
               $(this).addClass('now');
               $(this).find('.tan_box').show();
           }).delegate( 'li','mouseleave', function () {
               $(this).removeClass('now');
               $(this).find('.tan_box').hide();
           });
       }
       }
   nav.init();
   return nav;
}));