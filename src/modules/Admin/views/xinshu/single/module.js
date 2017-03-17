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
            jcrop_api:null,     //裁剪图片
            validate:null,      //表单验证
            img:null,           //保存上传图片地址
            imgurl:null,        //图片地址
            tailorData:null,    //保存图片裁剪后的数据
            xsize: '200',
            ysize: '26.3',
        },
        run: function(){
            var that=this;
            that.hash=(!window.location.hash)?"0":window.location.hash.split('=')[1];
            that.navPill(this.hash);
            that.datagrid();
            that.getData();
            that.uploader();        //上传
            that.validate();        //表单验证
            that.tailor();          //提交裁剪后的图片
            // that.jcrop();           //图片裁剪
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
                    {field: 'imgUrl', title: '图片', width: 290, align: 'center',formatter:function(value,row,index){
                        return "<a href="+row.prefix+value+" target='_blank'>"+row.prefix+value+"</a>"
                    }},
                    {field: 'linkUrl', title: '点击图片跳转URL', width: 240, align: 'center',formatter:function(value,row,index){
                        return "<a href="+value+" target='_blank'>"+value+"</a>"
                    }},
                    {field: 'updateTime', title: '最后操作时间', width: 240, align: 'center'},
                    {field: 'confId', title: '操作', width: 100, align: 'center',formatter:function(value,row,index){
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
            that.jcrop();  
            that.setData(that.opt.data.data[index]);
            $('#dlg').window('open');
            $("#dlg").panel("move",{top:$(document).scrollTop() + ($(window).height()-724) * 0.5,width:800});
            that.opt.urlType='edit';
            that.opt.editDataIndex=id;
            that.opt.editDataId=dataId;
        },
        cancle: function(){
            var that=this;
            $('#dlg').window('close');
            that.opt.jcrop_api.destroy();
            that.setData();
        },
        addData:function(){
            var that=this;
            $('#dlg').window('open');
            that.jcrop();  
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
                    var imgData = {
                        title: '',
                        firstDesc:$('#description').val(),
                        imgUrl:that.opt.imgurl,
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
            linkUrl=data.linkUrl?data.linkUrl:'';
            description=data.firstDesc?data.firstDesc:'';
            prefix=data.prefix?data.prefix:'';
            imgUrl=data.imgUrl?data.imgUrl:'';
            $('#searchName').val('');
            that.opt.validate.resetForm();
            that.opt.urlType=null;
            var order=data.order?data.order:1;
            $('#order').numberspinner('setValue', order);
            $('.error').removeClass('error');
            $('#linkurl').val(linkUrl);
            $('.dlg-link-box a').html(linkUrl);
            $('.dlg-link-box a').attr({href:linkUrl});
            $('#description').val(description);
            $('#element_id').attr({src:prefix+imgUrl});
            $('.jcrop-preview').attr({src:prefix+imgUrl});
            that.opt.img=prefix+imgUrl;
            that.opt.imgurl=imgUrl;
            if(that.opt.jcrop_api){
                that.opt.jcrop_api.setImage(prefix+imgUrl);
            }
        }
    };
    module.run();
})(window)