<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/2 上午9:04
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */
namespace services\data;

interface DataInterface {

    /**
     * 添加数据
     * @param array         $moduleInfo
     * @param array         $data
     * @param null|callable $callback
     * @return mixed
     */
    public static function add(array $moduleInfo, array $data, callable $callback = null);

    /**
     * 编辑数据
     * @param array         $moduleInfo
     * @param array         $data
     * @param callable|null $callback
     * @return mixed
     */
    public static function edit(array $moduleInfo, array $data, callable $callback = null);

    /**
     * 获取数据
     * @param array $ids
     * @return mixed
     */
    public static function getByIds(array $ids);

    /**
     * 删除数据
     * @param mixed $data
     * @return mixed
     */
    public static function delete($data);
}