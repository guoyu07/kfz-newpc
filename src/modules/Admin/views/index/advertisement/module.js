;(function(window){
    window.module = {
        opt: {
            hash:null,
            moduleId: $('#moduleId').val(),     //模块id
            order: {
                order: 'ASC',
                updateTime: 'DESC'
            },
            urlType:null,
            editDataIndex:null,
            editDataId:null,
            validate:null,      //表单验证
        },
        run: function(){
            var that=this;
            that.hash=(!window.location.hash)?"0":window.location.hash.split('=')[1];
            that.navPill(this.hash);
            that.datagrid();
            that.getData();
            that.validate();        //表单验证
        },
        navPill: function(hash){
            var that=this;
            $('#navPill-tab li').removeClass('active');
            $('#navPill-tab li').eq(hash).addClass('active');
            $('.tab-box li').removeClass('show');
            $('.tab-box li').eq(hash).addClass('show');
            $('#navPill-tab li').on('click',function(){
                var index=$(this).index();
                $('#navPill-tab li').removeClass('active');
                $('#navPill-tab li').eq(index).addClass('active');
                $('.tab-box li').removeClass('show');
                $('.tab-box li').eq(index).addClass('show');
                if(index==0){
                    that.datagrid('reload');
                }
            })
        },
        getData: function(){
            var that=this;
            $('#tt').datagrid('loading');
            $.ajax({
                type: "post",
                url: "/admin/module/getData",
                dataType: "json",
                data:{
                    moduleId:that.opt.moduleId,
                    params:{
                        isDefault: 1,    //是否为默认
                        order:that.opt.order,
                        'page': {
                            'maxRowPerPage': '',
                            'requirePage': ''  
                        },
                        'status': [''],
                        'regularlyStart': [''],
                        'regularlyEnd': [''],
                        'keyWords': '',  
                    }
                },
                success: function(data){
                    if(data.status==1){
                        that.opt.data=data;
                        $('#tt').datagrid('loadData',that.opt.data.data);    // reload the user data
                        $('#tt').datagrid('loaded');
                        that.datagrid('reload');
                        return;
                    }else {
                        $('#tt').datagrid('loaded');
                        that.alertInformation({type:false,msg:data.message});
                    }
                }
            })
        },
        datagrid: function(){
            var that=this;
            $('#tt').datagrid({
                autoRowHeight:true,
                singleSelect:true,
                nowrap: false,//数据长度超出列宽时将会自动截取
                toolbar: "#toolbar",
                columns:[[
                    {field: 'order', title: '排序', width: 50, align: 'center'},
                    {field: 'title', title: '广告标题', width: 160, align: 'center'},
                    {field: 'firstDesc', title: '广告描述1', width: 200, align: 'center'},
                    {field: 'secondDesc', title: '广告描述2', width: 200, align: 'center'},
                    {field: 'updateTime', title: '最后操作时间', width: 90, align: 'center'},
                    {field: 'confId', title: '操作', width: 150, align: 'center',formatter:function(value,row,index){
                        return "<a href='javascript:;' name='edit' style='color: #428bca;' onclick='module.editData("+value+","+row.moduleId+","+index+")'></a>" +
                            '<a href="javascript:;" name="remove" style="color: #428bca;" onclick="module.removeData('+value+','+row.moduleId+')"></a>'
                    }}
                ]],
                onLoadSuccess:function(data){
                    $("a[name='edit']").linkbutton({text:'修改',plain:true});
                    $("a[name='remove']").linkbutton({text:'删除',plain:true});
                },
                onClickRow: function (rowIndex, rowData) {
                    $(this).datagrid('unselectRow', rowIndex);
                },
            })
        },
        editData:function(dataId,id,index){
            var that=this;
            that.setData(that.opt.data.data[index]);
            $('#dlg').window('open');
            $("#dlg").panel("move",{top:$(document).scrollTop() + ($(window).height()-366) * 0.5,width:800});
            that.opt.urlType='edit';
            that.opt.editDataIndex=id;
            that.opt.editDataId=dataId;
        },
        cancle: function(){
            var that=this;
            $('#dlg').window('close');
            that.setData();
        },
        addData:function(){
            var that=this;
            $('#dlg').window('open');
            that.setData();
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
                                that.getData();   //获取表格数据
                                that.alertInformation({type:true,msg:'删除成功'});
                                return false;
                            }else {
                                that.alertInformation({type:false,msg:data.message});
                            }
                        }
                    })
                }
            });
        },
        linkurl:function(){
            $('.dlg-link-box a').html($('#linkurl').val());
            $('.dlg-link-box a').attr({href:$('#linkurl').val()});
        },
        validate: function(){
            var that=this;
            that.opt.validate = $('#fm').validate({
                rules: {
                    title:{
                        required:true
                    },
                    firstDesc:{
                        required:true
                    },
                    secondDesc:{
                        required:true
                    },
                    linkurl:{
                        required:true
                    }
                },
                messages: {
                    title:{
                        required:'标题不能为空'
                    },
                    firstDesc:{
                        required:'描述不能为空'
                    },
                    secondDesc:{
                        required:'描述不能为空'
                    },
                    bgcolor:{
                        required:'请输入一个颜色值'
                    },
                    linkurl:{
                        required:'请输入一个链接地址'
                    }
                },
                submitHandler:function() {
                    var imgData = {
                        title: $('#title').val(),
                        firstDesc: $('#firstDesc').val(),
                        secondDesc: $('#secondDesc').val(),
                        linkUrl:$('#linkurl').val(),
                        order:$('#order').val(),
                        confId:that.opt.urlType == 'edit'? that.opt.editDataId:'',
                        isDefault: 1,    //是否为默认
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
                                that.getData();   //获取表格数据
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
            title=data.title?data.title:'';
            firstDesc=data.firstDesc?data.firstDesc:'';
            secondDesc=data.secondDesc?data.secondDesc:'';
            linkUrl=data.linkUrl?data.linkUrl:'';
            that.opt.validate.resetForm();
            that.opt.urlType=null;
            var order=data.order?data.order:1;
            $('#order').numberspinner('setValue', order);
            $('.error').removeClass('error');
            $('#title').val(title);
            $('#firstDesc').val(firstDesc);
            $('#secondDesc').val(secondDesc);
            $('#linkurl').val(linkUrl);
            $('.dlg-link-box a').html(linkUrl);
            $('.dlg-link-box a').attr({href:linkUrl});
        }
    };
    module.run();
})(window)