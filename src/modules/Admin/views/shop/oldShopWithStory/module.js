;(function(window){
    window.app = {
        hash: (!window.location.hash)?"0":window.location.hash.split('=')[1],
        moduleId: moduleId,
        initStatus: {
            moduleTabSet: false,                           //TAB模块初始化状态
            moduleTitleSet: false                           //标题模块初始化状态
        },
        element: {
            navItem: $('#navPill-tab li'),
            tabItem: $('.tab-box li')
        },
        moduleTabFn: {
            opt: {
                moduleId: moduleId,                      
                formType: '',
                list: [],
                showType: '',
                orderType: '',
                editItem: {
                    moduleId: ''
                }
            },
            element: {
                table: $('#table'),
                addWin: $('#win-add'),
                orderWin: $('#win-order-type'),
                tabName: $('#tabName'),
                tabNum: $('#tabNum'),
                minPrice: $('#minPrice'),
                maxPrice: $('#maxPrice'),
                showType: $('#showType'),
                order: $('#order'),
                orderType: $('#orderType'),
                orderTip: $('#orderTip'),
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
                    url: "/admin/module/getMainSubModuleInfo",
                    dataType: "json",
                    data:{
                        mainModuleId:that.opt.moduleId
                    },
                    success: function(res){
                        if(res.status == 1){
                            that.opt.list = res.data.submodule;
                            that.element.table.datagrid('loadData',res.data.submodule);
                            if(res.data.showType == '0'){
                                that.element.orderTip.text('随机排序');
                                that.element.orderType.combobox('setValue','0');
                            }else{
                                that.element.orderTip.text('固定排序');
                                that.element.orderType.combobox('setValue','1');
                            }
                        }
                        that.element.table.datagrid('loaded');
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
                    autoRowHeight: true,
                    toolbar: "#toolbar",
                    singleSelect: true,
                    nowrap: false,
                    pagination: true,
                    columns:[[
                        {field: 'order', title: '排序', width: 80, align: 'center',formatter:function(value,row,index){
                            return row.params.order;
                        }},
                        {field: 'title', title: 'TAB名称', width: 150, align: 'center'},
                        {field: 'catId', title: '分类编号', width: 150, align: 'center',formatter:function(value,row,index){
                            return row.params.catId;
                        }},
                        {field: 'minPrice', title: '价格下限', width: 80, align: 'center',formatter:function(value,row,index){
                            return row.params.price.min;
                        }},
                        {field: 'maxPrice', title: '价格上限', width: 80, align: 'center',formatter:function(value,row,index){
                            return row.params.price.max;
                        }},
                        {field: 'isHide', title: '状态', width: 80, align: 'center',formatter:function(value,row,index){
                            if(value == '0'){
                                return '显示';
                            }else{
                                return '隐藏';
                            }
                        }},
                        {field: 'updateTime', title: '最后操作时间', width: 110, align: 'center'},
                        {field: 'confId', title: '操作', width: 200, align: 'center',formatter:function(value,row,index){
                            var btn1 = "<button class='btn btn-default btn-xs' onclick='app.moduleTabFn.editItem(" + index + ")'>编辑</button>",
                                btn2 = "<button class='btn btn-default btn-xs' style='margin-left: 10px;' onclick='app.moduleTabFn.remove(" + index + ")'>删除</button>";
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
            // 多选框设置
            combobox: function(){
                var that=this;
                that.element.showType.combobox({
                    valueField: 'label',
                    textField: 'value',
                    data:[{label: '0',value: '显示此TAB'},{label: '1',value: '隐藏此TAB'}],
                    multiple:false,
                    panelHeight:'auto',
                    onChange: function(newValue,oldValue){
                        that.opt.showType = newValue;
                    }
                });
                that.element.orderType.combobox({
                    valueField: 'label',
                    textField: 'value',
                    data:[{label: '1',value: '固定排序'},{label: '0',value: '随机排序'}],
                    multiple:false,
                    panelHeight:'auto',
                    onChange: function(newValue,oldValue){
                        that.opt.orderType = newValue;
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
                        tabName: "required",
                        tabNum: "required",
                        minPrice: {
                            required: true,
	                        number:true
                        },
                        maxPrice: {
                            required: true,
	                        number:true
                        }
                    },
                    messages: {
                        tabName: "请输入TAB名称",
                        tabNum: "请输入分类编号",
                        minPrice: {
                            required: "请输入价格下限",
                            number: "必须是数字"
                        },
                        maxPrice: {
                            required: "请输入价格上限",
                            number: "必须是数字"
                        }
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
                var result = {
                    title : that.element.tabName.val(),
                    catId : that.element.tabNum.val(),
                    minPrice : that.element.minPrice.val(),
                    maxPrice : that.element.maxPrice.val(),
                    isHide : that.opt.showType,
                    order : that.element.order.numberspinner('getValue')
                };
                $.ajax({
                    type: "post",
                    url: "/admin/module/addModule",
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
            editItemSubmit: function(){
                var that = this;
                var result = {
                    title : that.element.tabName.val(),
                    catId : that.element.tabNum.val(),
                    minPrice : that.element.minPrice.val(),
                    maxPrice : that.element.maxPrice.val(),
                    isHide : that.opt.showType,
                    order : that.element.order.numberspinner('getValue')
                };
                $.ajax({
                    type: "post",
                    url: "/admin/module/editModule",
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
            initFormData: function(data){
                var that = this;
                if(data){
                    that.opt.editItem.moduleId = data.moduleId;
                    that.element.tabName.val(data.title);
                    that.element.tabNum.val(data.params.catId);
                    that.element.minPrice.val(data.params.price.min);
                    that.element.maxPrice.val(data.params.price.max);
                    that.opt.showType = data.isHide;
                    that.element.showType.combobox('setValue', that.opt.showType);
                    that.element.order.numberspinner('setValue',data.params.order);
                }else{
                    that.element.tabName.val('');
                    that.element.tabNum.val('');
                    that.element.minPrice.val('');
                    that.element.maxPrice.val('');
                    that.element.showType.combobox('setValue', '0');
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
                that.element.addWin.window('open');
                that.validate();
            },
            editOrder: function(){
                var that = this;
                that.element.orderWin.window('open');
            },
            editOrderType: function(){
                var that = this;
                $.ajax({
                    type: "post",
                    url: "/admin/module/changeModuleShowType",
                    dataType: "json",
                    data:{
                        moduleId:that.opt.moduleId,
                        showType: that.opt.orderType
                    },
                    success:function(res){
                        if(res.status == 1){
                            that.alertInformation({type:true,msg:'操作成功'});
                            that.element.orderWin.window('close');
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
            },
            closeOrderWin: function(){
                var that = this;
                that.element.orderWin.window('close');
            },
            // 删除
            remove: function(index){
                var that = this,
                    data = that.opt.list[index];
                $.messager.confirm('删除', '确定删除么？', function(type){
                    if (type){
                        $.ajax({
                            type: "post",
                            url: "/admin/module/deleteModule",
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
                that.combobox();
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
                                    that.alertInformation({type:true,msg:'操作成功'});
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
                    that.moduleTab();
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
        moduleTab: function(){
             var that = this;
             if(that.initStatus.moduleTabSet){
                that.moduleTabFn.getData();
             }else{
                that.moduleTabFn.run();
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