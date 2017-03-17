<?php

namespace kongfz;

/**
 * ArrayTool
 * 数组工具类
 * @author DongNan <dongyh@126.com>
 * @date   2015-9-1
 */
class ArrayTool {

    static public function replace($search, $replace, &$data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    self::replace($search, $replace, $data[$key]);
                } else {
                    $data[$key] = str_replace($search, $replace, $value);
                }
            }
        } else {
            $data = str_replace($search, $replace, $data);
        }
        return $data;
    }
}
