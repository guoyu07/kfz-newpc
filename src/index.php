<?php

/**
 * 该文件定义应用的根目录，并初始化Yaf\Application对象
 * 主要实现一下几个操作：
 * 1、定义了应用的根目录为本文件的上一目录
 * 2、初始化Yaf\Application对象时，将配置文件application.ini加载到内存
 * 3、Yaf\Application::bootstrap()方法初始化应用本身定义的功能
 * 4、Yaf\Application::run()方法运行一个Yaf\Application, 开始接受并处理请求
 */
/* 调整默认的http useragent信息 */
ini_set('user_agent', 'kfzagent');
/* 设置输出流字符编码 */
header('Content-type: text/html;charset=utf-8');
/* 设置时区 */
date_default_timezone_set('Asia/Shanghai');
/* 指向项目根目录 */
define("APP_PATH", realpath(dirname(__FILE__) . '/../'));
/* web根目录 */
define("WEBROOT_DIR", dirname(__FILE__) . '/');
//loyout目录
define('LAYOUT_DIR', APP_PATH . '/src/layout/');
include (APP_PATH . "/conf/const.php");
//Widgets目录
define('WIDGETS_DIR', APP_PATH . '/src/widgets/');
//Widgets目录
define('MODELS_DATA_DIR', APP_PATH . '/src/data/');
//views目录(Yaf默认模板目录)
define('VIEWS_DIR', APP_PATH . '/src/modules/Index/views/');
/* 第三方库自己加载 */
include (APP_PATH . "/vendor/autoload.php");
$app = new Yaf\Application(APP_PATH . "/src/dev.ini");
$app->bootstrap();
$app->getDispatcher()->disableView();
$app->run();
