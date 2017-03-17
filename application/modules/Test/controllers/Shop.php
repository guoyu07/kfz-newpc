<?php
use linkcache\Cache;
use api\YarClient;

class ShopController extends \kongfz\Controller
{
    public function indexAction()
    {
        $redisConf = \Yaf\Registry::get('g_config')->redisSHOP->toArray();
        $redisObj = new Cache('redis', $redisConf);
        $bizType = consts\Cache::SHOP_UNION_INDEX_KEY;
        $key = kongfz\CacheTool::getKey($bizType, date('Ymd'));
        $data = $redisObj->get($key);

        if ($data === false) {
            $url = \Yaf\Registry::get('g_config')->interface->shop;
            $client = new YarClient($url);
            $data = $client->call('getShopHomePageData', []);
        }
        print_r($data);
    }
}