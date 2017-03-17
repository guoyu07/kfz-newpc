<?php

namespace api;

use kongfz\Exception;

/**
 * Yar客户端
 */
class YarClient {

    private static $signs = array(
        'VFun6SmhVNgfcPssovDAESxZ1yVky5LO',
        'wyOSLwVKJz5VVTW3XwWojzmYOcb3RBjD',
        'Wgzs9n5wuRrG4SfUKoQUYr68Z3NMjXwf',
        'qNafJD4WxrQzXcjUKEIrQBb3XJLfpb2P',
        'DbGtSASlFYAw0fpUO10SNijpV3uJaRHC',
    );
    public $server;
    private $callBack;
    private $callNum      = 0;

    public function __construct($server) {
        $this->server = $server;
    }

    /**
     * 取得签名
     * @param  $params 接口调用时的参数
     */
    protected function getSign($params) {
        ksort($params);
        $signStr = '';
        foreach ($params as $key => $val) {
            if (empty($val))
                continue;
            $signStr .= $key . '=' . $val . '&';
        }
        $signStr = rtrim($signStr, '&');
        return md5($signStr . self::$signs[mt_rand(0, count(self::$signs) - 1)]);
    }

    /**
     * 调用服务端接口
     * @param  $server		Api server
     * @param  $api			接口
     * @param  $params		参数
     * @param  $callBack	回调
     * @param  $openSign	开启签名
     */
    public function call($api, $params, $callBack = null, $openSign = true) {
        $log = 'begin:' . microtime(true);
        if ($openSign) {
            $params['sign'] = $this->getSign($params);
        }
        if ($callBack === null) {
            $client = new \Yar_Client($this->server);
            $client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, 1000);
            $client->SetOpt(YAR_OPT_TIMEOUT, 3000);
            try {
                return $client->$api($params);
            } catch (Exception $e) {
                //$logfile = DATA_LOG . 'shop/' . date('Y/md/') . __METHOD__ . '.log';
                $log .= ',end:' . microtime(true);
                $log .= ",{server}" . $this->server . "\t{api}{$api}\t{params}" . serialize($params) . "\t{message}" . $e->getMessage();
                //\Kfz_Lib_Log::writeLog($logfile, $log, 3);
            }
        }
        $this->callNum ++;
        $this->callBack = $callBack;
        try {
            return \Yar_Concurrent_Client::call($this->server, $api, $params, array($this, 'ApiClientCallBack'));
        } catch (Exception $e) {
            //$logfile = DATA_LOG . 'shop/' . date('Y/md/') . __METHOD__ . '.log';
            $log .= ',end:' . microtime(true);
            $log .= ",[ApiClientCallBack]{server}" . $this->server . "\t{api}{$api}\t{params}" . serialize($params) . "\t{message}" . $e->getMessage();
            //\Kfz_Lib_Log::writeLog($logfile, $log, 3);
        }
    }

    /**
     * 执行并发调用
     */
    public function loop() {
        return \Yar_Concurrent_Client::loop();
    }

    /**
     * 注册魔术方法
     * @param  $method
     * @param  $params
     */
    public function __call($method, $params) {
        $log            = 'begin:' . microtime(true);
        $params         = current($params);
        $params['sign'] = $this->getSign($params);
        $client         = new \Yar_Client($this->server);
        $client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, 20000);
        $client->SetOpt(YAR_OPT_TIMEOUT, 30000);
        try {
            return $client->$method($params);
        } catch (Exception $e) {
            //$logfile = DATA_LOG . 'shop/' . date('Y/md/') . __METHOD__ . '.log';
            $log .= ',end:' . microtime(true);
            $log .= ",{server}" . $this->server . "\t{method}{$method}\t{params}" . serialize($params) . "\t{message}" . $e->getMessage();
            //\Kfz_Lib_Log::writeLog($logfile, $log, 3);
        }
    }

    /**
     * 并发调用回调
     * @param  $retval
     * @param  $callinfo
     */
    public function ApiClientCallBack($retval, $callinfo) {
        if ($callinfo === null) {
            return $this->callBack($retval, $callinfo);
        }
        static $data               = array();
        $data[$callinfo['method']] = $retval;
        if (count($data) == $this->callNum) {
            $fn = $this->callBack;
            return $fn($data, $callinfo);
        }
    }

}
