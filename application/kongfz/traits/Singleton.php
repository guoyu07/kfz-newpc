<?php

namespace kongfz\traits;

/**
 * Singleton
 *
 * @author dongnan
 */
trait Singleton {

    private static $instance;

    /**
     * 单例
     */
    private static function instance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        if (method_exists($this, '_init_')) {
            $this->_init_();
        }
    }

    private function __clone() {
        ;
    }

}
