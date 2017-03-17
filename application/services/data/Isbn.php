<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/2 上午10:30
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */
namespace services\data;

class Isbn implements DataInterface {
    /**
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return bool|mixed
     */
    public static function add(array $moduleInfo, array $data, callable $callback = null) {
        $data['extFields'] = ['author', 'itemName'];
        return OperationConfig::add($moduleInfo, $data, function ($id, $data) {
            return \SearchModel::singleton()->createIndex($id, 'isbn', $data['dataId'])
                && \SearchModel::singleton()->createIndex($id, 'author', $data['author'])
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

    public static function getByIds(array $ids) {
        // TODO: Implement getByIds() method.
    }

    public static function delete($data) {
        // TODO: Implement delete() method.
    }

}