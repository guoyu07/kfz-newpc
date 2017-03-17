<?php

namespace kongfz;

use Yaf\Registry as R;
use kongfz\Exception;

/**
 * Lang
 * 语言包工具类，语言定义必须为一维数组，key必须为大写
 *
 * @author DongNan <dongyh@126.com>
 * @date 2015-8-28
 */
class Lang {

    /**
     * 语言包实例数组
     * @var array 
     */
    static private $instances = [];

    /**
     * 工厂模式
     * @param string|array $name
     * @param string $lang
     * @return \kongfz\Lang
     * @throws Exception
     */
    public static function factory($name, $lang = null) {
        if (empty($name)) {
            throw new Exception("PARAMETER 'name' IS EMPTY!", PARAMETER_ERROR);
        }
        //如果没有设置语言，则使用当期默认语言
        if (empty($lang)) {
            //如果设置了默认语言，则使用默认语言，如果没有，则使用简体中文
            $lang = strtolower(R::get('lang') ?: 'zh-cn');
        }
        $key = "{$name}:{$lang}";
        if (!isset(self::$instances[$key]) || !(self::$instances[$key] instanceof self)) {
            self::$instances[$key] = new self($name, $lang);
        }
        return self::$instances[$key];
    }

    /**
     * 语言包数组
     * @var array 
     */
    private $lang = [];

    private function __construct($name, $lang = null) {
        if (is_array($name)) {
            foreach ($name as $n) {
                $filename = LANG_DIR . $n . '.' . $lang . '.php';
                if (!file_exists($filename)) {
                    throw new Exception("FILE '{$filename}' IS NOT EXIST!", FILE_NOT_FOUND);
                }
                $this->set(include $filename);
            }
        } else {
            $filename = LANG_DIR . $name . '.' . $lang . '.php';
            if (!file_exists($filename)) {
                throw new Exception("FILE '{$filename}' IS NOT EXIST!", FILE_NOT_FOUND);
            }
            $this->set(include $filename);
        }
    }

    private function __clone() {
        ;
    }

    /**
     * 获取语言定义
     * @param string $name  语言定义的key
     * @param array $value  需要替换的变量
     * @return mixed
     */
    public function get($name = '', $value = null) {
        // 参数为空则返回所有定义
        if (empty($name)) {
            return $this->lang;
        }
        // 获取语言定义,如果$value是数组，则支持变量替换
        if (is_string($name)) {
            $name = strtoupper($name);
            if (is_null($value)) {
                return isset($this->lang[$name]) ? $this->lang[$name] : $name;
            } elseif (is_array($value)) {
                // 支持变量
                $replace = array_keys($value);
                foreach ($replace as &$v) {
                    $v = '{:' . $v . '}';
                }
                return str_replace($replace, $value, isset($this->lang[$name]) ? $this->lang[$name] : $name);
            }
        }
        return null;
    }

    /**
     * 设置语言定义
     * @param minxed $name
     * @param string $value
     * @return boolean
     */
    public function set($name, $value = null) {
        // 语言定义
        if (is_string($name)) {
            $this->lang[$name] = $value;
            return true;
        }
        // 批量定义
        elseif (is_array($name)) {
            $this->lang = array_merge($this->lang, array_change_key_case($name, CASE_UPPER));
            return true;
        }
        return false;
    }

}
