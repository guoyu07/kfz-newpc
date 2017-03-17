;(function(window){
    window.app = {
        opt: {
            moduleId: moduleId,
            formType: '',
            confId: '',
            auctionId: '',
            list: [],
            addItem: {
                isbn: '',
                itemName: '',
                author: ''
            },
            editItem: {
                moduleId: '',
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
                    isbn: '',
                    shopName: '',
                    author: ''
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
            curStatus: $('#curStatus'),
            curStartTime: $('#curStartTime'),
            curEndTime: $('#curEndTime'),
            table: $('#table'),
            addWin: $('#win-add'),
            orderWin: $('#win-order'),
            isbnInfo: $('#table-isbn-info'),
            ISBN: $('#ISBN'),
            startTimeType: $('#start-time-type'),
            endTimeType: $('#end-time-type'),
            startDateTimeBox: $('#start-datetimebox'),
            endDateTimeBox: $('#end-datetimebox'),
            order: $('#order'),
            selectOrder: $('#select-order'),
            addForm: $("#form-add"),
            loading:$('.loading-box')
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
        // 获取模块当前状态
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
                        that.element.table.datagrid('loaded');
                        that.pagination(res);
                        that.element.loading.hide();
                    }else {
                        that.element.table.datagrid('loaded');
                        that.alertInformation({type:false,msg:data.message});
                    }
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
                    that.element.table.datagrid('loading');
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
                    {field: 'dataId', title: 'ISBN', width: 150, align: 'center',formatter:function(value,row,index){
                        return '<a href="' + site.pm + row.itemId + '" target="_blank">' + value + '</a>'
                    }},
                    {field: 'itemName', title: '书名', width: 150, align: 'center',formatter:function(value,row,index){
                        return '<a href="' + site.user + row.userId + '" target="_blank">' + value + '</a>'
                    }},
                    {field: 'author', title: '作者', width: 150, align: 'center'},
                    {field: 'status', title: '状态', width: 80, align: 'center'},
                    {field: 'startTime', title: '定时发布', width: 80, align: 'center'},
                    {field: 'endTime', title: '定时结束', width: 80, align: 'center'},
                    {field: 'updateTime', title: '最后操作时间', width: 110, align: 'center',sortable:"true",order:'asc'},
                    {field: 'confId', title: '操作', width: 200, align: 'center',formatter:function(value,row,index){
                        var btn1 = "<button class='btn btn-default btn-xs' onclick='app.editItem(" + index + ")'>编辑</button>",
                            btn2 = "<button class='btn btn-default btn-xs' style='margin-left: 10px;' onclick='app.editOrder(" + index + ")'>修改固定位</button>",
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
        // 获取ISBN信息
        getAuctionItem: function(){
            var that = this,
                ISBN = that.element.ISBN.val();
            if(ISBN == ''){
                that.alertInformation({type:false,msg:'请输入要查询的ISBN'});
                return;
            }
            $.ajax({
                type: "post",
                url: "/admin/module/getItemInfoByIsbn",
                dataType: "json",
                data: {
                    isbn: ISBN
                },
                success: function(res){
                    if(res.status == 1){
                        var data = [
                            {key : '封面',value : res.data.itemImg},
                            {key : 'ISBN',value : res.data.isbn},
                            {key : 'ISBN标准名称',value : res.data.itemName,itemId : res.data.isbn},
                            {key : '作者',value : res.data.author},
                            {key : '出版社',value : res.data.press},
                            {key : '图书简介',value : res.data.jianjie},
                            {key : '出版时间',value : res.data.year},
                            {key : '严格数量',value : res.data.strictBindNum},
                            {key : '非严格数量',value : res.data.unStrictBindNum},
                            {key : '图书定价',value : res.data.price},
                            {key : '孔网最低价格',value : res.data.minPrice},
                            {key : '孔网最高价格',value : res.data.maxPrice}
                        ];
                        that.opt.addItem.isbn = res.data.isbn;
                        that.opt.addItem.itemName = res.data.itemName;
                        that.opt.addItem.author = res.data.author;
                        that.element.isbnInfo.datagrid('loadData',data);
                    }else{
                        $.messager.alert('系统提示',res.message);
                    }
                }
            })
        },
        // 拍品信息表格
        auctionDataGrid: function(){
            var that = this;
            that.element.isbnInfo.datagrid({
                nowrap: false,
                columns: [[
                    {field: 'key', title: 'ISBN信息', width: 148, align: 'center'},
                    {field: 'value', title: '数据', width: 148, align: 'center',formatter:function(value,row,index){
                        if(row.key == '封面' ){
                            return '<img src="' + value + '" style="width:100px;">';
                        }
                        if(row.key == 'ISBN' ){
                            return '<a href="' + site.pm + row.value + '" target="_blank">' + value + '</a>';
                        }
                        if(row.key == 'ISBN标准名称' ){
                            return '<a href="' + site.pm + row.itemId + '" target="_blank">' + value + '</a>';
                        }
                        return value;
                    }}
                ]],
                onClickRow: function (rowIndex, rowData) {
                    that.element.isbnInfo.datagrid('unselectRow', rowIndex);
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
            that.element.curStatus.combobox({
                valueField: 'label',
                textField: 'value',
                data:[
                    {label: 'draft',value: '草稿'},
                    {label: 'published',value: '发布中'},
                    {label: 'ended',value: '已结束'}
                ],
                multiple:true,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    that.opt.params.status = newValue;
                    that.getData();
                }
            });
            that.element.curStatus.combobox('setValues',['draft','published','ended']);
            that.element.curStartTime.combobox({
                valueField: 'label',
                textField: 'value',
                data:[
                    {label: 'progressing',value: '发布中'},
                    {label: 'countDown',value: '倒计时'}
                ],
                multiple:false,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    that.opt.params.regularlyStart = newValue;
                    that.getData();
                }
            });
            that.element.curEndTime.combobox({
                valueField: 'label',
                textField: 'value',
                data:[
                    {label: 'countDown',value: '倒计时'},
                    {label: 'break',value: '手动'}
                ],
                multiple:false,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    that.opt.params.regularlyEnd = newValue;
                    that.getData();
                }
            });
        },
        // 表格验证
        validate: function(){
            var that = this;
            that.element.addForm.validate({
                rules: {auctionId: "required"},
                messages: {auctionId: "请输入拍主ID"},
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
            if(that.opt.addItem.isbn === ''){
                that.alertInformation({type:false,msg:'请先点击查询按钮并检测拍品信息'});
                return;
            }
            var result = {
                isDefault : 0,
                dataId : that.opt.addItem.isbn,
                itemName : that.opt.addItem.itemName,
                author : that.opt.addItem.author,
                startTime : startTime,
                endTime : endTime,
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
                        that.opt.addItem.isbn = '';
                        that.opt.addItem.itemName = '';
                        that.opt.addItem.author = '';
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
            that.opt.addItem.isbn = '';
        },
        // 渲染表单
        initFormData: function(data){
            var that = this;
            if(data){
                var isbnInfo = [
                    {key : '封面',value : data.itemImg},
                    {key : 'ISBN',value : data.isbn},
                    {key : 'ISBN标准名称',value : data.itemName,itemId : data.isbn},
                    {key : '作者',value : data.author},
                    {key : '出版社',value : data.press},
                    {key : '图书简介',value : data.jianjie},
                    {key : '出版时间',value : data.year},
                    {key : '严格数量',value : data.strictBindNum},
                    {key : '非严格数量',value : data.unStrictBindNum},
                    {key : '图书定价',value : data.price},
                    {key : '孔网最低价格',value : data.minPrice},
                    {key : '孔网最高价格',value : data.maxPrice}
                ];
                that.element.isbnInfo.datagrid('loadData',isbnInfo);
                that.opt.editItem.moduleId = data.moduleId;
                that.opt.editItem.confId = data.confId;
                that.element.ISBN.val(data.dataId).attr("disabled","disabled");
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
                var isbnInfo = [
                    {key : '封面',value : ''},
                    {key : 'ISBN',value : ''},
                    {key : 'ISBN标准名称',value : ''},
                    {key : '作者',value : ''},
                    {key : '出版社',value : ''},
                    {key : '图书简介',value : ''},
                    {key : '出版时间',value : ''},
                    {key : '严格数量',value : ''},
                    {key : '非严格数量',value : ''},
                    {key : '图书定价',value : ''},
                    {key : '孔网最低价格',value : ''},
                    {key : '孔网最高价格',value : ''}
                ];
                that.element.isbnInfo.datagrid('loadData',isbnInfo);
                that.opt.addItem.isbn = '';
                that.opt.addItem.itemName = '';
                that.opt.addItem.author = '';
                that.element.ISBN.val('').removeAttr('disabled');
                that.element.startTimeType.combobox('setValue', '1');
                that.element.endTimeType.combobox('setValue', '1');
                that.element.startDateTimeBox.datetimebox('setValue',that.getNowFormatDate());
                that.element.endDateTimeBox.datetimebox('setValue','');
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
            that.getAuctionItem();
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
                        that.element.table.datagrid('loading');
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
            var that=this,
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
                                that.element.table.datagrid('loading');
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
        //搜索
        search: function(value){
            app.opt.params.keyWords.isbn = value;
            app.opt.params.keyWords.shopName = value;
            app.opt.params.keyWords.author = value;
            app.getData();
        },
        run: function(){
            var that = this;
            that.datagrid();
            that.auctionDataGrid();
            // that.getData();
            setTimeout(function(){
                that.combobox();
            });
        }
    };
    app.run();
})(window)