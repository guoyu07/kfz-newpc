<?php
/**
 * Created by PhpStorm.
 * User: diao
 * Date: 17-1-20
 * Time: 下午12:17
 */

namespace kongfz;

use consts\Cache;


class CacheTool
{
    /**
     * 获取缓存key
     *
     * @param string $bizName
     * @param string $str
     *
     * @return string
     */
    public static function getKey($bizName, $str)
    {
        if (stripos($bizName, Cache::SQL) === 0) {
            return $bizName . md5($str);
        } else {
            return $bizName . $str;
        }
    }

    /**
     * 获取缓存生存周期
     *
     * @param string $bizName
     * @param string $str
     *
     * @return string
     */
    public static function getTime($bizName)
    {
        $time = Cache::$TIME;
        if (isset($time[$bizName])) {
            return $time[$bizName];
        } else {
            return 0;
        }
    }
}