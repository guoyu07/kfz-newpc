<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/2 上午9:44
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */
namespace services\data;

use api\YarClient;
use yaf\Registry as R;

class AuctionItem implements DataInterface {

    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool|mixed
     */
    public static function add(array $moduleInfo, array $data, callable $callback = null) {
        return OperationConfig::add($moduleInfo, $data, function ($id, $data) {
            return \SearchModel::singleton()->createIndex($id, 'itemName', $data['itemName'])
                && \SearchModel::singleton()->createIndex($id, 'nickname', $data['nickname']);
        });
    }

    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool|mixed
     */
    public static function edit(array $moduleInfo, array $data, callable $callback = null) {
        return OperationConfig::edit($moduleInfo, $data, $callback);
    }

    /**
     * @param array $itemIds
     * @return array
     */
    public static function getByIds(array $itemIds) {
        $itemIds = \array_column($itemIds, 'dataId');
        $arr = [];
        if (!empty($itemIds) && \is_array($itemIds)) {
            $client = new YarClient(R::get('g_config')->interface->pm);
            $result = $client->getAuctionItemByItemIds($itemIds);
            //处理数组键名冲突
            foreach ($result['data'] as $item) {
                $item['auctionStatus'] = $item['status'];
                $item['auctionEndTime'] = $item['endTime'];
                unset($item['status']);
                unset($item['endTime']);
                $arr[] = $item;
            }
        }
        return $arr;
    }

    public static function delete($data) {
        // TODO: Implement delete() method.
    }

}