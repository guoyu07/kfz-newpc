;(function(window){
    window.app = {
        hash: (!window.location.hash)?"0":window.location.hash.split('=')[1],
        moduleId: moduleId,
        initStatus: {
            defaultDataSet: false,                          //默认数据模块初始化状态
            moduleTitleSet: false                           //标题模块初始化状态
        },
        element: {
            navItem: $('#navPill-tab li'),
            tabItem: $('.tab-box li')
        },
        defaultDataFn: {
            opt: {
                moduleId: moduleId1,                      
                formType: '',
                confId: '',
                list: [],
                addItem: {
                    shopId: '',
                    shopName: ''
                },
                editItem: {
                    moduleId: '',
                    confId: ''
                },
                params: {
                    isDefault: 1,
                    status : ['draft','published','ended'],
                    keyWords: {
                        shopName: '',
                        recommend: ''
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
                addWin: $('#win-add'),
                orderWin: $('#win-order'),
                shopInfo: $('#table-shop-info'),
                searchValue: $('#searchValue'),
                recommendInfo: $('#recommendInfo'),
                order: $('#order'),
                addForm: $("#form-add")
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
                            that.opt.list = res.data;
                            that.element.table.datagrid('loadData',res.data);
                            that.pagination(res);
                        }
                        that.element.table.datagrid('loaded');
                    },
                    error: function(){
                        that.element.table.datagrid('loaded');
                    }
                })
            },
            // 分页
            pagination: function(data){
                var that = this;
                that.element.table.datagrid('getPager').pagination({
                    total: parseInt(data.other.page.total),
                    pageSize: parseInt(data.other.page.pageSize),
                    pageNumber: parseInt(data.other.page.pageNumber),
                    layout: ['list','sep','first','prev','links','next','last','sep','refresh'],
                    pageList: [5,10,20,30,40,50],
                    onSelectPage: function(pageNumber, pageSize){
                        that.opt.params.page.requirePage = parseInt(pageNumber);
                        that.opt.params.page.maxRowPerPage = parseInt(pageSize);
                        that.getData();
                    }
                });
            },
            // 模块数据网格
            datagrid: function(){
                var that = this;
                that.element.table.datagrid({
                    autoRowHeight: true,
                    toolbar: "#toolbar",
                    singleSelect: true,
                    nowrap: false,
                    pagination: true,
                    columns:[[
                        {field: 'order', title: '排序', width: 80, align: 'center',sortable:"true",order:'desc'},
                        {field: 'shopName', title: '书店名称', width: 150, align: 'center',formatter:function(value,row,index){
                            return '<a href="' + site.shop + row.shopId + '" target="_blank">' + value + '</a>'
                        }},
                        {field: 'recommend', title: '推荐语', width: 150, align: 'center'},
                        {field: 'status', title: '状态', width: 80, align: 'center'},
                        {field: 'startTime', title: '定时发布', width: 80, align: 'center'},
                        {field: 'endTime', title: '定时结束', width: 80, align: 'center'},
                        {field: 'updateTime', title: '最后操作时间', width: 80, align: 'center',sortable:"true",order:'asc'},
                        {field: 'confId', title: '操作', width: 200, align: 'center',formatter:function(value,row,index){
                            var btn1 = "<button class='btn btn-default btn-xs' onclick='app.defaultDataFn.editItem(" + index + ")'>编辑</button>",
                                btn2 = "<button class='btn btn-default btn-xs' style='margin-left: 10px;' onclick='app.defaultDataFn.remove(" + index + ")'>删除</button>";
                                btn = btn1 + btn2;
                            return btn;
                        }}
                    ]],
                    onClickRow: function (rowIndex, rowData) {
                        that.element.table.datagrid('unselectRow', rowIndex);
                    },
                    onSortColumn:function(sort, order){
                        order = order == 'asc'?'ASC':'DESC';
                        if( sort == 'order' ){
                            that.opt.params.order.order = order;
                            that.opt.params.order.updateTime = 'DESC';
                            that.getData();
                        }else if(sort == 'updateTime'){
                            that.opt.params.order.order = 'ASC';
                            that.opt.params.order.updateTime = order;
                            that.getData();
                        }
                    }
                });
            },
            // 获取书店信息
            getShopInfo: function(){
                var that = this,
                    searchValue = that.element.searchValue.val();
                if(searchValue == ''){
                    that.alertInformation({type:true,msg:'请输入要查询的书店ID或书店名称'});
                    return;
                }
                $.ajax({
                    type: "post",
                    url: "/admin/shop/getShopInfo",
                    dataType: "json",
                    data: {
                        param: searchValue
                    },
                    success: function(res){
                        if(res.status == 1){
                            var data = [
                                {key : '书店名称',value : res.data.shopName,shopId : res.data.shopId},
                                {key : '书店ID',value : res.data.shopId},
                                {key : '拍卖会员等级',value : res.data.auctionLevel},
                                {key : '卖家信用',value : res.data.credit},
                                {key : '卖家好评率',value : res.data.rate}
                            ];
                            that.opt.addItem.shopId = res.data.shopId;
                            that.opt.addItem.shopName = res.data.shopName;
                            that.element.shopInfo.datagrid('loadData',data);
                        }else{
                            that.alertInformation({type:true,msg:res.message});
                        }
                    }
                })
            },
            // 书店信息表格
            auctionDataGrid: function(){
                var that = this;
                that.element.shopInfo.datagrid({
                    columns: [[
                        {field: 'key', title: '书店信息', width: 148, align: 'center'},
                        {field: 'value', title: '数据', width: 148, align: 'center',formatter:function(value,row,index){
                            if(row.key == '书店名称' ){
                                return '<a href="' + site.shop + row.shopId + '" target="_blank">' + value + '</a>';
                            }
                            if(row.key == '书店ID' ){
                                return '<a href="' + site.shop + row.value + '" target="_blank">' + value + '</a>';
                            }
                            return value;
                        }}
                    ]],
                    onClickRow: function (rowIndex, rowData) {
                        that.element.shopInfo.datagrid('unselectRow', rowIndex);
                    }
                });
            },
            // 获取当前时间
            getNowFormatDate:function(){
                var date = new Date();
                var seperator1 = "/";
                var seperator2 = ":";
                var month = date.getMonth() + 1;
                var strDate = date.getDate();
                if (month >= 1 && month <= 9) {
                    month = "0" + month;
                }
                if (strDate >= 0 && strDate <= 9) {
                    strDate = "0" + strDate;
                }
                var currentdate = month + seperator1 + strDate + seperator1 + date.getFullYear()
                        + " " + date.getHours() + seperator2 + date.getMinutes()
                        + seperator2 + date.getSeconds();
                return currentdate;
            },
            // 表格验证
            validate: function(){
                var that = this;
                that.element.addForm.validate({
                    rules: {
                        searchValue: "required",
                        recommendInfo: "required"
                    },
                    messages: {
                        searchValue: "请输入书店ID或书店名称",
                        recommendInfo: "请输入书店推荐语"
                    },
                    errorPlacement: function(error, element) {  
                        error.appendTo(element.parent());  
                    },
                    submitHandler: function(){   
                        if(that.opt.formType == 'add'){
                            that.addItemSubmit();
                        }
                        if(that.opt.formType == 'edit'){
                            that.editItemSubmit();
                        }
                        return false;
                    }  
                })
            },
            // 新增提交
            addItemSubmit: function(){
                var that = this;
                if(that.opt.addItem.shopId === ''){
                    that.alertInformation({type:false,msg:'请先点击查询按钮并检测店铺信息'});
                    return;
                }
                if(that.opt.addItem.shopName === ''){
                    that.alertInformation({type:false,msg:'请先点击查询按钮并检测店铺信息'});
                    return;
                }
                var result = {
                    isDefault : 1,
                    dataId : that.opt.addItem.shopId,
                    shopName : that.opt.addItem.shopName,
                    recommend : that.element.recommendInfo.val(),
                    order : that.element.order.numberspinner('getValue')
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
                            that.opt.addItem.shopId = '';
                            that.opt.addItem.shopName = '';
                            that.getData();
                        }else {
                            that.alertInformation({type:true,msg:res.message});
                        }
                        return false;
                    },
                    error:function(res){
                        that.alertInformation({type:true,msg:res.message});
                        return false;
                    }
                });
                return false;
            },
            // 编辑提交
            editItemSubmit: function(){
                var that = this;
                var result = {
                    confId : that.opt.editItem.confId,
                    recommend : that.element.recommendInfo.val(),
                    order : that.element.order.numberspinner('getValue')
                };
                $.ajax({
                    type: "post",
                    url: "/admin/module/editModuleData",
                    dataType: "json",
                    data:{
                        moduleId:that.opt.editItem.moduleId,
                        data: result
                    },
                    success:function(res){
                        if(res.status == 1){
                            that.alertInformation({type:true,msg:'操作成功'});
                            that.element.addWin.window('close');
                            that.getData();
                        }else {
                            that.alertInformation({type:true,msg:res.message});
                        }
                        return false;
                    },
                    error:function(res){
                        that.alertInformation({type:true,msg:res.message});
                        return false;
                    }
                });
                return false;
            },
            // 关闭新增窗口
            closeAddWin: function(){
                var that=this;
                that.element.addWin.window('close');
                that.opt.addItem.searchValue = '';
            },
            // 渲染表单
            initFormData: function(data){
                var that = this;
                if(data){
                    var shopInfo = [
                        {key : '书店名称',value : data.shopName,shopId : data.shopId},
                        {key : '书店ID',value : data.shopId},
                        {key : '拍卖会员等级',value : data.auctionLevel},
                        {key : '卖家信用',value : data.credit},
                        {key : '卖家好评率',value : data.rate}
                    ];
                    that.element.shopInfo.datagrid('loadData',shopInfo);
                    that.opt.editItem.moduleId = data.moduleId;
                    that.opt.editItem.confId = data.confId;
                    that.element.searchValue.val(data.shopName).attr("disabled","disabled");
                    that.element.recommendInfo.val(data.recommend);
                    that.element.order.numberspinner('setValue',data.order);
                }else{
                    var shopInfo = [
                        {key : '书店名称',value : ''},
                        {key : '书店ID',value : ''},
                        {key : '拍卖会员等级',value : ''},
                        {key : '卖家信用',value : ''},
                        {key : '卖家好评率',value : ''}
                    ];
                    that.element.shopInfo.datagrid('loadData',shopInfo);
                    that.opt.addItem.searchValue = '';
                    that.element.searchValue.val('').removeAttr("disabled");
                    that.element.recommendInfo.val('');
                    that.element.order.numberspinner('setValue',1);
                }
            },
            // 新增
            addItem: function(){
                var that = this;
                that.opt.formType = 'add';            
                that.initFormData();
                that.element.addWin.window('open');
                that.validate();
            },
            // 编辑
            editItem: function(index){
                var that = this,
                    data = that.opt.list[index];
                that.opt.formType = 'edit';
                that.initFormData(data);
                that.getShopInfo();
                that.element.addWin.window('open');
                that.validate();
            },
            // 删除
            remove: function(index){
                var that = this,
                    data = that.opt.list[index];
                $.messager.confirm('删除', '确定删除么？', function(type){
                    if (type){
                        $.ajax({
                            type: "post",
                            url: "/admin/module/deleteModuleData",
                            dataType: "json",
                            data:{
                                moduleId: data.moduleId,
                                confId: data.confId
                            },
                            success:function(res){
                                if(res.status == 1){
                                    that.alertInformation({type:true,msg:'操作成功'});
                                    that.getData(); 
                                }else{
                                    that.alertInformation({type:false,msg:res.message});
                                }
                                return false;
                            },
                            error:function(res){
                                that.alertInformation({type:false,msg:res.message});
                                return false;
                            }
                        })
                    }
                });
            },
            run: function(){
                var that = this;
                that.datagrid();
                that.auctionDataGrid();
                that.getData();
            }
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
                    that.defaultData();
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
        defaultData: function(){
             var that = this;
             if(that.initStatus.defaultDataSet){
                that.defaultDataFn.getData();
             }else{
                that.defaultDataFn.run();
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