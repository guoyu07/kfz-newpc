<?php

/**
 * Simple RPC Client
 */

namespace api;

use kongfz\Exception;

class RpcClient {

    private static $signArray = [
        'VFun6SmhVNgfcPssovDAESxZ1yVky5LO',
        'wyOSLwVKJz5VVTW3XwWojzmYOcb3RBjD',
        'Wgzs9n5wuRrG4SfUKoQUYr68Z3NMjXwf',
        'qNafJD4WxrQzXcjUKEIrQBb3XJLfpb2P',
        'DbGtSASlFYAw0fpUO10SNijpV3uJaRHC',
        'Bda4eusEFGagTKqvu9duhzRc26cRVAjj',
        'wnGbQp2mf4hmSQs344MI65RMjGR6N5HN',
        'uYsuEhyemqm6AXheFZnhPtT7cjucBtPa',
        'hdEWxxVyVzNLe9NoIvnSH4kO3PlcNKcZ',
        'cbHPtbt0sNhOAhu9Yr2gSIG0rTAlgghV',
        'e7VorEwgKlOL9o62SBMzhHBg4R0xyhyZ',
        'dXhZ2fsMRQhYllOoVzjLDEJFTOWLHKeC',
        'J8UavPsshyWpPm8BZpcJ6EEp8K3RFgmf',
        'Kx0nwIPs2uoEbxN4pSZKwlIywG1YFAzZ',
        'JqS1fXD03G2dfMn4Q7J17Py3qPY63NYW',
        'qM1mXbmheI39t3sSjcHNHTFi0s6lUYlZ',
        'zQxM2PqbVwSjpliKAbJF4DikCWLNwot7',
        'jAjj1FR9YDU61EJL37LT6raXJfi8aGZM',
        '3ahHsK7dXvpRJVYeBX5aAQgAOIIxaHom',
        'iOvA7VycM0xHWmD4a1FFuXvMG0wfwt6A'
    ];

    /**
     * 数字签名限制个数
     * @var int
     */
    private static $limit = 10;

    /**
     * @var array
     */
    private static $option = [
        'timeout'    => 20,
        'transports' => 'tcp://',
        'port'       => '80'
    ];

    /**
     * @param array $params
     * @return array
     */
    public static function setSign(array $params) {
        if (\is_array($params) && !empty($params)) {
            self::$signArray = $params;
        }
        return self::$signArray;
    }

    /**
     * @return mixed
     */
    public static function getSign() {
        $size = \count(self::$signArray);
        $end = $size > self::$limit ? self::$limit : $size;
        $i = \mt_rand(0, $end - 1);
        return self::$signArray[$i];
    }

    /**
     * @param string $url
     * @param string $method
     * @param array  $parameters
     * @param array $option
     * @return array|mixed
     */
    public static function call($url, $method, $parameters, $option = []) {
        if (!empty($option) && is_array($option)) {
            self::$option = array_merge(self::$option, $option);
        }
        $secureSign = self::getSign();
        //序列化数据，发送请求并等待返回结果
        $data = ['METHOD' => $method, 'ARGUMENTS' => $parameters];
        $serialStr = \serialize($data);
        $contents = 'CONTENTS=' . \urlencode($serialStr) . '&SIGN=' . \md5($secureSign . '|' . $serialStr);
        $resultSet = self::httpRequest($url, $contents, self::$option['timeout']);
        //正常响应
        $status = \intval($resultSet['status']);
        if ($status == 200) {
            $serialStr = \trim($resultSet['contents']);
            $result = \unserialize($serialStr);
            #反序列化后必须得到一个数组，否则视为返回无效数据
            if (\gettype($result) == 'array') {
                return $result;
            } else {
                $error = "Error: RPC server returned invalid data. \n" . $serialStr;
                $result = ['result' => null, 'error' => $error];
                return $result;
            }
        } // 无响应，或非正常响应
        else {
            $error = 'Error: RPC server does not respond, request service address is ' . $url;
            $result = ['result' => null, 'error' => $error];
            return $result;
        }

    }

    /**
     * @param $serverUrl
     * @param $data
     * @param $timeout
     * @return array
     * @throws Exception
     */
    private static function httpRequest($serverUrl, $data, $timeout) {
        #strict data type
        $serverUrl = \strval($serverUrl);
        $data = \strval($data);
        $timeout = \intval($timeout);
        #pase URL
        $url = \parse_url($serverUrl);

        if (!$url) {
            throw new Exception('Error: Server Url \''.$serverUrl.'\' is invalided');
        }
        if (self::$option['transports'] !== '' && self::$option['port'] !== '') {
            $url['transports'] = self::$option['transports'];
            $url['port'] = self::$option['port'];
        } else {
            if ($url['scheme'] == 'https') {
                $url['transports'] = 'tcp://';
                $url['port'] = '80';
            } else {
                $url['transports'] = 'tcp://';
                $url['port'] = '80';
            }
        }

        #organise data
        $contents = "POST " . $url['path'] . " HTTP/1.0\r\n";
        $contents .= "Accept: */*\r\n";
        $contents .= "User-Agent: Lowell-Agent\r\n";
        $contents .= "Host: " . $url['host'] . "\r\n";
        $contents .= "Content-type: application/x-www-form-urlencoded;charset=UTF-8\r\n";
        $contents .= "Content-length: " . \strlen($data) . "\r\n";
        $contents .= "Connection: close\r\n\r\n";
        $contents .= $data . "\r\n";
        #open connection
        $handle = @fsockopen($url['transports'] . $url['host'], $url['port'], $errno, $errstr, $timeout);

        $resultSet = ['status' => 0, 'contents' => ''];
        if (!$handle) {
            return $resultSet;
        }

        #send data
        \fputs($handle, $contents);
        \stream_set_timeout($handle, $timeout);
        $buffers = [];
        while (!\feof($handle)) {
            \array_push($buffers, \fgets($handle, 2048));
        }
        \fclose($handle);
        $buffers = \join('', $buffers);
        $pos = \strpos($buffers, "\r\n\r\n");
        $header = \substr($buffers, 0, $pos);
        $status = \substr($header, 0, \strpos($header, "\r\n"));
        $body = \substr($buffers, $pos + 4);

        if (\preg_match("/^HTTP\/\d\.\d\s(\d{3,4})\s/", $status, $matches)) {
            $resultSet['status'] = $matches[1];
            $resultSet['contents'] = $body;
        }

        return $resultSet;
    }
}