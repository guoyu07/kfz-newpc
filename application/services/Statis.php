<?php


namespace services;

use Http\Request;
use linkcache\Cache;
use Yaf\Application;

class Statis {

    /** @var string cookie key */
    const COOKIE_KEY = 'kongfz-os-key';

    /** @var string 队列key */
    const QUEUE_STATIS_KEY = 'kongfz_os_flow_key';

    /**
     * 生成一次会话的唯一标识
     * @return string
     */
    public static function generateUUID() {
        return md5(microtime(true) . mt_rand(0, 10000));
    }

    /**
     * 添加数据
     * @param $data
     * @return array|mixed
     */
    public static function push($data) {
        if (isset($_COOKIE[Statis::COOKIE_KEY]) && !empty($_COOKIE[Statis::COOKIE_KEY])) {
            $uuid = $_COOKIE[Statis::COOKIE_KEY];
        } else {
            $uuid = self::generateUUID();
            setcookie(Statis::COOKIE_KEY, $uuid, 0, '/');
        }

        $arr = [
            'moduleId'   => $data['moduleId'],
            'dataId'     => $data['dataId'],
            'agent'      => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'uuid'       => $uuid,
            'ip'         => Request::getClientIp(),
            'userId'     => isset($data['userId']) ? $data['userId'] : 0,
            'updateTime' => time()
        ];
        //存入队列，定期回收做持久化
        return self::pushQueue($arr);
        //return \StatisModel::singleton()->pushData($arr);
    }

    /**
     * 统计数据入队
     * @param $data
     * @return bool
     */
    private static function pushQueue($data) {
        /** @var array $redisConf */
        $redisConf = Application::app()->getConfig()->cache->redis->statis->toArray();

        /** @var \Redis $redis */
        $redis = new Cache('redis', [
            'host' => $redisConf[0]['host'],
            'port' => $redisConf[0]['port']
        ]);
        $redis->lPush(Statis::QUEUE_STATIS_KEY, json_encode($data));

        return true;
    }

    /**
     * 统计数据持久化
     */
    private static function persistence() {
        /** @var array $redisConf */
        $redisConf = Application::app()->getConfig()->cache->redis->statis->toArray();

        /** @var \Redis $redis */
        $redis = new Cache('redis', [
            'host' => $redisConf[0]['host'],
            'port' => $redisConf[0]['port']
        ]);

        $statisModel = \StatisticModel::singleton();

        while ($data = $redis->rPop(Statis::QUEUE_STATIS_KEY)) {
            $data = json_decode($data);
            $statisModel->pushData($data);
        }

        usleep(100000);
    }

}