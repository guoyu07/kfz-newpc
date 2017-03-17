;(function(window){
    window.banner = {
        opt: {
            chooseVal:null,     //用来保存单选按钮的值    
            jcrop_api:null,     //裁剪图片
            validate:null,      //表单验证
            img:null,           //保存上传图片地址
            imgurl:null,        //图片地址
            tailorData:null,    //保存图片裁剪后的数据
            moduleId:$('#moduleId').val(),     //模块id
            xsize: '200',
            ysize: '26.3',
            boundx: null,
            boundy: null,
            data:null,
            urlType:null,
            editDataIndex:null,
            editDataId:null,
            startTimeValue:null,
            endTimeValue:null,
            params:{
                page: {
                    maxRowPerPage: '',
                    requirePage:''
                },
                keyWords:{
                    firstDesc:''
                },
                status:["draft", "published", "ended"],
                regularlyStart:'',
                regularlyEnd:'',
                order: {
                    order: 'ASC',
                    updateTime: 'DESC'
                },
                isDefault : 0,
            }
        },
        run: function(){
            var that=this;
            that.uploader();        //上传
            that.validate();        //表单验证
            that.tailor();          //提交裁剪后的图片
            that.datagrid();        //表格
            // that.getDataAction();   //获取表格数据
            // that.jcrop();           //图片裁剪
            that.startTime();
            that.endTime();
            that.getModuleInfo();
        },
        getModuleInfo: function(){
            var that=this;
            $.ajax({
                type: "post",
                url: "/admin/module/getModuleInfo",
                dataType: "json",
                data:{
                    moduleId:that.opt.moduleId
                },
                success:function(data){
                    if(data.status==1){
                         var value=data.data.isHide == 0 ? '显示' : '隐藏';
                        $('#banner-showType-val').html(value);
                    }else {
                        that.alertInformation({type:false,msg:data.message});
                    }
                    that.combobox();        //多选框
                }
            })
        },
        cancle: function(){
            var that=this;
            $('#dlg').window('close');
            that.opt.jcrop_api.destroy();
            that.setData();
        },
        showType:function(index){
            this.opt.showType = index;
        },
        typeSave:function(){
            var that=this;
            if(that.opt.showType=='' || that.opt.showType == undefined || that.opt.showType == null){
                that.alertInformation({type:false,msg:'单选为必选项'});
                return;
            }

            var value = this.opt.showType == 0 ? '显示' : '隐藏';
            var type = $('#banner-showType-val').html();
            if(value == type){
                that.alertInformation({type:false,msg:'你已经选择了'+value});
                return;
            }
            $.ajax({
                type: "post",
                url: "/admin/module/changeModuleShowStatus",
                dataType: "json",
                data:{
                    moduleId:that.opt.moduleId,
                    isHide:this.opt.showType
                },
                success:function(data){
                    if(data.status==1){
                        $('#banner-showType-val').html(value);
                        $('#showType').window('close');
                        that.alertInformation({type:true,msg:'操作成功'});
                    }else {
                        that.alertInformation({type:false,msg:data.message})
                    }
                }
            })
        },
        getDataAction: function(){
            var that=this;
            $('#tt').datagrid('loading');
            $.ajax({
                type: "post",
                url: "/admin/module/getData",
                dataType: "json",
                data:{
                    moduleId:that.opt.moduleId,
                    params: that.opt.params
                },
                success: function(data){
                    if(data.status==1){
                        that.opt.data=data;
                        $('#tt').datagrid('loadData',that.opt.data.data);    // reload the user data
                        $('#tt').datagrid('loaded');
                        that.pagination(data);
                    }else {
                        $('#tt').datagrid('loaded');
                        that.alertInformation({type:false,msg:data.message});
                    }
                }
            })
        },
        pagination:function(data){
            var that=this;
            $('#tt').datagrid('getPager').pagination({
                total:parseInt(data.other.page.total),
                pageSize:parseInt(data.other.page.pageSize),
                pageNumber:parseInt(data.other.page.pageNumber),
                layout:['list','sep','first','prev','links','next','last','sep','refresh'],
                pageList: [5,10,20,30,40,50],
                onSelectPage:function(pageNumber, pageSize){
                    $('#tt').datagrid('loading');
                    that.opt.params.page.requirePage = parseInt(pageNumber);
                    that.opt.params.page.maxRowPerPage = parseInt(pageSize);
                    that.getDataAction();
                }
            });
        },
        datagrid: function(){
            var that=this;
            $('#tt').datagrid({
                autoRowHeight:true,
                toolbar: "#toolbar",
                singleSelect:true,
                nowrap: false,//数据长度超出列宽时将会自动截取
                pagination:true,
                columns:[[
                    {field: 'order', title: '排序', width: 50, align: 'center',sortable:"true",order:'desc'},
                    {field: 'imgUrl', title: '图片', width: 160, align: 'center',formatter:function(value,row,index){
                        return "<a href="+row.prefix+value+" target='_blank'>"+row.prefix+value+"</a>"
                    }},
                    {field: 'linkUrl', title: '点击图片跳转URL', width: 160, align: 'center',formatter:function(value,row,index){
                        return "<a href="+value+" target='_blank'>"+value+"</a>"
                    }},
                    {field: 'firstDesc', title: '描述', width: 160, align: 'center'},
                    {field: 'status', title: '状态', width: 50, align: 'center'},
                    {field: 'startTime', title: '定时发布', width: 80, align: 'center'},
                    {field: 'endTime', title: '定时结束', width: 80, align: 'center'},
                    {field: 'updateTime', title: '最后操作时间', width: 150, align: 'center',sortable:"true",order:'asc'},
                    {field: 'confId', title: '操作', width: 150, align: 'center',formatter:function(value,row,index){
                        return '<a href="javascript:;" name="add" style="color: #428bca;" onclick="banner.editOrder('+value+','+row.moduleId+','+row.order+')"></a>' +
                            "<a href='javascript:;' name='edit' style='color: #428bca;' onclick='banner.editData("+value+","+row.moduleId+","+index+")'></a>" +
                            '<a href="javascript:;" name="remove" style="color: #428bca;" onclick="banner.removeData('+value+','+row.moduleId+')"></a>'
                    }}
                ]],
                onLoadSuccess:function(data){
                    $("a[name='add']").linkbutton({text:'修改排序',plain:true});
                    $("a[name='edit']").linkbutton({text:'编辑',plain:true});
                    $("a[name='remove']").linkbutton({text:'删除',plain:true});
                },
                onClickRow: function (rowIndex, rowData) {
                    $(this).datagrid('unselectRow', rowIndex);
                },
                onSortColumn:function(sort, order){
                    order=order=='asc'?'ASC':'DESC';
                    if(sort=='order'){
                        that.opt.params.order = {
                            order: order,
                            updateTime: 'DESC'
                        } 
                        that.getDataAction();
                        that.pagination(that.opt.data);
                    }else if(sort=='updateTime'){
                        that.opt.params.order = {
                            updateTime: order,
                            order: 'ASC'
                        } 
                        that.getDataAction();
                        that.pagination(that.opt.data);
                    }
                }
            });
        },
        newUser: function(){
            var that=this;
            that.jcrop();  
            $('#dlg').window('open');
            that.setData();
        },
        editData: function(dataId,id,index){
            var that=this;
            that.jcrop();  
            that.setData(that.opt.data.data[index]);
            $('#dlg').window('open');
            $("#dlg").panel("move",{top:$(document).scrollTop() + ($(window).height()-724) * 0.5,width:800});
            that.opt.urlType='edit';
            that.opt.editDataIndex=id;
            that.opt.editDataId=dataId;
        },
        editOrder: function(index,id,order){
            $('#orderIndex').numberspinner('setValue', order);
            $('#editOrder').window('open');
            $("#editOrder").panel("move",{top:$(document).scrollTop() + ($(window).height()-250) * 0.5});
            $('#editOrderBtn').data('order',index);  
            $('#editOrderBtn').data('moduleId',id);  
        },
        editOrderClick:function(){
            var that=this;
            var order=$('#orderIndex').val();
            var index=$('#editOrderBtn').data('order');
            var id=$('#editOrderBtn').data('moduleId');
            $.ajax({
                type: "post",
                url: "/admin/module/sortModuleData",
                dataType: "json",
                data:{
                    moduleId:id,
                    confId: index,
                    pos:order
                },
                success:function(data){
                    if(data.status==1){
                        $('#editOrder').window('close')
                        $('#tt').datagrid('loading');
                        that.getDataAction();   //获取表格数据
                        that.alertInformation({type:true,msg:'操作成功'});
                        return;
                    }else {
                        that.alertInformation({type:false,msg:data.message});
                    }
                }
            })
        },
        removeData:function(index,id){
            var that=this;
            $.messager.confirm('删除', '确定删除么？', function(r){
                if (r){
                    $.ajax({
                        type: "post",
                        url: "/admin/module/deleteModuleData",
                        dataType: "json",
                        data:{
                            moduleId:id,
                            confId: index
                        },
                        success:function(data){
                            if(data.status==1){
                                $('#tt').datagrid('loading');
                                that.getDataAction();   //获取表格数据
                                that.alertInformation({type:true,msg:'操作成功'});
                                return false;
                            }else {
                                that.alertInformation({type:false,msg:data.message});
                            }
                        }
                    })
                }
            });
        },
        combobox: function(){
            var that=this;
            $('#zt').combobox({
                valueField: 'label',
                textField: 'value',
                data: [{
                    label: 'draft',
                    value: '草稿'
                },{
                    label: 'published',
                    value: '发布中'
                },{
                    label: 'ended',
                    value: '已结束'
                }],
                multiple:true,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    newValue=newValue.splice(',');
                    that.opt.params.status = newValue;
                    console.log(that.opt.params.status);
                    that.getDataAction();
                }
            });
            $('#zt').combobox('setValues', ['draft','published','ended']);
            $('#dsfb').combobox({
                valueField: 'label',
                textField: 'value',
                data: [{
                    label: 'progressing',
                    value: '发布中'
                },{
                    label: 'countDown',
                    value: '倒计时'
                }],
                multiple:false,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    that.opt.params.regularlyStart = newValue;
                    console.log(that.opt.params.regularlyStart);
                    that.getDataAction();
                }
            });
            $('#dsjs').combobox({
                valueField: 'label',
                textField: 'value',
                data: [{
                    label: 'countDown',
                    value: '倒计时'
                },{
                    label: 'break',
                    value: '手动'
                }],
                multiple:false,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    that.opt.params.regularlyEnd = newValue;
                    console.log(that.opt.params.regularlyEnd);
                    that.getDataAction();
                }
            });
        },
        doSearch: function(value){
            banner.opt.params.keyWords.firstDesc = value;
            banner.getDataAction();
        },
        linkSearch:function(){
            var that=this;
            if($('#searchName').val()){
                var checkfiles=new RegExp("((^http)|(^https)|(^ftp)):\/\/(\\w)+\.(\\w)+");
                if(!checkfiles.test($('#searchName').val())){
                    that.alertInformation({type:false,msg:'请输入正确的地址'});
                    return;
                }
                $.ajax({
                    type: "post",
                    url: "http://newpc.kfz.com/demo/upload",
                    dataType: "json",
                    data:{
                        imgurl:$('#searchName').val()
                    },
                    success: function(data){
                        if(data.status==true){
                            $('#element_id').attr({src:data.data.prefix+data.data.imgurl});
                            $('.jcrop-preview').attr({src:data.data.prefix+data.data.imgurl});
                            that.opt.img=data.data.prefix+data.data.imgurl;
                            that.opt.imgurl=data.data.imgurl;
                            if(that.opt.jcrop_api){
                                that.opt.jcrop_api.setImage(data.data.prefix+data.data.imgurl);
                            }
                        }else {
                            that.alertInformation({type:false,msg:data.error});
                        }
                        
                    }
                })
            }
        },
        linkurl:function(){
            $('.dlg-link-box a').html($('#linkurl').val());
            $('.dlg-link-box a').attr({href:$('#linkurl').val()});
        },
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
        startTime:function(index){
            var that=this;
            $('#dlg-start-time').combobox({
                valueField: 'label',
                textField: 'value',
                data:[{
                    label: '1',
			        value: '直接发布'
                },{
                    label: '2',
			        value: '定时发布'
                }],
                multiple:false,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    if(newValue==2){
                        $('.dlg-start-time .easyui-datetimebox').datetimebox({
                            disabled:false
                        });
                        that.opt.startTimeValue = 2;
                    }else {
                        $('.dlg-start-time .easyui-datetimebox').datetimebox({
                            disabled:true
                        });
                        $('.dlg-start-time .easyui-datetimebox').datetimebox('setValue',that.getNowFormatDate());
                        that.opt.startTimeValue = 1;
                    }
                },
                onLoadSuccess:function(){
                    $('#dlg-start-time').combobox('select', '直接发布');
                    that.opt.startTimeValue=1;
                }
            });
            // $('#dlg-start-time').combobox('select', '直接发布');
        },
        endTime:function(index){
            var that=this;
            $('#dlg-end-time').combobox({
                valueField: 'label',
                textField: 'value',
                data:[{
                    label: '1',
			        value: '手动结束'
                },{
                    label: '2',
			        value: '定时结束'
                }],
                multiple:false,
                panelHeight:'auto',
                onChange: function(newValue,oldValue){
                    if(newValue==2){
                        $('.dlg-end-time .easyui-datetimebox').datetimebox({
                            disabled:false
                        });
                        that.opt.endTimeValue=2;
                    }else {
                        $('.dlg-end-time .easyui-datetimebox').datetimebox({
                            disabled:true
                        });
                        that.opt.endTimeValue=1;
                    }
                },
                onLoadSuccess:function(){
                    $('#dlg-end-time').combobox('select', '手动结束');
                    that.opt.endTimeValue=1;
                }
            });
        },
        uploader: function(){
            var that=this;
            var uploader = WebUploader.create({
                // 选完文件后，是否自动上传。
                auto:true,
                // 选择文件的按钮。可选。
                // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                pick: {
                    id: '#filePicker',
                    label: '点击选择图片',
                    multiple:false
                },
                // 只允许选择图片文件。
                accept: {
                    title: 'Images',
                    extensions: 'jpg,jpeg,png',
                    mimeTypes: 'image/jpg,image/jpeg,image/png'   //修改这行
                },
                duplicate:true,
                // swf文件路径
                swf: '../../../../../libs/webuploader/Uploader.swf',
                // 文件接收服务端。
                server: 'http://newpc.kfz.com/demo/upload'
            });

            uploader.on('fileQueued', function (file) {

            });
            uploader.on('uploadSuccess', function (file, data) {
                if(data.status == true){
                    $('#element_id').attr({src:data.data.prefix+data.data.imgurl});
                    $('.jcrop-preview').attr({src:data.data.prefix+data.data.imgurl});
                    that.opt.img=data.data.prefix+data.data.imgurl;
                    that.opt.imgurl=data.data.imgurl;
                    if(that.opt.jcrop_api){
                        that.opt.jcrop_api.setImage(data.data.prefix+data.data.imgurl);
                    }
                }else {
                    that.alertInformation({type:false,msg:data.error});
                }
            });
            uploader.on('error', function (error) {
                console.log(error)
            });
            uploader.on('uploadComplete', function () {
               $('#loading-center-absolute').hide();
            });
            uploader.on('startUpload', function () {
                $('#loading-center-absolute').show();
            });
        },
        jcrop: function(ele){
            var that=this;
            $('.jcrop-preview').show();
            $('#element_id').Jcrop({
                boxWidth:460,
                boxHeight:103,
                aspectRatio:7.6,
                bgOpacity: 0.7,
                onChange: updatePreview,
                onSelect: updatePreview
            },function(){
                that.opt.jcrop_api=this;
            });
            function updatePreview(c){
                var bounds = that.opt.jcrop_api.getBounds();
                that.opt.boundx = bounds[0];
                that.opt.boundy = bounds[1];
                if (parseInt(c.w) > 0)
                {
                    var rx = that.opt.xsize / c.w;
                    var ry = that.opt.ysize / c.h;

                    $('.jcrop-preview').css({
                        width: Math.round(rx * that.opt.boundx) + 'px',
                        height: Math.round(ry * that.opt.boundy) + 'px',
                        marginLeft: '-' + Math.round(rx * c.x) + 'px',
                        marginTop: '-' + Math.round(ry * c.y) + 'px'
                    });
                }
                that.opt.tailorData = {w:c.w,h:c.h,x:c.x,y:c.y}
            }
        },
        tailor: function(){
            var that=this;
            $('#dlg-tailor').on('click',function(){
                if(that.opt.img){
                    var data=that.opt.tailorData;
                    if(data==null || data==undefined || data == ''){
                        return;
                    }
                    $.ajax({
                        type: "post",
                        url: "http://newpc.kfz.com/demo/crop",
                        dataType: "json",
                        data:{
                            imgurl:that.opt.imgurl,
                            w:data.w,
                            h:data.h,
                            x:data.x,
                            y:data.y,
                            dst_x:''
                        },
                        success:function(data){
                            if(data.status == true){
                                $('#element_id').attr({src:data.data.prefix+data.data.imgurl+'?v='+Math.random()});
                                $('.jcrop-preview').attr({src:data.data.prefix+data.data.imgurl+'?v='+Math.random()});
                                that.opt.img=data.data.prefix+data.data.imgurl;
                                that.opt.imgurl=data.data.imgurl;
                                if(that.opt.jcrop_api){
                                    that.opt.jcrop_api.setImage(data.data.prefix+data.data.imgurl+'?v='+Math.random());
                                }
                            }else {
                                that.alertInformation({type:false,msg:data.error});
                            }
                        },
                        error:function(data){
                            that.alertInformation({type:false,msg:data});
                        }
                    })
                }
            })
        },
        validate: function(){
            var that=this;
            that.opt.validate = $('#fm').validate({
                rules: {
                    bgcolor:{
                        required:true
                    },
                    linkurl:{
                        required:true
                    },
                    description: {
                        required:true
                    },
                    startTime: {
                        required:true
                    },
                    endTime:{
                        required:true
                    }

                },
                messages: {
                    bgcolor:{
                        required:'请输入一个颜色值'
                    },
                    linkurl:{
                        required:'请输入一个链接地址'
                    },
                    description:{
                        required:'请输入描述文字'
                    }
                },
                submitHandler:function() {
                    if(that.opt.imgurl == "" || that.opt.imgurl == null || that.opt.imgurl == undefined){
                        that.alertInformation({type:false,msg:'图片不能为空,请上传图片'});
                        return;
                    }
                    
                    if(that.opt.startTimeValue== 1){

                    }else if(that.opt.startTimeValue == 2){
                        if($('.dlg-start-time .easyui-datetimebox').datebox('getValue')=='' || $('.dlg-start-time .easyui-datetimebox').datebox('getValue') == undefined || $('.dlg-start-time .easyui-datetimebox').datebox('getValue')==null){
         
                            that.alertInformation({type:false,msg:'发布时间不能为空'});
                            return;
                        }
                    }else {
                        that.alertInformation({type:false,msg:'请选择发布时间类型'});
                        return;
                    } 
                    
                    if(that.opt.endTimeValue== 1){

                    }else if(that.opt.endTimeValue == 2){
                        if($('.dlg-end-time .easyui-datetimebox').datebox('getValue')=='' || $('.dlg-end-time .easyui-datetimebox').datebox('getValue') == undefined || $('.dlg-end-time .easyui-datetimebox').datebox('getValue')==null){

                            that.alertInformation({type:false,msg:'结束时间不能为空'});
                            return;
                        }
                    }else {
                        that.alertInformation({type:false,msg:'请选择结束时间类型'});
                        return;
                    }

                    var d1 = $.fn.datebox.defaults.parser($('.dlg-start-time .easyui-datetimebox').datebox('getValue')); 
                    var d2 = $.fn.datebox.defaults.parser($('.dlg-end-time .easyui-datetimebox').datebox('getValue')); 
                    if((d1 != '' || d1 != undefined || d1 != null) && (d2 != '' || d2 != undefined || d2 != null)){
                        if(d1>d2){
                            that.alertInformation({type:false,msg:'发布时间不能大于结束时间'});
                            return;
                        }
                    }
                   
                    var imgData = {
                        title: '',
                        firstDesc:$('#description').val(),
                        imgUrl:that.opt.imgurl,
                        linkUrl:$('#linkurl').val(),
                        order:$('#order').val(),
                        startTime:$('.dlg-start-time .easyui-datetimebox').datebox('getValue'),
                        endTime:$('.dlg-end-time .easyui-datetimebox').datebox('getValue'),
                        confId:that.opt.urlType == 'edit'? that.opt.editDataId:'',
                        isDefault: 0,    //是否为默认
                    };
                    var urlType = that.opt.urlType == 'edit' ? '/admin/module/editModuleData':"/admin/module/addModuleData";
                    var id = that.opt.urlType == 'edit' ? that.opt.editDataIndex:that.opt.moduleId;
                    $.ajax({
                        type: "post",
                        url: urlType,
                        dataType: "json",
                        data:{
                            moduleId:id,
                            data: imgData
                        },
                        success:function(data){
                            if(data.status==1){
                                $('#dlg').window('close');        // close the dialog
                                $('#tt').datagrid('loading');
                                that.getDataAction();   //获取表格数据
                                that.alertInformation({type:true,msg:'操作成功'});
                                return;
                            }else {
                                that.alertInformation({type:false,msg:data.message});
                                return;
                            }
                        },
                        error:function(data){
                            that.alertInformation({type:false,msg:data.message});
                            return;
                        }
                    })
                    return false; //此处必须返回false，阻止常规的form提交
                },
            });
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
                clearTimeout(window.alertWinTime);
                $('#alert-tishi').remove();
                domAppend();
            }else {
                domAppend();
            }
        },
        setData:function(data){
            var that=this;
            data=data?data:'';
            linkUrl=data.linkUrl?data.linkUrl:'';
            description=data.firstDesc?data.firstDesc:'';
            addTime=data.startTime?data.startTime:'';
            endTime=data.endTime?data.endTime:'';
            prefix=data.prefix?data.prefix:'';
            imgUrl=data.imgUrl?data.imgUrl:'';
            var order=data.order?data.order:1;
            $('#order').numberspinner('setValue', order);
            $('#searchName').val('');
            that.opt.validate.resetForm();
            that.opt.urlType=null;
            $('.error').removeClass('error');
            $('#linkurl').val(linkUrl);
            $('.dlg-link-box a').html(linkUrl);
            $('.dlg-link-box a').attr({href:linkUrl});
            $('#description').val(description);
            if(addTime){
                that.opt.startTimeValue = 2;
                $('#dlg-start-time').combobox('select', '定时发布');
                $('.dlg-start-time .easyui-datetimebox').datetimebox({disabled:false});
                $('.dlg-start-time .easyui-datetimebox').datetimebox('setValue',addTime);
            }else {
                that.opt.startTimeValue = 1;
                $('#dlg-start-time').combobox('select', '直接发布');
                $('.dlg-start-time .easyui-datetimebox').datetimebox('setValue',that.getNowFormatDate());
            }
            if(endTime){
                that.opt.endTimeValue = 2;
                $('#dlg-end-time').combobox('select', '定时结束');
                $('.dlg-end-time .easyui-datetimebox').datetimebox({disabled:false});
                $('.dlg-end-time .easyui-datetimebox').datetimebox('setValue',endTime);
            }else {
                that.opt.endTimeValue = 1;
                $('#dlg-end-time').combobox('select', '手动结束');
            }

            $('#element_id').attr({src:prefix+imgUrl});
            $('.jcrop-preview').attr({src:prefix+imgUrl});
            that.opt.img=prefix+imgUrl;
            that.opt.imgurl=imgUrl;
            if(that.opt.jcrop_api){
                that.opt.jcrop_api.setImage(prefix+imgUrl);
            }
        }
    };
    banner.run();
    return window.banner;
})(window)