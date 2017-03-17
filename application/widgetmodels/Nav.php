<?php

namespace widgetmodels;

use api\YarClient;
use Yaf\Registry as R;


/**
 * Nav
 *
 * @author dongnan
 */
class Nav extends \kongfz\ViewModel
{

    use \kongfz\traits\Singleton;

    /**
     * 单例
     * @return \widgetmodels\Nav
     */
    public static function singleton()
    {
        return self::instance();
    }

    public function hello()
    {
        echo "Hello, I'm Nav!\n";
    }

    public function top($query = [])
    {
        //根据query处理业务数据
        //$data = $model->execute($query);
        $data = ['_METHOD_' => __METHOD__, 'test' => 'json', 'hello' => 'world'];
        return $data;
    }

    /**
     * 书店区左侧边栏
     * @param array $query
     * @return array
     */
    public function shopleftbar($query = [])
    {
        $client = new YarClient(R::get('g_config')->interface->shop);
        $shopStaticData = $client->getShopStaticData();

        $data = [];
        $data['shopNum'] = isset($shopStaticData['onSaleShopAmount']) ? $shopStaticData['onSaleShopAmount'] : '';
        $data['tanNum'] = isset($shopStaticData['onSaleTanAmount']) ? $shopStaticData['onSaleTanAmount'] : '';
        $data['bookNum'] = isset($shopStaticData['bookAmount']) ? $shopStaticData['bookAmount'] : '';
        $data['saleTop10ByDay'] = isset($shopStaticData['saleTop10'][0]) ? $shopStaticData['saleTop10'][0] : '';
        $data['saleTop10ByWeek'] = isset($shopStaticData['saleTop10'][1]) ? $shopStaticData['saleTop10'][1] : '';
        $data['saleTop10ByMonth'] = isset($shopStaticData['saleTop10'][2]) ? $shopStaticData['saleTop10'][2] : '';
        $data['areaStatic'] = isset($shopStaticData['stateShopList']) ? $shopStaticData['stateShopList'] : '';

        $sortBase = [];
        foreach ($data['areaStatic'] as &$item) {
            unset($item['shopList']);
            $sortBase[] = $item['shopCount'];
        }
        array_multisort($sortBase, SORT_DESC, $data['areaStatic']);
        $data['total'] = $shopStaticData['total'];
        return $data;
    }

}
