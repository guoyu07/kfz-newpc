<?php

//基本url
define('BASE_URL', '/');
//根目录
define('ROOT_DIR', APP_PATH . "/webroot/");
//layout模板目录
defined('LAYOUT_DIR') || define('LAYOUT_DIR', APP_PATH . "/application/layout/");
//语言包目录
defined('LANG_DIR') || define('LANG_DIR', APP_PATH . "/application/lang/");

/** 内存 */
defined('MEMORY_ON') || define('MEMORY_ON', function_exists('memory_get_usage'));

// 异常日志文件  每个月新建一次异常文件
define('DATA_LOG', '/data/logs/scripts/');

//验证条件
/** 存在字段就验证（默认） */
define('VALIDATE_EXISTS', 0);
/** 必须验证 */
define('VALIDATE_MUST', 1);
/** 值不为空的时候验证 */
define('VALIDATE_VALUE', 2);

//触发验证事件（可选）
/** 新增数据时候验证 */
define('MODEL_INSERT', 1);
/** 编辑数据时候验证 */
define('MODEL_UPDATE', 2);
/** 全部情况下验证（默认） */
define('MODEL_BOTH', 3);

//异常定义
define('UNDEFINED_ERR', 0);
//Yaf常量定义，错误代码常量 5xx Yaf框架定义的
/*
  常量(启用命名空间后的常量名)
  ----------------------------------------------------------------------------------------------------------
  YAF_VERSION(Yaf\VERSION)                                  Yaf框架的三位版本信息
  YAF_ENVIRON(Yaf\ENVIRON)                                  Yaf的环境常量, 指明了要读取的配置的节, 默认的是product
  YAF_ERR_STARTUP_FAILED(Yaf\ERR\STARTUP_FAILED)            Yaf的错误代码常量, 表示启动失败, 值为512
  YAF_ERR_ROUTE_FAILED(Yaf\ERR\ROUTE_FAILED)                Yaf的错误代码常量, 表示路由失败, 值为513
  YAF_ERR_DISPATCH_FAILED(Yaf\ERR\DISPATCH_FAILED)          Yaf的错误代码常量, 表示分发失败, 值为514
  YAF_ERR_NOTFOUND_MODULE(Yaf\ERR\NOTFOUD\MODULE)           Yaf的错误代码常量, 表示找不到指定的模块, 值为515
  YAF_ERR_NOTFOUND_CONTROLLER(Yaf\ERR\NOTFOUD\CONTROLLER)   Yaf的错误代码常量, 表示找不到指定的Controller, 值为516
  YAF_ERR_NOTFOUND_ACTION(Yaf\ERR\NOTFOUD\ACTION)           Yaf的错误代码常量, 表示找不到指定的Action, 值为517
  YAF_ERR_NOTFOUND_VIEW(Yaf\ERR\NOTFOUD\VIEW)               Yaf的错误代码常量, 表示找不到指定的视图文件, 值为518
  YAF_ERR_CALL_FAILED(Yaf\ERR\CALL_FAILED)                  Yaf的错误代码常量, 表示调用失败, 值为519
  YAF_ERR_AUTOLOAD_FAILED(Yaf\ERR\AUTOLOAD_FAILED)          Yaf的错误代码常量, 表示自动加载类失败, 值为520
  YAF_ERR_TYPE_ERROR(Yaf\ERR\TYPE_ERROR)                    Yaf的错误代码常量, 表示关键逻辑的参数错误, 值为521
 */

//代码错误导致的异常 2xx
define('CLASS_NOT_FOUND', 200);
define('METHOD_NOT_FOUND', 201);
define('FUNCTION_NOT_FOUND', 203);
define('TEMPLATE_NOT_FOUND', 204);
define('FILE_NOT_FOUND', 205);
define('PARAMETER_ERROR', 206);


//请求不合法异常 3xx

//缓存异常  4xx

//接口异常  6xx

//数据库异常 7xx

//写文件异常 8xx
