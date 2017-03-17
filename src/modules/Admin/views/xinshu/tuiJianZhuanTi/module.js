;(function(window){
    window.module = {
        opt: {
            moduleId:$('#moduleId').val(),     //模块id
            data:null,
            editItem : {
                    moduleId : ''
            }
        },
        run: function(){
            var that = this;
            that.hash=(!window.location.hash)?"0":window.location.hash.split('=')[1];
            that.navPill(this.hash);
            that.getData();
            that.datagrid();
        },
        element: {
            form: $('#form-title-module'),
            table: $('#table-module-title'),
            win: $('#win-title-module'),
            titleText: $('#titleText'),
            subtitle:$('#subtitle'),
            moreUrl: $('#moreUrl')
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
            $.ajax({
                type: "post",
                url: "/admin/module/getModuleInfo",
                dataType: "json",
                data:{
                    moduleId:that.opt.moduleId,
                },
                success: function(data){
                    if(data.status==1){
                        that.opt.data=[data.data];
                        that.element.table.datagrid('loadData',that.opt.data);    // reload the user data
                        that.element.table.datagrid('loaded');
                        that.element.table.datagrid('reload');
                    }else {
                        that.alertInformation({type:false,msg:data.message});
                    }
                }
            })
        },
        datagrid: function(){
            var that = this;
            that.element.table.datagrid({
                autoRowHeight:true,
                nowrap: false,//数据长度超出列宽时将会自动截取
                columns: [[
                    {field: 'title', title: '标题', width: 200, align: 'center'},
                    {field: 'subtitle', title: '副标题', width: 200, align: 'center'},
                    {field: 'showMoreUrl', title: '更多url', width: 210, align: 'center',formatter:function(value,row,index){
                        return '<a href="' + value + '" target="_blank">' + value + '</a>'
                    }},
                    {field: 'btn', title: '操作', width: 100, align: 'center',formatter:function(value,row,index){
                        var btn = "<a href='javascript:;' name='edit' style='color: #428bca;' onclick='module.editItem(" + index + ")'></a>";
                        return btn;
                    }}
                ]],
                onLoadSuccess:function(data){
                    $("a[name='edit']").linkbutton({text:'修改',plain:true});
                },
                onClickRow: function (rowIndex, rowData) {
                    that.element.table.datagrid('unselectRow', rowIndex);
                }
            });
        },
        editItem: function(index){
                var that = this,
                data = that.opt.data[index];
                that.editItem.moduleId = data.moduleId;
                that.initFormData(data);
                that.element.win.window('open');
                that.validate();
        },
        initFormData: function(data){
            var that = this;
            that.element.titleText.val(data.title);
            that.element.subtitle.val(data.subtitle);
            that.element.moreUrl.val(data.showMoreUrl);
        },
        validate: function(){
            var that = this;
            that.element.form.validate({
                rules: {
                    titleText: "required",
                    subtitle: "required",
                    moreUrl: "url"
                },
                messages: {
                    titleText: "请输入标题",
                    subtitle: "请输入标题",
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
                            subtitle: that.element.subtitle.val() 
                        },
                        success:function(res){
                            if(res.status == 1){
                                that.element.win.window('close');
                                that.element.table.datagrid('loading');
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
        closeWin: function(){
            var that=this;
            that.element.win.window('close');
        }
    }
    module.run();
})(window)