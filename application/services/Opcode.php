<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/2/28 上午9:47
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */

namespace services;

class Opcode {

    /** 计算操作码的倍率 */
    const RATE = 1000;

    private static $map = [
        'login'             => [
            'offset' => -1,
            'desc'   => '登录了系统'
        ],
        'addDefaultData'    => [
            'offset' => 0,
            'desc'   => '添加默认数据，moduleId为：{moduleId}，数据为：{message}'
        ],
        'editDefaultData'   => [
            'offset' => 1,
            'desc'   => '修改了默认数据，moduleId为：{moduleId}，confId为：{confId}, 改动为:{message}'
        ],
        'deleteDefaultData' => [
            'offset' => 2,
            'desc'   => '删除了默认数据，moduleId为：{moduleId}，confId为:{confId}'
        ],
        'editModule'        => [
            'offset' => 3,
            'desc'   => '修改了模块信息，moduleId为：{moduleId}，改动为:{message}'
        ],
        'addModuleData'     => [
            'offset' => 4,
            'desc'   => '添加了模块数据，moduleId为：{moduleId}, 数据为：{message}'
        ],
        'editModuleData'    => [
            'offset' => 5,
            'desc'   => '修改了模块数据，moduleId为：{moduleId}，改动为：{message}'
        ],
        'deleteModuleData'  => [
            'offset' => 6,
            'desc'   => '删除了模块数据，moduleId为：{moduleId}, confId为：{confId}'
        ],
        'addSubModule'      => [
            'offset' => 7,
            'desc'   => '添加了子模块，父模块moduleId为：{moduleId}，数据为：{message}'
        ],
        'deleteModule'      => [
            'offset' => 8,
            'desc'   => '删除了模块，moduleId为：{moduleId}'
        ]

    ];

    /**
     * 根据模块id和操作类型获取操作码和描述信息
     * @param $moduleId
     * @param $opType
     * @return bool|mixed
     */
    public static function get($moduleId, $opType) {

        if (!isset(self::$map[$opType])) {
            Error::set('操作类型错误');
            return false;
        }

        $arr = self::$map[$opType];
        //一些特殊操作的偏移量小于0
        if ($arr['offset'] < 0) {
            return $arr;
        }
        $arr['opcode'] = $moduleId * self::RATE + $arr['offset'];
        unset($arr['offset']);

        return $arr;
    }

    /**
     * 通过opcode反推moduleId
     * @param $opcode
     * @return int
     */
    public static function getModuleIdByOpcode($opcode) {
        return intval($opcode / self::RATE);
    }
}