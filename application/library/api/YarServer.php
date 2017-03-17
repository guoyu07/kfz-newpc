<?php

namespace api;

/**
 * @date 2014-12-23
 * @author zhengyin <zhengyin@kongfz.com>
 * Yar 接口服务
 */
class YarServer {

    private static $signs = array(
        'VFun6SmhVNgfcPssovDAESxZ1yVky5LO',
        'wyOSLwVKJz5VVTW3XwWojzmYOcb3RBjD',
        'Wgzs9n5wuRrG4SfUKoQUYr68Z3NMjXwf',
        'qNafJD4WxrQzXcjUKEIrQBb3XJLfpb2P',
        'DbGtSASlFYAw0fpUO10SNijpV3uJaRHC',
    );

    /**
     * 验证签名
     * @param  $params 接口调用时的参数
     * @param  $sign   签名
     */
    public function checkSign($params) {
        if (empty($params['sign'])) {
            return false;
        }
        $sign    = $params['sign'];
        ksort($params);
        $signStr = '';
        foreach ($params as $key => $val) {
            if (empty($val) || $val == $sign)
                continue;
            $signStr .= $key . '=' . $val . '&';
        }
        $signStr = rtrim($signStr, '&');
        foreach (self::$signs as $v) {
            if (md5($signStr . $v) === $sign) {
                return true;
            }
        }
        return false;
    }

    /**
     * 返回接口处理结果
     * @param  $status
     * @param  $data
     * @param  $other
     * return  Array [格式化好了的结果]
     */
    public function response($status, $data, $other = array()) {
        $response            = array();
        $response ['status'] = (int) $status;
        $response ['data']   = $data;
        $response ['other']  = $other;
        return $response;
    }

}
