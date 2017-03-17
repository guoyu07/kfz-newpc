<?php

namespace http;


class Response
{

    /** @var ajax请求未登录返回的错误 */
    const ERR_AJAX_NO_LOGIN = 1;

    /**
     * @param bool|int $status
     * @param string $message
     * @param array  $data
     * @param int    $errType
     * @param array  $other
     */
    public static function json($status, $message = '', $data = [], $errType = 0, $other = [])
    {
        Header::json();

        echo json_encode([
            'status' => (int) $status,
            'data' => $data,
            'message' => $message,
            'errType' => $errType,
            'other' => $other
        ]);

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        exit;
    }

    public static function _404()
    {
        header('Content-Type:text/html;charset=UTF-8', true, 404);
        die('<h1>404 Not Found</h1>');
    }
}