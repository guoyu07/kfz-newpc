;(function(window){
    window.app = {
        hash: (!window.location.hash)?"0":window.location.hash.split('=')[1],
        moduleId: moduleId,
        initStatus: {
            dataSourceSet: false,                           //数据源模块初始化状态
            moduleTitleSet: false                           //标题模块初始化状态
        },
        element: {
            navItem: $('#navPill-tab li'),
            tabItem: $('.tab-box li')
        },
        dataSourceFn: {
            opt: {
                moduleId: moduleId,  
                formType: '',                    
                add: {
                    shopId: '',
                    shopName: ''
                },
                edit: {
                    confId: '',
                    shopId: '',
                    shopName: ''
                },
                params: {
                    isDefault : 0,
                    status : ['draft','published','ended'],
                    regularlyStart : '',
                    regularlyEnd : '',
                    keyWords: {
                        shopName: ''
                    },
                    page: {
                        maxRowPerPage: '',                  //当前页显示数据条数
                        requirePage: '',                    //当前页
                    },
                    order: {
                        order: 'ASC',
                        updateTime: 'DESC'
                    }
                }
            },
            element: {
                table: $('#table'),
                dataSourceName: $('#dataSourceName'),
                addDataSource: $('#addDataSource'),
                editDataSource: $('#editDataSource'),
                addWin: $('#win-add'),
                addForm: $("#form-add"),
                shopInfo: $('#table-shop-info'),
                searchValue: $('#searchValue')
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
                    url: "/admin/module/getData",
                    dataType: "json",
                    data:{
                        moduleId:that.opt.moduleId,
                        params: that.opt.params
                    },
                    success: function(res){
                        if(res.status == 1){
                            if(res.data.length){
                                var info = res.data[0];
                                that.element.dataSourceName.text(info.shopName);
                                that.element.addDataSource.hide();
                                that.element.editDataSource.show();
                                that.opt.edit.confId = info.confId;
                                that.opt.edit.shopId = info.shopId;
                                that.opt.edit.shopName = info.shopName;
                                that.getShopInfo(info.shopName,function(shopInfo,data){
                                    that.opt.add.shopId = shopInfo.shopId;
                                    that.opt.add.shopName = shopInfo.shopName;
                                    that.element.table.datagrid('loadData',data);
                                });
                            }else{
                                that.element.dataSourceName.text('暂未添加');
                                that.element.addDataSource.show();
                                that.element.editDataSource.hide();
                            }
                            that.element.table.datagrid('loaded');
                        }
                    },
                    error: function(){
                        that.element.table.datagrid('loaded');
                    }
                })
            },
            // 模块数据网格
            datagrid: function(){
                var that = this;
                that.element.table.datagrid({
                    toolbar: "#toolbar",
                    columns:[[
                        {field: 'key', title: '书店信息', width: 148, align: 'center'},
                        {field: 'value', title: '数据', width: 148, align: 'center',formatter:function(value,row,index){
                            if(row.key == '书店名称' ){
                                return '<a href="' + site.shop + row.shopId + '" target="_blank">' + value + '</a>';
                            }
                            if(row.key == '书店ID' ){
                                return '<a href="' + site.shop + value + '" target="_blank">' + value + '</a>';
                            }
                            return value;
                        }}
                    ]],
                    onClickRow: function (rowIndex, rowData) {
                        that.element.table.datagrid('unselectRow', rowIndex);
                    }
                });
            },
            query: function(){
                var that = this,
                    searchValue = that.element.searchValue.val();
                if(searchValue == ''){
                    that.alertInformation({type:false,msg:'请输入要查询的书店ID或书店名称'});
                    return;
                }
                that.getShopInfo(searchValue,function(shopInfo,data){
                    that.opt.edit.shopId = shopInfo.shopId;
                    that.opt.edit.shopName = shopInfo.shopName;
                    that.element.shopInfo.datagrid('loadData',data);
                });

            },
            // 获取书店信息
            getShopInfo: function(searchValue,callback){
                var that = this;
                $.ajax({
                    type: "post",
                    url: "/admin/shop/getShopInfo",
                    dataType: "json",
                    data: {
                        param: searchValue
                    },
                    success: function(res){
                        if(res.status == 1){
                            var shopInfo = res.data,
                            data = [
                                {key : '书店名称',value : res.data.shopName,shopId : res.data.shopId},
                                {key : '书店ID',value : res.data.shopId},
                                {key : '拍卖会员等级',value : res.data.auctionLevel},
                                {key : '卖家信用',value : res.data.credit},
                                {key : '卖家好评率',value : res.data.rate}
                            ];
                            callback(shopInfo,data);
                        }else{
                            that.alertInformation({type:false,msg:res.message});
                        }
                    }
                })
            },
            // 书店信息表格
            shopDataGrid: function(){
                var that = this;
                that.element.shopInfo.datagrid({
                    columns: [[
                        {field: 'key', title: '书店信息', width: 148, align: 'center'},
                        {field: 'value', title: '数据', width: 148, align: 'center',formatter:function(value,row,index){
                            if(row.key == '书店名称' ){
                                return '<a href="' + site.shop + row.shopId + '" target="_blank">' + value + '</a>';
                            }
                            if(row.key == '书店ID' ){
                                return '<a href="' + site.shop + value + '" target="_blank">' + value + '</a>';
                            }
                            return value;
                        }}
                    ]],
                    onClickRow: function (rowIndex, rowData) {
                        that.element.shopInfo.datagrid('unselectRow', rowIndex);
                    }
                });
            },
            // 表格验证
            validate: function(){
                var that = this;
                that.element.addForm.validate({
                    rules: {searchValue: "required"},
                    messages: {searchValue: "请输入TAB名称"},
                    errorPlacement: function(error, element) {  
                        error.appendTo(element.parent());  
                    },
                    submitHandler: function(){   
                        if(that.opt.formType == 'add'){
                            that.addSubmit();
                        }
                        if(that.opt.formType == 'edit'){
                            that.editSubmit();
                        }
                        return false;
                    }  
                })
            },
            // 新增提交
            addSubmit: function(){
                var that = this;
                if(that.opt.add.shopId === '' || that.opt.add.shopName === ''){
                    that.alertInformation({type:false,msg:'请先点击查询按钮并检测店铺信息'});
                    return;
                }
                var result = {
                    isDefault : 0,
                    dataId : that.opt.add.shopId,
                    shopName : that.opt.add.shopName
                };
                $.ajax({
                    type: "post",
                    url: "/admin/module/addModuleData",
                    dataType: "json",
                    data:{
                        moduleId:that.opt.moduleId,
                        data: result
                    },
                    success:function(res){
                        if(res.status == 1){
                            that.alertInformation({type:true,msg:'操作成功'});
                            that.element.addWin.window('close');
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
            },
            // 编辑提交
            editSubmit: function(){
                var that = this;
                if(that.opt.edit.shopId === '' || that.opt.edit.shopName === ''){
                    that.alertInformation({type:false,msg:'请先点击查询按钮并检测店铺信息'});
                    return;
                }
                var result = {
                    confId : that.opt.edit.confId,
                    dataId : that.opt.edit.shopId,
                    shopName : that.opt.edit.shopName
                };
                $.ajax({
                    type: "post",
                    url: "/admin/module/editModuleData",
                    dataType: "json",
                    data:{
                        moduleId:that.opt.moduleId,
                        data: result
                    },
                    success:function(res){
                        if(res.status == 1){
                            that.alertInformation({type:true,msg:'操作成功'});
                            that.element.addWin.window('close');
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
            },
            // 关闭新增窗口
            closeAddWin: function(){
                var that=this;
                that.element.addWin.window('close');
            },
            // 渲染表单
            initFormData: function(){
                var that = this;
                var shopInfo = [
                    {key : '书店名称',value : ''},
                    {key : '书店ID',value : ''},
                    {key : '拍卖会员等级',value : ''},
                    {key : '卖家信用',value : ''},
                    {key : '卖家好评率',value : ''}
                ];
                that.element.shopInfo.datagrid('loadData',shopInfo);
                that.element.searchValue.val('');
            },
            // 新增
            addDataSource: function(){
                var that = this;
                that.opt.formType = 'add';            
                that.initFormData();
                that.element.addWin.window('open');
                that.validate();
            },
            // 编辑
            editDataSource: function(){
                var that = this;
                that.opt.formType = 'edit';
                that.initFormData();
                that.element.addWin.window('open');
                that.validate();
            },
            run: function(){
                var that = this;
                that.datagrid();
                that.shopDataGrid();
                that.getData();
            }
        },
        moduleTitleFn: {
            opt: {
                moduleId : moduleId,
                title : ''
            },
            element: {
                form: $('#form-title-module'),
                titleText: $('#titleText')
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
                $.ajax({
                    type: "post",
                    url: "/admin/module/getMainSubModuleInfo",
                    dataType: "json",
                    data:{
                        mainModuleId: that.opt.moduleId
                    },
                    success: function(res){
                        if(res.status == 1){
                            that.opt.title = res.data.title;
                            that.element.titleText.val(res.data.title);
                        }
                    }
                })
            },
            validate: function(){
                var that = this;
                that.element.form.validate({
                    rules: {
                        titleText: "required"
                    },
                    messages: {
                        titleText: "请输入标题"
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
                                moduleId:that.opt.moduleId,
                                title: that.element.titleText.val()
                            },
                            success:function(res){
                                if(res.status == 1){
                                    that.alertInformation({type:true,msg:'标题修改成功'});
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
            cancel: function(){
                var that = this;
                that.element.titleText.val(that.opt.title);
            },
            run: function(){
                var that = this;
                that.validate();
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
                    that.dataSource();
                    break;
                case '1':
                    that.moduleTitle();
                    break;
                case '2':
                    
                    break;
                case '3':
                    
                    break;
                case '4':
                    
                    break;
                default:
                    break;
            } 
        },
        dataSource: function(){
             var that = this;
             if(that.initStatus.dataSourceSet){
                that.dataSourceFn.getData();
             }else{
                that.dataSourceFn.run();
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