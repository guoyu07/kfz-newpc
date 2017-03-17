<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/2 上午9:13
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------

 */
namespace services\data;

use storage\Db;

class Advertise implements DataInterface {

    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool
     */
    public static function add(array $moduleInfo, array $data, callable $callback = null) {
        $res = false;
        $data['moduleId'] = $moduleInfo['moduleId'];
        $dataId = \AdvertiseImageModel::singleton()->addImg($data, $moduleInfo['showType']);
        if ($dataId) {
            $data['dataId'] = $dataId;
            $confId = \OperationConfigModel::singleton()->add($data);
            //search表中新增数据,建立索引
            if ($confId) {
                $res = \SearchModel::singleton()->createIndex($confId, 'firstDesc', $data['firstDesc']);
                if ($moduleInfo['showType'] == '0') {
                    $res = \SearchModel::singleton()->createIndex($confId, 'title', $data['title']);
                    $res = \SearchModel::singleton()->createIndex($confId, 'secondDesc', $data['secondDesc']);
                }

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
        $res = false;
        $moduleId = $moduleInfo['moduleId'];
        $showType = $moduleInfo['showType'];
        $data['moduleId'] = $moduleId;
        Db::factory('adminMaster')->action(function ($db) use ($moduleId, $showType, $data, &$res) {
            $res = \OperationConfigModel::singleton()->updateByConfId($moduleId, $data)
                && \AdvertiseImageModel::singleton()->updateAdvertiseImgInfo($showType, $data);
            if ($res) {
                //查询是否存在此索引，并且是否与现有的索引值一致
                $firstDescVal = \SearchModel::singleton()->getIndex($data['confId'], 'firstDesc');
                if (empty($firstDescVal) || $firstDescVal != $data['firstDesc']) {
                    $res = \SearchModel::singleton()->updateIndex($data['confId'], 'firstDesc', $data['firstDesc']);
                }
                if ($showType == '0') {
                    $titleVal = \SearchModel::singleton()->getIndex($data['confId'], 'title');
                    if (empty($titleVal) || $titleVal != $data['title']) {
                        $res = \SearchModel::singleton()->updateIndex($data['confId'], 'title', $data['title']);
                    }
                    $secondDescVal = \SearchModel::singleton()->getIndex($data['confId'], 'secondDesc');
                    if (empty($secondDescVal) || $secondDescVal != $data['secondDesc']) {
                        $res = \SearchModel::singleton()->updateIndex($data['confId'], 'secondDesc',
                            $data['secondDesc']);
                    }
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
        $result = [];
        if (!empty($ids) && is_array($ids)) {
            $result = \AdvertiseImageModel::singleton()->getImgByIds($ids);
        }
        return $result;
    }

    public static function delete($data) {

    }
}