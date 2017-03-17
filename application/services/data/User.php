<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/2 上午9:48
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */

namespace services\data;

use api\YarClient;
use yaf\Registry as R;

class User implements DataInterface {

    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool
     */
    public static function add(array $moduleInfo, array $data, callable $callback = null) {
        return OperationConfig::add($moduleInfo, $data, function ($id, $data) {
            return \SearchModel::singleton()->createIndex($id, 'nickname', $data['nickname']);
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
     * @param array $userIds
     * @return array|mixed
     */
    public static function getByIds(array $userIds) {
        $userIds = \array_column($userIds, 'dataId');
        $result = [];
        if (!empty($userIds) && \is_array($userIds)) {
            $client = new YarClient(R::get('g_config')->interface->user);
            $result = $client->getUserInfoByUserIds($userIds);
        }
        if (isset($result['status']) && $result['status'] == 1) {
            $result = $result['data'];
        } else {
            $result = [];
        }
        return $result;
    }

    public static function delete($data) {
        // TODO: Implement delete() method.
    }

}