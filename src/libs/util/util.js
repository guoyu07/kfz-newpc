;(function($)
{
    $.extend(
    {
    	kfzUtil : {
			/**
			 * 日期工具函数
			 */
    		date : {
    			/**
		         * 兼容日期对象参数、时间戳参数
				 * @param {Object|Number} date 日期对象参数或时间戳参数
				 * @returns {Object} 返回对应的日期对象
		         */
		    	paramsDeBug: function(date){
		    		date = date == undefined ? new Date() : date;
		            date = typeof date == 'number' ? new Date(date) : date;
		            return date;
		    	},
		        /**
		         * 将日期格式化成指定格式的字符串
		         * @param {Object|Number} date 要格式化的日期，不传时默认当前时间，也可以是一个时间戳
		         * @param {String} fmt 目标字符串格式，支持的字符有：y、M、d、q、w、H、h、m、s、S、默认：yyyy-MM-dd HH:mm:ss
		         * @returns {String} 返回格式化后的日期字符串
		         */
		        formatDate: function(date, fmt){
		            fmt = fmt || 'yyyy-MM-dd HH:mm:ss';
		            date = this.paramsDeBug(date);
		            var obj =
		            {
		                'y': date.getFullYear(), // 年份，注意必须用getFullYear
		                'M': date.getMonth() + 1, // 月份，注意是从0-11
		                'd': date.getDate(), // 日期
		                'q': Math.floor((date.getMonth() + 3) / 3), // 季度
		                'w': date.getDay(), // 星期，注意是0-6
		                'H': date.getHours(), // 24小时制
		                'h': date.getHours() % 12 == 0 ? 12 : date.getHours() % 12, // 12小时制
		                'm': date.getMinutes(), // 分钟
		                's': date.getSeconds(), // 秒
		                'S': date.getMilliseconds() // 毫秒
		            };
		            var week = ['天', '一', '二', '三', '四', '五', '六'];
		            for(var i in obj)
		            {
		                fmt = fmt.replace(new RegExp(i+'+', 'g'), function(m)
		                {
		                    var val = obj[i] + '';
		                    if(i == 'w') return (m.length > 2 ? '星期' : '周') + week[val];
		                    for(var j = 0, len = val.length; j < m.length - len; j++) val = '0' + val;
		                    return m.length == 1 ? val : val.substring(val.length - m.length);
		                });
		            }
		            return fmt;
		        },
		        /**
		         * 将字符串解析成日期
		         * @param {String} str 输入的日期字符串，如'2017-01-12'
		         * @param {String} fmt 字符串格式，默认'yyyy-MM-dd'，支持如下：y、M、d、H、m、s、S，不支持w和q
		         * @returns {Object} 解析后的Date类型日期
		         */
		        parseDate: function(str, fmt){
		            fmt = fmt || 'yyyy-MM-dd HH:mm:ss';
		            var obj = {y: 0, M: 1, d: 1, H: 0, h: 0, m: 0, s: 0, S: 0};
		            fmt.replace(/([^yMdHmsS]*?)(([yMdHmsS])\3*)([^yMdHmsS]*?)/g, function(m, $1, $2, $3, $4, idx, old)
		            {
		                str = str.replace(new RegExp($1+'(\\d{'+$2.length+'})'+$4), function(_m, _$1)
		                {
		                    obj[$3] = parseInt(_$1);
		                    return '';
		                });
		                return '';
		            });
		            obj.M--; // 月份是从0开始的，所以要减去1
		            var date = new Date(obj.y, obj.M, obj.d, obj.H, obj.m, obj.s);
		            if(obj.S !== 0) date.setMilliseconds(obj.S); // 如果设置了毫秒
		            return date;
		        },
				/**
		         * 根据 '当前时间' 距 '指定时间' 的 '时间间隔' 、将 '指定时间' 格式化成 '友好格式'
				 * 距指定的时间1分钟以内的返回'刚刚',当天的返回时分,当年的返回月日,否则,返回年月日
		         * @param {Object|Number} date 指定的时间、默认为当前日期对象
		         * @param {Object|Number} now 当前服务器时间、默认为当前日期对象
		         * @returns {String} 返回友好格式的目标时间段
		         */
				formatDateToFriendly: function(date, now){
					date = this.paramsDeBug(date);
					now = this.paramsDeBug(now);
					if((now.getTime() - date.getTime()) < 60*1000) return '刚刚';
					var temp = this.formatDate(date, 'yyyy年M月d日');
					if(temp == this.formatDate(now, 'yyyy年M月d日')) return this.formatDate(date, 'HH:mm');
					if(date.getFullYear() == now.getFullYear()) return this.formatDate(date, 'M月d日');
					return temp;
				},
		        /**
		         * 将一段时长转换成友好格式
		         * @param {number} second 需要格式化的秒数
		         * @returns {String} 返回友好格式的时长
		         */
		        formatDateToSecond: function(second){
		            if(second < 60) return second + '秒';
		            else if(second < 60*60) return (second-second%60)/60+'分'+second%60+'秒';
		            else if(second < 60*60*24) return (second-second%3600)/60/60+'小时'+Math.round(second%3600/60)+'分';
		            return (second/60/60/24).toFixed(1)+'天';
		        },
		        /** 
		         * 将一段时长转换成MM:SS形式
				 * @param {number} second 需要转换的秒数
				 * @returns {String} 返回转换后的时长
		         */
		        formatTimeToFriendly: function(second){
		            var m = Math.floor(second / 60);
		            m = m < 10 ? ( '0' + m ) : m;
		            var s = second % 60;
		            s = s < 10 ? ( '0' + s ) : s;
		            return m + ':' + s;
		        },
		        /**
		         * 判断某一年是否是闰年
		         * @param {Object|Number} year 目标年份或日期对象、默认为当前日期对象
				 * @returns {true|false} 返回Boolean
		         */
		        isLeapYear: function(year){
		            if(year === undefined) year = new Date();
		            if(year instanceof Date) year = year.getFullYear();
		            return (year % 4 == 0 && year % 100 != 0) || (year % 400 == 0);
		        },
		        /**
		         * 获取指定年月的总天数
				 * 支持三种传参方式
				 * 方式一: getMonthDays();
		         * 方式二: getMonthDays(new Date());
		         * 方式三: getMonthDays(2017, 1);
		         * @param {Object|Number} date 目标年份 
		         * @param {Object|Number} month 目标月份
				 * @returns {true|false} 返回目标年月的总天数
		         */
		        getMonthDays: function(date, month){
		            var y, m;
		            if(date == undefined) date = new Date();
		            if(date instanceof Date)
		            {
		                y = date.getFullYear();
		                m = date.getMonth();
		            }
		            else if(typeof date == 'number')
		            {
		                y = date;
		                m = month-1;
		            }
		            var days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]; // 非闰年的一年中每个月份的天数
		            //如果是闰年并且是2月
		            if(m == 1 && this.isLeapYear(y)) return days[m]+1;
		            return days[m];
		        },
				/**
		         * 计算两个时间点的差值
		         * @param {Object|Number} date1 要计算的日期对象或时间戳-1
		         * @param {Object|Number} date2 要计算的日期对象或时间戳-2
		         * @returns {Number} 返回两个时间点的差值
		         */
				getDifference: function(date1, date2){
					data1 = this.paramsDeBug(date1);
		            date2 = this.paramsDeBug(date2);
					return Math.abs(data1.getTime() - date2.getTime());
				}
    		},
			/**
			 * url解析
			 */
			url : {
				/** 
				 * 解析URL
				 * 该函数在没有传递参数的情况下默认解析的是当前URL
				 * @param {string} url 完整的URL地址、若URL没有带上协议、端口号,那么函数解析的URL将默认在首部加上当前URL 
				 * @returns {object} 返回解析后的url对象 
				 * 解析后的url对象
				 * source       {String}      当前URL
				 * protocol     {String}      协议
				 * host         {String}      域名
				 * port         {String}      端口号
				 * path         {String}      路径
				 * file         {String}      文件名(路径的最后一部分)
				 * query        {String}      查询部分(?后面的部分)
				 * hash         {String}      哈希(#后面的部分)
				 * relative     {String}      端口号后面的部分(路径+查询部分+哈希)
				 * segments     {String[]}    路径解析
				 * params       {Object}      解析后的查询部分(俗称URL参数)
				 */  
				parseURL : function (url) {  
					url = url || location.href;
					var a =  document.createElement('a');  
					a.href = url;  
					return {  
						source: url,  
						protocol: a.protocol.replace(':',''),  
						host: a.hostname,
						port: a.port,  
						query: a.search, 
						hash: a.hash.replace('#',''),  
						path: a.pathname.replace(/^([^\/])/,'/$1'), 
						file: (a.pathname.match(/\/([^\/?#]+)$/i) || [,''])[1],  
						relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [,''])[1],  
						segments: a.pathname.replace(/^\//,'').split('/'),   
						params: (function(){  
							var ret = {},  
								seg = a.search.replace(/^\?/,'').split('&'),  
								len = seg.length, i = 0, s;  
							for (;i<len;i++) {  
								if (!seg[i]) { continue; }  
								s = seg[i].split('=');  
								ret[s[0]] = s[1];  
							}  
							return ret;  
						})()
					};  
				}   
			},
			/**
			 * unicode编码解码
			 */
			unicode : {
				/**
				 * unicode编码
				 * @param {string} str 将要进行编码的字符串
				 * @returns {string} 返回unicode编码后的结果
				 */
				charToUnicode : function(str) {
					if (!str) return '';
					var unicode = '', i = 0, len = (str = '' + str).length;
					for (; i < len; i ++) {
						unicode += 'k' + str.charCodeAt(i).toString(16).toLowerCase();
					}
					return unicode;
				},
				/**
				 * unicode解码
				 * @param {string} str 将要进行解码的字符串
				 * @returns {string} 返回unicode解码后的结果
				 */
				unicodeToChar : function(unicode) {
					if (typeof unicode === 'undefined') return '';
					var str = '', arr = unicode.split('k'), i = 0, len = arr.length;
					for (; i < len; i ++) {
						var oneUnicode = arr[i], oneStr;
						if (!oneUnicode) continue;
						oneUnicode = parseInt(oneUnicode, 16).toString(10);
						oneStr = String.fromCharCode(oneUnicode);
						str += oneStr;
					}
					return str;
				}
			},
			/**
			 * cookies操作
			 * 支持三种传参方式
			 * @param {String} name cookies名称
			 * @param {String} value cookies值
			 * value === 'null'                                 该函数将删除名称为name的cookie
			 * value === 'undefined'                            该函数将返回名称为name的cookie值
			 * value != 'undefined' && value != 'null'          该函数将设置一个名为name的cookie
			 * @param {Object} options cookies设置
			 */
			cookies : function(name, value, options){
				if (typeof value != 'undefined') {
					options || (options = {});
					if (value === null) {
						value = '';
						options.expires = -1;
					}
					var expires = '';
					if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
						var date;
						if (typeof options.expires == 'number') {
							date = new Date();
							date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
						} else {
							date = options.expires;
						}
						expires = '; expires=' + date.toUTCString();
					}
					var path = options.path ? '; path=' + options.path : '';
					var domain = options.domain ? '; domain=' + options.domain : '';
					var secure = options.secure ? '; secure' : '';
					document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
				} else {
					var cookieValue = null;
					if (document.cookie && document.cookie != '') {
						var c_start = document.cookie.indexOf(name + "=");
						if (c_start != -1) { 
							c_start = c_start + name.length + 1;
							var c_end = document.cookie.indexOf(";", c_start);
							if (c_end == -1) c_end = document.cookie.length;
							cookieValue = decodeURIComponent(document.cookie.substring(c_start,c_end));
						} 
					}
					return cookieValue;
				}
			},
			/**
			 * 字节处理
			 */
			bytes : {
				/**
				 * 字节格式化
				 * 1024B      ==      1KB
				 * 1024KB     ==      1MB
				 * 1024MB     ==      1GB
				 * 1024GB     ==      1TB
				 * 1024TB     ==      1PB
				 * 1024PB     ==      1EB
				 * 1024EB     ==      1ZB
				 * 1024ZB     ==      1YB
				 * @param {Number} bytes 总字节数
				 * @returns {String} 返回格式化后的字节单位、
				 */
				format : function(bytes) {
					if (bytes === 0) return '0 B';
					var unit = 1024,
						sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
						i = Math.floor(Math.log(bytes) / Math.log(unit));
					return (bytes / Math.pow(unit, i)).toPrecision(3) + ' ' + sizes[i];
				},
				/**
				 * 文本转字节
				 * @param {String} text 将进行转换的文本
				 * @returns {Number} 返回转换的字节数
				 */
				textToBytes : function(text) {
					// return text.replace(/[^\x00-\xff]/g, '**').length;
					var byteLen = 0,len = text.length;
					for(var i=0; i<len; i++){
						if(text.charCodeAt(i)>255){
							byteLen += 2;
						}else{
							byteLen++;
						}
					}
					return byteLen;
				}
			},
			/**
			 * 浏览器信息
			 * @returns {String} 返回浏览器信息
			 * {Number}
			 */
			browserInfo : function(){
				var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串  
				var isOpera = userAgent.indexOf("Opera") > -1; //判断是否Opera浏览器  
				var isIE = userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera; //判断是否IE浏览器  
				var isEdge = userAgent.indexOf("Windows NT 6.1; Trident/7.0;") > -1 && !isIE; //判断是否IE的Edge浏览器  
				var isFF = userAgent.indexOf("Firefox") > -1; //判断是否Firefox浏览器  
				var isSafari = userAgent.indexOf("Safari") > -1 && userAgent.indexOf("Chrome") == -1; //判断是否Safari浏览器  
				var isChrome = userAgent.indexOf("Chrome") > -1 && userAgent.indexOf("Safari") > -1; //判断Chrome浏览器  
     			 if (isIE){  
           			var reIE = new RegExp("MSIE (\\d+\\.\\d+);");  
           			reIE.test(userAgent);  
           			var fIEVersion = parseFloat(RegExp["$1"]);  
           			if(fIEVersion == 7){ 
						   return "IE7";
					}else if(fIEVersion == 8){ 
						return "IE8";
					}else if(fIEVersion == 9){ 
						return "IE9";
					}else if(fIEVersion == 10){ 
						return "IE10";
					}else if(fIEVersion == 11){ 
						return "IE11";
					}else{ return "0"}//IE版本过低  
       			}
				if (isFF) {  return "FF";}  
				if (isOpera) {  return "Opera";}  
				if (isSafari) {  return "Safari";}  
				if (isChrome) { return "Chrome";}  
				if (isEdge) { return "Edge";}  
			},
			/**
			 * 图片加载失败处理
			 * @param {String} src 图片加载失败时替换的图片地址
			 */
			imgError : function(src){
				$("img").error(function(){
					$(this).attr('src',src);
				});
			},
			/**
			 * 队列
			 * 实现一个优先队列
			 */
			priorityQueue : function () {
    			var items = [];
    			function QueueElement (element, priority){
        			this.element = element;
        			this.priority = priority;
    			}
    			this.enqueue = function(element, priority){
					var queueElement = new QueueElement(element, priority);
					var added = false;
					for (var i=0; i< items.length; i++){
						if ((items[i].priority == undefined && queueElement.priority != undefined) || (items[i].priority != undefined && queueElement.priority < items[i].priority)){
							items.splice(i,0,queueElement);
							added = true;
							break;
						}
					}
					if (!added){
						items.push(queueElement); 
					}
				};
				this.dequeue = function(){
					return items.shift();
				};
				this.front = function(){
					return items[0];
				};
				this.isEmpty = function(){
					return items.length == 0;
				};
				this.size = function(){
					return items.length;
				};
				this.print = function(){
					return items;
				};
			}
    	}
    });
})(jQuery);