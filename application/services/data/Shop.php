<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/2 上午9:42
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */

namespace services\data;

use api\YarClient;
use yaf\Registry as R;

class Shop implements DataInterface {

    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool
     */
    public static function add(array $moduleInfo, array $data, callable $callback = null) {
        return OperationConfig::add($moduleInfo, $data, function ($id, $data) {
            return \SearchModel::singleton()->createIndex($id, 'shopName', $data['shopName']);
        });
    }

    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool
     */
    public static function edit(array $moduleInfo, array $data, callable $callback = null) {
        return OperationConfig::edit($moduleInfo, $data, $callback);
    }

    /**
     * @param array $shopIds
     * @return bool
     */
    public static function getByIds(array $shopIds) {
        $shopIds = \array_column($shopIds, 'dataId');
        $client = new YarClient(R::get('g_config')->interface->shop);
        $result = $client->call('getShopInfoByShopIds', $shopIds);
        if ($result['status']) {
            return $result['data'];
        } else {
            return false;
        }
    }

    public static function delete($data) {
        // TODO: Implement delete() method.
    }

}