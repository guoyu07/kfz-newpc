(function (root, factory) {
    var id = 'header/main/header';
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
    var header= {
        headerSearch:function(){

            var that=this;
            $('body').on('click','#headerSearchtit a',function(){
                $('#headerSearchtit a').removeClass('now');
                $('#headerSearchtit a span').removeClass('jiantou_up');
                $(this).addClass('now');
                $(this).find('span').addClass('jiantou_up');
            });

            //绑定搜索
            $('body').on('click','#headerSearch_btn',function(){
                that.searchBtnEvent();
            });

            //输入框事件
            $('#headerSearch_input').on({
                focus:function(){ that.showSearchHistory()},
                keyup:function(e){
                    var search_input_txt= $('#headerSearch_input').val().trim();
                    if(e.keyCode == 13){
                        that.searchBtnEvent();
                        $('.search_iput_box .assBox').hide();
                    }else{
                        if(search_input_txt == ''){
                            that.showSearchHistory();
                        }else{
                            //联想
                            that.sugWordslist();

                        }
                    }
                }

            })



            //点删除历史记录
            $('body').on('click','.search_del',function(){
                var delSearchname = $(this).next('.search_key_name').text();
                console.log(delSearchname);
                that.delKeywords(delSearchname);
                return false;
            });

            //输入框失去焦点
            $(document).on('click',function(e){
                var flag=$(e.target).closest('.search_iput_box');
                if(flag.html() == null){
                    $('.search_iput_box .assBox').hide();
                }
            });
            //点联想下拉单的a标签
            $('body').on('click','#search_key_list a',function(){               
                var keyWordNow = $(this).find('.search_key_name').text().trim();             
                $('#headerSearch_input').val(keyWordNow);
                that.searchBtnEvent();
            })
        },
    //输入联想事件
        sugWordslist:function(){
            var that=this;
            $('.search_iput_box .assBox').show();
            $('.search_iput_box .assBox .asstit').hide();
            var search_input_txt= $('#headerSearch_input').val().trim();
            var url="http://search.kongfz.com/sug/suggest_server.jsp?query="+search_input_txt+"&_="+new Date().getTime();
            if(this.gettingList){
                return;
            }
            this.gettingList = true;
            $.ajax({
                dataType:"jsonp",
                //jsonp:"callback",//请求自动带上callback参数，callback值为jsonpCallback的值
                type:"GET",
                url:url,//接口服务器地址
                //data:{},//请求数据
                success:function(res){
                    var tpl="";
                    $.each(res,function(i,val){
                        tpl+="<a href='javascript:;'><span class='search_key_name'>"+val+"</span></a>"                       
                    });
                   $('#search_key_list').html(tpl);
                },
                error:function(){
                    //失败执行
                },
                complete:function(){
                    that.gettingList = false;
                }

            })

    },
     //搜索事件
        searchBtnEvent:function(){
            var that=this;
            var search_input_txt= $('#headerSearch_input').val().trim();
            var search_input_bt=$('#headerSearchtit a.now').attr('id');

            if(search_input_txt!=""){
                //存历史记录
                that.saveKeywords();

                if(search_input_bt == "headerSearchshop"){
                    window.location.href="#";
                }
                else{
                    window.location.href="#";
                }
            }

        },
        //加载历史记录list
        showSearchHistory:function(){
            //$('#search_key_list').empty();
            var tpl='';
            var search_history_name= store.get('searchKeyUsers');
            console.log(search_history_name);
            if(search_history_name){
                $('.asstit').show();
                search_history_name.reverse();
                $.each(search_history_name,function(i,val){
                    tpl+="<a href='javascript:;'><span class='search_del'>删除</span><span class='search_key_name'>"+val+"</span></a>"
                    //$('#search_key_list').append(str);
                });
                $('#search_key_list').html(tpl);
                $('.search_iput_box .assBox').show();

            }
        },
    //存历史记录
      saveKeywords:function(){
          var search_input_txt= $('#headerSearch_input').val().trim();
          var search_history_name = store.get('searchKeyUsers');
        //存在历史记录
          if($.isArray(search_history_name)){
              var search_history_len=search_history_name.length;
              if(search_history_name.indexOf(search_input_txt) == -1){
                  if(search_history_len<10 ){

                      search_history_name.push(search_input_txt);
                      store.set('searchKeyUsers',search_history_name);
                      console.log(search_history_name)

                  }
                  else{
                      search_history_name.reverse();
                      search_history_name.pop();
                      search_history_name.reverse();
                      search_history_name.push(search_input_txt);

                      store.set('searchKeyUsers',search_history_name);
                      console.log(search_history_name)
                  }
              }

          }
          //不存在历史记录
          else{
              search_history_name = [];
              search_history_name.push(search_input_txt);
              store.set('searchKeyUsers',search_history_name);
              console.log(search_history_name)
          }

        },
    //删除历史记录
        delKeywords:function(i){
            var that=this;
            var search_history_name = store.get('searchKeyUsers');
            var keyNamenum = search_history_name.indexOf(i);
            search_history_name.splice(keyNamenum,1);
            store.set('searchKeyUsers',search_history_name);

            //if(search_history_name.length>1){
                //$('.search_iput_box .assBox').show();
                that.showSearchHistory();
            //}

        },
        // 字符转unicode
        charToUnicode:function(str) {
            if (!str) return '';
            var unicode = '', i = 0, len = (str = '' + str).length;
            for (; i < len; i ++) {
                unicode += 'k' + str.charCodeAt(i).toString(16).toLowerCase();
            }
            return unicode;
        }
    };
    header.headerSearch();
    return header;

}));