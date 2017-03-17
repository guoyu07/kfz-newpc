;(function(window){
    window.app = {
        hash: (!window.location.hash)?"0":window.location.hash.split('=')[1],
        moduleId: moduleId,
        initStatus: {
            moduleTitleSet: false                           //标题模块初始化状态
        },
        element: {
            navItem: $('#navPill-tab li'),
            tabItem: $('.tab-box li')
        },
        moduleTitleFn: {
            opt: {
                moduleId : moduleId,
                list : [],
                editItem : {
                    moduleId : ''
                }
            },
            element: {
                form: $('#form-title-module'),
                table: $('#table-module-title'),
                win: $('#win-title-module'),
                titleText: $('#titleText'),
                moreUrl: $('#moreUrl')
            },
            alertInformation:function(data){
                var html = '';
                window.alertWinTime = window.alertWinTime? window.alertWinTime:'';
                function domAppend(){
                    if(data.type){
                        html='<div class="alert alert-success alert-tishi" id="alert-tishi" role="alert" style="display:none;position: fixed;left: 50%;top:0px;width: 900px;margin-left: -450px;text-align: center;z-index: 10000000;"><strong class="fa fa-check-circle fa-fw"></strong>'+data.msg+'</div>';
                    }else {
                        html='<div class="alert alert-danger alert-tishi" id="alert-tishi" role="alert" style="display:none;position: fixed;left: 50%;top:0px;width: 900px;margin-left: -450px;text-align: center;z-index: 10000000;"><strong class="fa fa-times-circle fa-fw"></strong>'+data.msg+'</div>';
                    }
                    $('body').append(html);
                    $('#alert-tishi').slideDown();
                    var t=setTimeout(function(){
                        $('#alert-tishi').slideUp("normal",function(){
                            $('#alert-tishi').remove();
                        });
                    },2000);
                    window.alertWinTime=t;
                }
                if(window.alertWinTime){
                    console.log(window.alertWinTime);
                    clearTimeout(window.alertWinTime);
                    $('#alert-tishi').remove();
                    domAppend();
                }else {
                    domAppend();
                }
            },
            getData: function(){
                var that = this;
                that.element.table.datagrid('loading');
                $.ajax({
                    type: "post",
                    url: "/admin/module/getMainSubModuleInfo",
                    dataType: "json",
                    data:{
                        mainModuleId: that.opt.moduleId
                    },
                    success: function(res){
                        if(res.status == 1){
                            var mainModule = res.data;
                            res.data.submodule.push(mainModule);
                            that.opt.list = res.data.submodule;
                            that.element.table.datagrid('loadData',res.data.submodule);
                        }
                        that.element.table.datagrid('loaded');
                    },
                    error: function(){
                        that.element.table.datagrid('loaded');
                    }
                })
            },
            datagrid: function(){
                var that = this;
                that.element.table.datagrid({
                    columns: [[
                        {field: 'title', title: '标题', width: 200, align: 'center'},
                        {field: 'showMoreUrl', title: '更多url', width: 500, align: 'center',formatter:function(value,row,index){
                            return '<a href="' + value + '" target="_blank">' + value + '</a>'
                        }},
                        {field: 'btn', title: '操作', width: 100, align: 'center',formatter:function(value,row,index){
                            var btn = "<button class='btn btn-default btn-xs' onclick='app.moduleTitleFn.editItem(" + index + ")'>修改</button>";
                            return btn;
                        }}
                    ]],
                    onClickRow: function (rowIndex, rowData) {
                        that.element.table.datagrid('unselectRow', rowIndex);
                    }
                });
            },
            validate: function(){
                var that = this;
                that.element.form.validate({
                    rules: {
                        titleText: "required",
                        moreUrl: "url"
                    },
                    messages: {
                        titleText: "请输入标题",
                        moreUrl: "请输入正确的链接地址"
                    },
                    errorPlacement: function(error, element) {  
                        error.appendTo(element.parent());  
                    },
                    submitHandler: function(){   
                        $.ajax({
                            type: "post",
                            url: "/admin/module/setModule",
                            dataType: "json",
                            data:{
                                moduleId:that.editItem.moduleId,
                                title: that.element.titleText.val(),
                                url: that.element.moreUrl.val(),
                                subtitle: '' 
                            },
                            success:function(res){
                                if(res.status == 1){
                                    that.alertInformation({type:true,msg:'操作成功'});
                                    that.element.win.window('close');
                                    that.getData();
                                }else {
                                    that.alertInformation({type:false,msg:res.message});
                                }
                                return false;
                            },
                            error:function(res){
                                that.alertInformation({type:false,msg:res.message});
                                return false;
                            }
                        });
                        return false;
                    }  
                })
            },
            editItem: function(index){
                var that = this,
                data = that.opt.list[index];
                that.editItem.moduleId = data.moduleId;
                that.initFormData(data);
                that.element.win.window('open');
                that.validate();
            },
            initFormData: function(data){
                var that = this;
                that.element.titleText.val(data.title);
                that.element.moreUrl.val(data.showMoreUrl);
            },
            closeWin: function(){
                var that=this;
                that.element.win.window('close');
            },
            run: function(){
                var that = this;
                that.datagrid();
                that.getData();
            }
        },
        initNav: function(){
            var that = this;
            that.navChange(that.hash);
            that.element.navItem.on('click',function(){
                var index = $(this).index() + '';
                that.navChange(index);
            })
        },
        navChange: function(index){
            var that = this;
            that.element.navItem.removeClass('active');
            that.element.navItem.eq(index).addClass('active');
            that.element.tabItem.removeClass('show');
            that.element.tabItem.eq(index).addClass('show');
            switch (index) {
                case '0':
                    that.moduleTitle();
                    break;
                case '1':
                    
                    break;
                case '2':
                    
                    break;
                case '3':
                    
                    break;
                default:
                    break;
            } 
        },
        moduleTitle: function(){
            var that = this;
             if(that.initStatus.moduleTitleSet){
                that.moduleTitleFn.getData();
             }else{
                that.moduleTitleFn.run();
             }
        },
        run: function(){
            this.initNav();
        }
    };
    app.run();
})(window)