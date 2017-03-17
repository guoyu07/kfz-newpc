<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/2 上午9:07
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */

namespace services\data;

use storage\Db;

class LunboImg implements DataInterface {
    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool
     */
    public static function add(array $moduleInfo, array $data, callable $callback = null) {
        $res = false;
        $data['moduleId'] = $moduleInfo['moduleId'];
        $dataId = \LunboImageModel::singleton()->addImg($data);
        if ($dataId) {
            $data['dataId'] = $dataId;
            $confId = \OperationConfigModel::singleton()->add($data);
            //search表中新增数据,建立索引
            if ($confId) {
                $res = \SearchModel::singleton()->createIndex($confId, 'description', $data['description']);
            }
        }
        return (bool)$res;
    }

    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool
     */
    public static function edit(array $moduleInfo, array $data, callable $callback = null) {
        //由于medoo的事务操作只有在action参数is_callable不成立的时候返回false,具体事务执行成功与否都返回void
        //所以这里在action外部声明一个$res，use到action中的匿名函数中，并使用引用类型。
        $res = false;
        $moduleId = $moduleInfo['moduleId'];
        $data['moduleId'] = $moduleId;
        Db::factory('adminMaster')->action(function ($db) use ($moduleId, $data, &$res) {
            $res = \OperationConfigModel::singleton()->updateByConfId($moduleId, $data)
                && \LunboImageModel::singleton()->updateLunboImgInfo($data);
            if ($res) {
                //查询是否存在此索引，并且是否与现有的索引值一致
                $descriptionVal = \SearchModel::singleton()->getIndex($data['confId'], 'description');
                if (empty($descriptionVal) || $descriptionVal != $data['description']) {
                    $res = \SearchModel::singleton()->updateIndex($data['confId'], 'description', $data['description']);
                }
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
        $result = \LunboImageModel::singleton()->getImgByIds($ids);
        if (empty($result)) {
            return [];
        }
        return $result;
    }


    public static function delete($data) {
        //暂不使用
    }

}