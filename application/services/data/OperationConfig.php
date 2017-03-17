<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/2 上午10:22
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */
namespace services\data;

class OperationConfig implements DataInterface {
    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool
     */
    public static function add(array $moduleInfo, array $data, callable $callback = null) {
        $data['moduleId'] = $moduleInfo['moduleId'];
        $id = \OperationConfigModel::singleton()->add($data);
        if (!$id) {
            return false;
        }
        if (is_callable($callback)) {
            return $callback($id, $data);
        }
        return true;
    }

    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool
     */
    public static function edit(array $moduleInfo, array $data, callable $callback = null) {
        return \OperationConfigModel::singleton()->updateByConfId($moduleInfo['moduleId'], $data);
    }

    public static function getByIds(array $ids) {
        // TODO: Implement getByIds() method.
    }

    public static function delete($data) {
        // TODO: Implement delete() method.
    }

}