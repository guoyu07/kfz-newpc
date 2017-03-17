<?php

use Yaf\Registry as R;

class Bootstrap extends Yaf\Bootstrap_Abstract {

    /**
     * 注册自动加载
     * @param Yaf\Dispatcher $dispatcher
     */
    public function _initAutoload(Yaf\Dispatcher $dispatcher) {
        spl_autoload_register(function($class) {
            if (false !== strpos($class, '\\')) {
                $name = strstr($class, '\\', true);
                if (in_array($name, ['kongfz', 'widgetmodels', 'interfaces', 'services'])) {
                    $filename = APP_PATH . '/application/' . str_replace('\\', '/', $class) . '.php';
                    if (file_exists($filename)) {
                        require_once $filename;
                    }
                }
            }
        });
    }

    /**
     * 注册插件
     * @param Yaf\Dispatcher $dispatcher
     */
    public function _initPlugin(Yaf\Dispatcher $dispatcher) {
        
    }

    /**
     * 注册公共对象
     * @param Yaf\Dispatcher $dispatcher
     */
    public function _initRegistry(Yaf\Dispatcher $dispatcher) {
        R::set('g_config', Yaf\Application::app()->getConfig());
        //暂时默认为简体中文
        R::set('lang', 'zh-cn');
    }

    /**
     * 注册常量
     * @param Yaf\Dispatcher $dispatcher
     */
    public function _initConst(Yaf\Dispatcher $dispatcher) {
        define('REQUEST_SUFFIX', strtolower($dispatcher->getRequest()->getQuery('_suffix_')));
    }

    /**
     * 初始化路由协议
     * @param Yaf\Dispatcher $dispatcher
     */
    public function _initRoute(Yaf\Dispatcher $dispatcher) {
        $router = $dispatcher->getRouter();
        $router->addConfig(Yaf\Application::app()->getConfig()->routes);
    }

    /**
     * 初始化模板引擎
     * @param Yaf\Dispatcher $dispatcher
     */
    public function _initView(Yaf\Dispatcher $dispatcher) {
        if (defined('VIEWS_DIR')) {
            $dispatcher->initView(VIEWS_DIR);
        }
    }

    /**
     * 初始化session
     * @param Yaf\Dispatcher $dispatcher
     */
    public function _initSession(Yaf\Dispatcher $dispatcher) {
        //if (!$dispatcher->getRequest()->isCli()) {
            $session = R::get('g_config')->cache->memcache->adminSession->toArray();
            new session\MemcacheSession([
                'sessionName' => $session[0]['sessionName'],
                'servers'     => ['host' => $session[0]['host'], 'port' => $session[0]['port']],
                'lifetime'    => $session[0]['leftTime']
            ]);
            ini_set('session.cookie_domain', $session[0]['domain']);
            session_name('KFZ_ADMIN_SESSION_NAME');
            session_cache_limiter('private, must-revalidate');
            session_start();
        //}
    }
}
