<?php

namespace services;

/**
 * Class Error
 * @package services
 *          运营系统中的一些自定义错误，用于系统中共享错误信息，方便随时获取
 */
class Error {

    /** @var int */
    private static $code = 0;

    /** @var string */
    private static $message = '';

    /**
     * 设置错误信息
     * @param string|int $code 错误码，如果错误信息为空，则表示错误信息
     * @param string     $message
     */
    public static function set($code, $message = '') {
        if (!empty($message)) {
            self::$code = $code;
            self::$message = $message;
        } else {
            self::$message = $code;
        }
    }

    /**
     * 获取错误码
     * @return int
     */
    public static function getCode() {
        return self::$code;
    }

    /**
     * 获取错信息
     * @return string
     */
    public static function getMessage() {
        return self::$message;
    }

    /**
     * 获取错误码和错误信息
     * @return array
     */
    public static function get() {
        return [
            'code'     => self::$code,
            'messsage' => self::$message
        ];
    }
}