<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/2 上午9:30
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */

namespace services\data;

use storage\Db;

class Links implements DataInterface {

    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool|int
     */
    public static function add(array $moduleInfo, array $data, callable $callback = null) {
        $res = false;
        $data['moduleId'] = $moduleInfo['moduleId'];
        $dataId = \LinksModel::singleton()->addLinks($data);
        if ($dataId) {
            $data['dataId'] = $dataId;
            $confId = \OperationConfigModel::singleton()->add($data);
            if ($confId) {
                $res = \SearchModel::singleton()->createIndex($confId, 'linkTitle', $data['linkTitle']);
            }
        }
        return $res;
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
                && \LinksModel::singleton()->update($data);
            if ($res) {
                //更新索引
                $res = \SearchModel::singleton()->updateIndex($data['confId'], 'linkTitle', $data['linkTitle']);
            }
            return $res;
        });

        return $res;
    }

    /**
     * @param array $ids
     * @return array
     */
    public static function getByIds(array $ids) {
        $ids = \array_column($ids, 'dataId');
        $result = \LinksModel::singleton()->getLinkByIds($ids);
        if (empty($result)) {
            return [];
        }
        return $result;
    }

    public static function delete($data) {
        // TODO: Implement delete() method.
    }

}