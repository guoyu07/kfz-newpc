<?php

namespace http;

/**
 * Header
 *
 * @author dongnan
 */
class Header {

    /**
     * 禁止缓存
     */
    public static function noCache() {
        // Date in the past
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        // always modified
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        // HTTP/1.1
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        // HTTP/1.0
        header('Pragma: no-cache');
    }

    /**
     * 输出json
     */
    public static function json() {
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * 输出html
     */
    public static function html() {
        header('Content-Type:text/html;charset=UTF-8');
    }

    /**
     * 输出xls(Excel文件)
     */
    public static function xls($filename) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
    }

}
