(function (root, factory) {
    var id = 'category/main/category';
    //项目所有自定义模块都放在widgets对象下
    root.widgets || (root.widgets = {});
    if (typeof define === 'function' && define.amd) {
        // AMD. 注册匿名模块
        //如果不需要将此模块暴露在widgets下，可使用如下写法
        //define('widgets/' + id, ['libs/jQuery'],factory);
        //如果想要将此模块暴露在全局变量widgets下
        define('widgets/' + id, ['libs/jQuery'], function ($, _, Backbone) {
            //如果想要将此模块暴露在全局变量widgets下
            return (root.widgets[id] = factory($, _, Backbone));
        });
    } else {
        //如果不需要将此模块暴露在widgets下，可使用如下写法
        //factory(root.jQuery);
        //如果想要将此模块暴露在全局变量widgets下
        root.widgets[id] = factory(root.jQuery);
    }
}(this, function ($) {
	var category = {
		status: true,
		log: [],
		configParams: {//模块配置
			id: 'category',
			top: 0,
			left: 0,
			ad: true,
			scroll: true
		},
		elemt: {
			categoryBox: $('.cagetory-box'),
			scrollBox: $('.cagetory-box .other-info')
		},
		setLog: function(params){
			var d = new Date(),
				t = d.toTimeString();
			var item = {
				status: params.status,
				title: params.title,
				msg: params.msg,
				executor: params.executor,
				time: t
			}
			this.log.push(item);
		},
		logRun: function(){
			if(window.console){
				$.each(this.log,function(index,item){
					console.group('分类模块日志：' + item.title);
					console.debug('时间：' + item.time);
					console.debug('执行者：' + item.executor);
					console[item.status]('状态：' + item.msg);
					console.groupEnd();
				});
			}
		},
		config: function(params){
			var that = this;
			$.each(params,function(key,value){
				that.configParams[key] = value;
			});
			if(this.configParams.top == 0){
				this.setLog({title: '分类模块配置过程',msg: '未配置模块定位信息-top',executor: 'category',status: 'info'});
			}else{
				this.elemt.categoryBox.css('top',this.configParams.top);
			}
			if(this.configParams.left == 0){
				this.setLog({title: '分类模块配置过程',msg: '未配置模块定位信息-left',executor: 'category',status: 'info'});
			}else{
				this.elemt.categoryBox.css('left',this.configParams.left);
			}
			this.elemt.categoryBox.attr('id',this.configParams.id);
			return this;
		},
		itemListen: function(){
			this.elemt.categoryBox.delegate( '.list-group-item', 'mouseover',function () {
				var d = $(this),
					flag = d.attr('init-detail');
				if(flag === 'wait'){
					var t = d.position().top + 1,
						h = d.parents('.list-group').height() + 1;
	                d.attr('init-detail','configured').find('.detail').css({top: -t,height: h});
				}
				d.addClass('active');
            }).delegate( '.list-group-item','mouseout', function () {
                $(this).removeClass('active');
            });
		},
		scrollApply: function(type){
			if(window.Ps){
				switch (type){
					case 'initialize':
						this.elemt.scrollBox.addClass('scroll');
						Ps.initialize(this.elemt.scrollBox[0]);
						break;
					case 'update':
						Ps.update(this.elemt.scrollBox[0]);
						break;
					case 'destroy':
						Ps.destroy(this.elemt.scrollBox[0]);
						break;
					default:
						this.elemt.scrollBox.addClass('scroll');
						Ps.initialize(this.elemt.scrollBox[0]);
						break;
				}
			}else{
				this.setLog({title: '设置滚动条过程',msg: 'perfect-scrollbar插件未加载，该插件用于自定义滚动条',executor: 'scrollApply',status: 'error'});
				this.status = false;
			}
		},
		run: function(){
			this.itemListen();//分类列表监听
			if(this.configParams.scroll){
				this.scrollApply();//设置滚动条
			}
			if(this.status){
				this.setLog({title: '分类模块启动过程',msg: '模块启动成功',executor: 'category',status: 'debug'});
			}
		}
	}
	// category.run();
    return category;
}));