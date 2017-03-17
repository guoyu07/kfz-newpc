;(function(window){
    window.app = {
        opt: {
            moduleId: moduleId,
            tabModuleId: '',
            formType: '',
            confId: '',
            tabList: [],
            list: [],
            addItem: {
                shopId: '',
                shopName: '',
                moduleId: ''
            },
            editItem: {
                moduleId: '',
                editModuleId: '',
                confId: ''
            },
            editOrder: {
                moduleId: '',
                confId: ''
            },
            params: {
                isDefault : 0,
                status : ['draft','published','ended'],
                keyWords: {
                    shopName: '',
                    itemName: ''
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
            curTab: $('#curTab'),
            table: $('#table'),
            addWin: $('#win-add'),
            orderWin: $('#win-order'),
            shopInfo: $('#table-shop-info'),
            searchValue: $('#searchValue'),
            tabType: $('#tabType'),
            startTimeType: $('#start-time-type'),
            endTimeType: $('#end-time-type'),
            startDateTimeBox: $('#start-datetimebox'),
            endDateTimeBox: $('#end-datetimebox'),
            recommendInfo: $('#recommendInfo'),
            order: $('#order'),
            selectOrder: $('#select-order'),
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
        getTabList: function(){
            var that = this;
            $.ajax({
                type: "post",
                url: "/admin/module/getMainSubModuleInfo",
                dataType: "json",
                data: {
                    mainModuleId: that.opt.moduleId
                },
                success: function(res){
                    if(res.status == 1){
                        if(res.data.submodule.length){
                            $.each(res.data.submodule,function(index,data){
                                var item = {
                                    label: data.moduleId,
                                    value: data.title + "（" + (data.isHide == '0'?'显':'隐')  + "）" 
                                };
                                that.opt.tabList.push(item);
                            });
                            that.combobox();
                        }
                    }else{
                        that.alertInformation({type:false,msg:res.message});
                    }
                }
            })
        },
        // 获取模块当前状态
        getData: function(){
            var that = this;
            that.element.table.datagrid('loading');
            $.ajax({
                type: "post",
                url: "/admin/module/getData",
                dataType: "json",
                data:{
                    moduleId: that.opt.tabModuleId,
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
                        return '<a href="' + site.shop + row.itemId + '" target="_blank">' + value + '</a>';
                    }},
                    {field: 'status', title: '状态', width: 80, align: 'center'},
                    {field: 'startTime', title: '定时发布', width: 80, align: 'center'},
                    {field: 'endTime', title: '定时结束', width: 80, align: 'center'},
                    {field: 'updateTime', title: '最后操作时间', width: 110, align: 'center',sortable:"true",order:'asc'},
                    {field: 'confId', title: '操作', width: 200, align: 'center',formatter:function(value,row,index){
                        var btn1 = "<button class='btn btn-default btn-xs' onclick='app.editItem(" + index + ")'>编辑</button>",
                            btn2 = "<button class='btn btn-default btn-xs' style='margin-left: 10px;' onclick='app.editOrder(" + index + ")'>修改排序</button>",
                            btn3 = "<button class='btn btn-default btn-xs' style='margin-left: 10px;' onclick='app.remove(" + index + ")'>删除</button>";
                            btn = btn1 + btn2 + btn3;
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
                that.alertInformation({type:false,msg:'请输入要查询的书店ID或书店名称'});
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
                    {field: 'key', title: '商品信息', width: 148, align: 'center'},
                    {field: 'value', title: '数据', width: 148, align: 'center',formatter:function(value,row,index){
                        if(row.key == '商品主图' ){
                            return '<img src="' + value + '" style="width:100px;">';
                        }
                        if(row.key == '商品名称' ){
                            return '<a href="' + site.book + row.itemId + '" target="_blank">' + value + '</a>';
                        }
                        if(row.key == '商品ID' ){
                            return '<a href="' + site.book + value + '" target="_blank">' + value + '</a>';
                        }
                        if(row.key == '书店名称' ){
                            return '<a href="' + site.shop + row.shopId + '" target="_blank">' + value + '</a>';
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
        // 多选框设置
        combobox: function(){
            var that=this;
            that.element.startTimeType.combobox({
                valueField: 'label',
                textField: 'value',
                data:[{label: '1',value: '直接发布'},{label: '2',value: '定时发布'}],
                multiple:false,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    if(newValue == 2){
                        that.element.startDateTimeBox.datetimebox({disabled:false});
                    }else {
                        that.element.startDateTimeBox.datetimebox({disabled:true});
                        that.element.startDateTimeBox.datetimebox('setValue',that.getNowFormatDate());
                    }
                }
            });
            that.element.endTimeType.combobox({
                valueField: 'label',
                textField: 'value',
                data:[{label: '1',value: '手动结束'},{label: '2',value: '定时结束'}],
                multiple:false,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    if(newValue == 2){
                        that.element.endDateTimeBox.datetimebox({disabled:false});
                    }else {
                        that.element.endDateTimeBox.datetimebox({disabled:true});
                    }
                }
            });
            that.element.curTab.combobox({
                valueField: 'label',
                textField: 'value',
                data:that.opt.tabList,
                multiple:false,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    that.opt.tabModuleId = newValue;
                    that.getData();
                }
            });
            that.element.curTab.combobox('setValue',that.opt.tabList[0].label);
            that.element.tabType.combobox({
                valueField: 'label',
                textField: 'value',
                data:that.opt.tabList,
                multiple:false,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    that.opt.addItem.moduleId = newValue;
                    that.opt.editItem.editModuleId = newValue;
                }
            });
            that.element.tabType.combobox('setValue',that.opt.tabList[0].label);
        },
        // 表格验证
        validate: function(){
            var that = this;
            that.element.addForm.validate({
                rules: {searchValue: "required"},
                messages: {searchValue: "请输入书店ID或书店名称"},
                errorPlacement: function(error, element) {  
                    error.appendTo(element.parent());  
                },
                submitHandler: function(){   
                    var startTimeType = that.element.startTimeType.combobox('getValue'),
                        endTimeType = that.element.endTimeType.combobox('getValue'),
                        startTime = that.element.startDateTimeBox.val(),
                        endTime = that.element.endDateTimeBox.val();
                    if( startTimeType == '2' && startTime == '' ){
                        that.alertInformation({type:false,msg:'发布时间不能为空'});
                        return;
                    }
                    if( endTimeType == '2' && endTime == '' ){
                        that.alertInformation({type:false,msg:'结束时间不能为空'});
                        return;
                    }
                    if(endTimeType == '2'){
                        if( startTime > endTime ){
                            that.alertInformation({type:false,msg:'发布时间不能大于结束时间'});
                            return;
                        }
                    }
                    if(that.opt.formType == 'add'){
                        that.addItemSubmit(startTime,endTime);
                    }
                    if(that.opt.formType == 'edit'){
                        that.editItemSubmit(startTime,endTime);
                    }
                    return false;
                }  
            })
        },
        // 新增提交
        addItemSubmit: function(startTime,endTime){
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
                isDefault : 0,
                dataId : that.opt.addItem.shopId,
                shopName : that.opt.addItem.shopName,
                startTime : startTime,
                endTime : endTime,
                order : that.element.order.numberspinner('getValue')
            };
            $.ajax({
                type: "post",
                url: "/admin/module/addModuleData",
                dataType: "json",
                data:{
                    moduleId:that.element.tabType.combobox('getValue'),
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
        editItemSubmit: function(startTime,endTime){
            var that = this;
            var result = {
                moduleId : that.opt.editItem.editModuleId,
                confId : that.opt.editItem.confId,
                startTime : startTime,
                endTime : endTime,
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
                that.element.tabType.combobox("setValue",data.moduleId);
                that.element.startTimeType.combobox('setValue', '2' );
                that.element.startDateTimeBox.datetimebox({disabled:false});
                that.element.startDateTimeBox.datetimebox('setValue',data.startTime );
                if(data.endTime){
                    that.element.endTimeType.combobox('setValue', '2' );
                    that.element.endDateTimeBox.datetimebox({disabled:false});
                }else{
                    that.element.endTimeType.combobox('setValue', '1');
                }
                that.element.endDateTimeBox.datetimebox('setValue',data.endTime );
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
                that.element.searchValue.val('').removeAttr("disabled");
                that.element.tabType.combobox("setValue",that.opt.tabList[0].label);
                that.element.startTimeType.combobox('setValue', '1');
                that.element.endTimeType.combobox('setValue', '1');
                that.element.startDateTimeBox.datetimebox('setValue',that.getNowFormatDate());
                that.element.endDateTimeBox.datetimebox('setValue','');
                that.element.order.numberspinner('setValue', 1);
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
        // 修改排序
        editOrder: function(index){
            var that = this,
                data = that.opt.list[index];
            that.opt.editOrder.moduleId = data.moduleId;
            that.opt.editOrder.confId = data.confId;
            that.initFromOrder(data.order);
            that.element.orderWin.window('open');
            that.element.orderWin.panel("move",{top:$(document).scrollTop() + ($(window).height()-250) * 0.5});
        },
        // 渲染排序
        initFromOrder: function(order){
            var that = this;
            that.element.selectOrder.numberspinner('setValue',order);
        },
        // 选择排序
        selectOrder: function(){
            var that = this;
            that.element.orderWin.window('close');
            $.ajax({
                type: "post",
                url: "/admin/module/sortModuleData",
                dataType: "json",
                data:{
                    moduleId: that.opt.editOrder.moduleId,
                    confId: that.opt.editOrder.confId,
                    pos: that.element.selectOrder.numberspinner('getValue')
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
        },
        // 关闭排序窗口
        closeOrderWin: function(){
            var that = this;
            that.opt.selectOrder = '';
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
        search: function(value){
            app.opt.params.keyWords.itemName = value;
            app.opt.params.keyWords.shopName = value;
            app.getData();
        },
        run: function(){
            var that = this;
            that.datagrid();
            that.shopDataGrid();
            that.getTabList();
        }
    };
    app.run();
})(window)