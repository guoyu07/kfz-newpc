<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/2 上午9:40
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */

namespace services\data;

use api\YarClient;
use yaf\Registry as R;

class ShopItem implements DataInterface {
    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool
     */
    public static function add(array $moduleInfo, array $data, callable $callback = null) {
        $data['extFields'] = ['shopId'];
        return OperationConfig::add($moduleInfo, $data, function ($id, $data) {
            return \SearchModel::singleton()->createIndex($id, 'shopName', $data['shopName'])
                && \SearchModel::singleton()->createIndex($id, 'itemName', $data['itemName']);
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
     * @param array $ids
     * @return array|mixed
     */
    public static function getByIds(array $ids) {
        $result = [];
        $arr = [];
        if (\is_array($ids) && !empty($ids)) {
            foreach ($ids as $item) {
                \array_push($arr, ['itemId' => $item['dataId'], 'shopId' => $item['shopId']]);
            }
            $client = new YarClient(R::get('g_config')->interface->shop);
            $result = $client->getItemInfoByItemIds($arr);
        }
        //处理数组键名冲突
        if (!empty($result['data']) && \is_array($result['data'])) {
            foreach ($result['data'] as &$row) {
                $row['itemStatus'] = $row['status'];
                $row['itemAddTime'] = $row['addTime'];
                unset($row['status']);
                unset($row['addTime']);
            }
            return $result['data'];
        }
        return [];
    }

    public static function delete($data) {
        // TODO: Implement delete() method.
    }

}