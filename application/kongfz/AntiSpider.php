<?php
/**
 * 反爬虫类
 * Author: diao
 * Date: 17-1-22
 */

namespace kongfz;

use linkcache\Cache;
use http\Request;
use kongfz\traits\Singleton;

class AntiSpider
{
    private $redisConf;
    private $redisObj;

    use Singleton;

    public static function getInstance()
    {
        return self::instance();
    }

    public function _init_()
    {
        $this->redisConf = \Yaf\Registry::get('g_config')->redisSHOP->toArray();
        $this->redisObj = new Cache('redis', $this->redisConf);
    }
    /**
     * 开始反爬虫
     * @param $num 阀值 每分钟允许访问次数
     * @param $time 封锁时间 单位秒
     * @param $bizType 当前业务类型
     * @return bool 是否为爬虫
     */
    public function start($num, $time, $bizType)
    {
        if (!isset($num) || empty($num) || !isset($time) || empty($time) || !isset($bizType) || empty($bizType)) {
            exit('necessary params is lost!');
        }
        $ip = Request::getClientIp();
        $clientKey = \consts\Cache::CLIENT_IP . $ip;
        $spiderKey = \consts\Cache::SPIDER_IP . $ip;
        if ($this->redisObj->get($clientKey) === false) {
            $this->redisObj->set($clientKey, 0, CacheTool::getTime(\consts\Cache::CLIENT_IP));
        }

        // 判断是否为爬虫
        if ($this->redisObj->incr($clientKey) > $num) {
            $this->redisObj->setnx($spiderKey, $bizType, $time);
        }
        if ($this->redisObj->has($spiderKey)) {
            return true;
        }
        return false;
    }
}