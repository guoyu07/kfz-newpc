# category
> 作者：陈辉
> 贡献者：[点击查看详细信息](#version)
> 版本信息：[点击查看详细信息](#version)
> 类型：PHP模块
> 依赖模块：无
> 依赖插件：[perfect-scrollbar](https://github.com/noraesae/perfect-scrollbar)自定义滚动条插件
> 依赖雪碧图：[category]()
> 依赖样式表：[normalize.css]()   [base.css]()    [perfect-scrollbar.css]()
> 文档：[API](#api)

使用方法
```
/**
* 以默认配置启动分类模块
**/
categroy.run()

/**
* 配置并启动分类模块
**/
category.config({id: 'category',top: 194,left: 200,ad: false,scroll: true}).run()
```

# version
| 版本 | 备注 | 贡献者 | 
| :--: | :--: | :--: | 
| 1.0.0 | 分类模块构建 | 陈辉 |

# API
| 属性&方法 | 类型 | 简介 | 
| :--: | :--: | :--: | 
| [status](#api-status) | 属性 | 纪录模块执行状态 |
| [log](#api-log) | 属性 | 纪录模块执行日志 |
| [configParams](#api-configParams) | 属性 | 模块的默认配置信息 |
| [elemt](#api-elemt) | 属性 | 存放模块需要操作的dom元素 |
| [logRun](#api-logRun) | 方法 | 执行此方法可在控制台显示模块执行日志 |
| [config](#api-config) | 方法 | 执行此方法可修改模块配置 |
| [scrollApply](#api-scrollApply) | 方法 | 执行此方法可修改模块滚动条部分状态 |
| [run](#api-run) | 方法 | 执行此方法可激活模块 |
| [setLog](#api-setLog) | 方法 | 模块内置方法（不建议外部使用） |
| [itemListen](#api-itemListen) | 方法 | 模块内置方法（不建议外部使用） |

# API-status
```
categroy.status    //供debug使用    略 
```

# API-log
```
categroy.log      //供debug使用    存放模块执行日志的数组
/**
* 以下是默认log信息
* @executor    执行者
* @msg         状态信息
* @status      控制台显示类型  debug||error||.....
* @time        写入log时间
* @title       log组标题
**/
{
    executor:"category"，
    msg:"模块启动成功"，
    status:"debug"，
    time:"11:04:43 GMT+0800 (中国标准时间)"，
    title:"分类模块启动过程"
}
```

# API-configParams
```
categroy.configParams    //供模块内部使用    存放模块的配置信息
/**
* 以下是默认配置信息
* @id      模块id
* @top     模块定位-top
* @left    模块定位-left
* @ad      模块广告栏状态 true:显示 false:隐藏
* @scroll  模块滚动条部分状态 true:显示 false:隐藏
**/
{
    id: 'category',
    top: 0,
    left: 0,
    ad: true,
    scroll: true
}
```

# API-elemt
```
categroy.elemt      //供模块内部使用    存放模块要操作的dom元素 避免重复获取
/**
* 以下是要操作的模块元素
**/
{
    categoryBox: $('.cagetory-box'),
    scrollBox: $('.block-other')
}
```

# API-logRun
```
categroy.logRun()      //供debug使用    在控制台显示模块执行日志 浏览器控制台请输入widgets["category/main/category"].logRun()
```

# API-config
| 参数 | 类型 | 是否必填 | 默认值 | 功能说明 | 
| :--: | :--: | :--: | :--: | :--: | 
| params | Object | 否 | [API-configParams](#api-configParams) | 修改模块配置 |

> ps：params示例参考[API-configParams](#api-configParams)

# API-scrollApply
| 参数 | 类型 | 是否必填 | 默认值 | 功能说明 | 
| :--: | :--: | :--: | :--: | :--: | 
| type | String | 否 | initialize | 修改模块滚动条部分状态 |
```
categroy.scrollApply()
```
> `initialize` 创建模块滚动条
> `update` 更新模块滚动条
> `destroy` 销毁模块滚动条

# API-run
```
/**
* 以默认配置启动分类模块
**/
categroy.run()

/**
* 配置并启动分类模块
**/
category.config({id: 'category',top: 194,left: 200,ad: false,scroll: true}).run()