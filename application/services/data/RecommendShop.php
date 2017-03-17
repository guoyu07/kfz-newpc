<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/2 上午9:34
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */

namespace services\data;

use storage\Db;

class RecommendShop implements DataInterface {

    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool
     */
    public static function add(array $moduleInfo, array $data, callable $callback = null) {
        $result = false;
        $data['moduleId'] = $moduleInfo['moduleId'];
        $dataId = \ShopRecommendModel::singleton()->add($data);
        if ($dataId) {
            $data['dataId'] = $dataId;
            $confId = \OperationConfigModel::singleton()->add($data);
            if ($confId) {
                $result = \SearchModel::singleton()->createIndex($confId, 'shopName', $data['shopName']);
            }
        }
        return (bool)$result;
    }

    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool
     */
    public static function edit(array $moduleInfo, array $data, callable $callback = null) {
        $res = false;
        $moduleId = $moduleInfo['moduleId'];
        $data['moduleId'] = $moduleId;
        Db::factory('adminMaster')->action(function ($db) use ($moduleId, $data, &$res) {
            $res = \OperationConfigModel::singleton()->updateByConfId($moduleId, $data)
                && \ShopRecommendModel::singleton()->editShopInfo($data);
            return $res;
        });
        return $res;
    }

    /**
     * @param array $ids
     * @return array|bool
     */
    public static function getByIds(array $ids) {
        $ids = \array_column($ids, 'dataId');
        $result = [];
        if (!empty($ids) && \is_array($ids)) {
            $result = \ShopRecommendModel::singleton()->getDataByIds($ids);
        }
        return $result;
    }

    public static function delete($data) {
        // TODO: Implement delete() method.
    }

}