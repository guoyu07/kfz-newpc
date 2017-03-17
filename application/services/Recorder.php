<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/2/28 上午11:55
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */

namespace services;

class Recorder {

    /** @var null | \LogModel */
    private $logger = null;

    /** @var array */
    private $valiables = [];

    /** @var int */
    private $opcode = 0;

    /** @var string */
    private $message = '';

    public function __construct($moduleId, $opType, $logger = null) {
        $this->getOpinfo($moduleId, $opType);
        if (null === $logger) {
            $this->logger = \LogModel::singleton();
        } else {
            $this->logger = $logger;
        }
    }

    /**
     * @param int    $moduleId
     * @param string $opType
     * @return $this
     */
    public function getOpinfo($moduleId, $opType) {
        $arr = Opcode::get($moduleId, $opType);

        if (!empty($arr)) {
            $this->opcode = $arr['opcode'];
            $this->message = $arr['desc'];
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string $val
     * @return $this
     */
    public function assign($key, $val) {
        $this->valiables[$key] = $val;
        return $this;
    }

    /**
     * @param string $message
     * @return bool
     */
    public function recorde($message = '') {
        if (empty($this->opcode)) {
            return false;
        }
        if (empty(\trim($message))) {
            $message = $this->message;
        }
        if (empty($message)) {
            return false;
        }
        foreach ($this->valiables as $key => $val) {
            if (\is_array($val)) {
                $val = \json_encode($val);
            }
            $message = \preg_replace('/\{' . $key . '\}/', $val, $message);
        }

        return $this->logger->write($this->opcode, $message);
    }

    /**
     * @param array|string $new
     * @param array        $old
     * @return string
     */
    public static function diff($new, $old = []) {
        $message = '';
        if (empty($new) || !\is_array($new)) {
            return $message;
        }

        if (empty($old)) {
            foreach ($new as $k => $v) {
                if (\is_array($v)) {
                    $v = \json_encode($v);
                }
                $message .= "'{$k}':'{$v}';";
            }
        } elseif (\is_array($old)) {
            foreach ($new as $k => $v) {
                if (\is_array($v)) {
                    $v = \json_encode($v);
                }
                if (isset($old[$k]) && \is_array($old[$k])) {
                    $old[$k] = \json_encode($old[$k]);
                }
                if (isset($old[$k]) && $old[$k] !== $v) {
                    $message .= "将'" . $k . "'由'" . $old[$k] . "'改为'" . $v . "';";
                } elseif (!empty($old[$k])) {
                    $message .= "新增了'" . $k . "' => '" . $v . "';";
                }
            }
        }

        return $message;
    }

    /**
     * 将数组转成字符串
     * @param string|array $arr
     * @return string
     */
    public static function arrToString($arr) {
        $message = '';
        if (\is_string($arr)) {
            return $arr;
        }
        if (\is_array($arr)) {
            foreach ($arr as $k => $v) {
                $message .= "'{$k}':'{$v}';";
            }
        }
        return $message;
    }
}