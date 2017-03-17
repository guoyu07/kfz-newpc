<?php

/**
 * linkcache - 一个灵活高效的PHP缓存工具库
 *
 * @author      Dong Nan <hidongnan@gmail.com>
 * @copyright   (c) Dong Nan http://idongnan.cn All rights reserved.
 * @link        http://git.oschina.net/dongnan/LinkCache
 * @license     BSD (http://opensource.org/licenses/BSD-3-Clause)
 */

namespace linkcache\drivers;

use linkcache\interfaces\driver\Base;
use linkcache\interfaces\driver\Lock;
use linkcache\interfaces\driver\Incr;
use linkcache\interfaces\driver\Multi;
use \Exception;

/**
 * Memcache
 */
class Memcache implements Base, Lock, Incr, Multi {

    use \linkcache\traits\CacheDriver;

    /**
     * Memcache 对象
     * @var \Memcache 
     */
    private $handler;

    /**
     * 是否连接server
     * @var boolean 
     */
    private $isConnected = false;

    /**
     * 重连次数
     * @var int
     */
    private $reConnected = 0;

    /**
     * 最大重连次数,默认为3次
     * @var int
     */
    private $maxReConnected = 3;

    /**
     * 压缩参数
     * @var int
     */
    private $compress = 0;

    /**
     * 构造函数
     * @param array $config 配置
     * @throws \Exception   异常
     */
    public function __construct($config = []) {
        if (!extension_loaded('memcache')) {
            throw new \Exception("memcache extension is not exists!");
        }
        $this->handler = new \Memcache();
        $this->init($config);
        //最大重连次数
        if (isset($config['maxReConnected'])) {
            $this->maxReConnected = (int) $config['maxReConnected'];
        }
        $this->initServers();
    }
    
    public function __set($name, $value) {
        return $this->set($name, $value);
    }

    public function __get($name) {
        return $this->get($name);
    }

    public function __unset($name) {
        return $this->del($name);
    }

    /**
     * Call the memcache handler's method
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $args) {
        if (method_exists($this->handler, $method)) {
            return call_user_func_array(array($this->handler, $method), $args);
        } else {
            throw new \Exception(__CLASS__ . ":{$method} is not exists!");
        }
    }

    /**
     * 初始化servers
     */
    public function initServers() {
        if (empty($this->config['servers'])) {
            $servers = [
                ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 1, 'persistent' => true, 'timeout' => 1, 'retry_interval' => 15, 'status' => true],
            ];
        } else {
            $servers = $this->config['servers'];
        }
        foreach ($servers as $server) {
            $host = isset($server['host']) ? $server['host'] : '127.0.0.1';
            $port = isset($server['port']) ? $server['port'] : 11211;
            $persistent = isset($server['persistent']) ? $server['persistent'] : null;
            $weight = isset($server['weight']) ? $server['weight'] : null;
            $timeout = isset($server['timeout']) ? $server['timeout'] : null;
            $retry_interval = isset($server['retry_interval']) ? $server['retry_interval'] : null;
            $status = isset($server['status']) ? $server['status'] : null;
            $failure_callback = isset($server['failure_callback']) ? $server['failure_callback'] : null;
            $this->handler->addserver($host, $port, $persistent, $weight, $timeout, $retry_interval, $status, $failure_callback);
        }
        if (!empty($this->config['compress'])) {
            $threshold = isset($this->config['compress']['threshold']) ? $this->config['compress']['threshold'] : 2000;
            $min_saving = isset($this->config['compress']['min_saving']) ? $this->config['compress']['min_saving'] : 0.2;
            $this->handler->setcompressthreshold($threshold, $min_saving);
            $this->compress = MEMCACHE_COMPRESSED;
        }
        //如果获取服务器池的统计信息返回false,说明服务器池中有不可用服务器
        try {
            if ($this->handler->getStats() === false) {
                $this->isConnected = false;
            } else {
                $this->isConnected = true;
            }
        } catch (Exception $ex) {
            $this->isConnected = false;
        }
    }

    /**
     * 检查连接状态
     * @return boolean
     */
    public function checkDriver() {
        if (!$this->isConnected && $this->reConnected < $this->maxReConnected) {
            if ($this->handler->getStats() !== false) {
                $this->isConnected = true;
            } else {
                $this->initServers();
            }
            if (!$this->isConnected) {
                $this->reConnected++;
            }
            //如果重连成功,重连次数置为0
            else {
                $this->reConnected = 0;
            }
        }
        return $this->isConnected;
    }

    /**
     * 获取handler(Memcache实例)
     * @return \Memcache
     */
    public function getHandler() {
        return $this->handler;
    }

    /**
     * 根据键值获取压缩参数
     * @param mixed $value
     * @return int
     */
    private function compress($value) {
        if ($this->compress) {
            //如果是数字,则不压缩
            if (is_numeric($value)) {
                return 0;
            }
        }
        return $this->compress;
    }

    /**
     * 设置键值
     * @param string $key   键名
     * @param mixed $value  键值
     * @param int $time     过期时间,默认为-1,<=0则设置为永不过期
     * @return boolean      是否成功
     */
    public function set($key, $value, $time = -1) {
        $value = self::setValue($value);
        try {
            if ($time > 0) {
                $exTime = $time <= 2592000 ? $time : time() + $time;
                if ($this->handler->set($key, $value, $this->compress($value), $exTime)) {
                    $this->handler->set(self::timeKey($key), self::setValue(['expire_time' => time() + $time]), 0, $exTime);
                    return true;
                }
                return false;
            } else {
                $this->handler->delete(self::timeKey($key));
                return $this->handler->set($key, $value, $this->compress($value));
            }
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 当键名不存在时设置键值
     * @param string $key   键名
     * @param mixed $value  键值
     * @param int $time     过期时间,默认为-1,<=0则设置为永不过期
     * @return boolean      是否成功
     */
    public function setnx($key, $value, $time = -1) {
        $value = self::setValue($value);
        try {
            if ($time > 0) {
                $exTime = $time <= 2592000 ? $time : time() + $time;
                if ($this->handler->add($key, $value, $this->compress($value), $exTime)) {
                    $ret = $this->handler->set(self::timeKey($key), self::setValue(['expire_time' => time() + $time]), 0, $exTime);
                    //如果执行失败，则尝试删除key
                    if ($ret === false) {
                        $this->handler->delete($key);
                    }
                    return $ret !== false ? true : false;
                }
                return false;
            }
            return $this->handler->add($key, $value, $this->compress($value));
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 设置键值，将自动延迟过期;<br>
     * 此方法用于缓存对过期要求宽松的数据;<br>
     * 使用此方法设置缓存配合getDE方法可以有效防止惊群现象发生
     * @param string $key    键名
     * @param mixed $value   键值
     * @param int $time      过期时间，<=0则设置为永不过期
     * @param int $delayTime 延迟过期时间，如果未设置，则使用配置中的设置
     * @return boolean       是否成功
     */
    public function setDE($key, $value, $time, $delayTime = null) {
        $value = self::setValue($value);
        try {
            if ($time > 0) {
                $delayTime = $this->getDelayTime($delayTime);
                $exTime = $time + $delayTime <= 2592000 ? $time + $delayTime : time() + $time + $delayTime;
                if ($this->handler->set($key, $value, $this->compress($value), $exTime)) {
                    $this->handler->set(self::timeKey($key), self::setValue(['expire_time' => time() + $time + $delayTime, 'delay_time' => $delayTime]), 0, $exTime);
                    return true;
                }
                return false;
            }
            $timeValue = self::getValue($this->handler->get(self::timeKey($key)));
            //已过期或 time<=0 时
            if ($timeValue !== false && (self::isExpiredDE($timeValue) || $time <= 0)) {
                $this->handler->delete($key, self::timeKey($key));
            }
            return $this->handler->set($key, $value, $this->compress($value));
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 获取键值
     * @param string $key   键名
     * @return mixed|false  键值,失败返回false
     */
    public function get($key) {
        try {
            return self::getValue($this->handler->get($key));
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 获取延迟过期的键值，与setDE配合使用;<br>
     * 此方法用于获取setDE设置的缓存数据;<br>
     * 当isExpired为true时，说明key已经过期，需要更新;<br>
     * 更新数据时配合isLock和lock方法，防止惊群现象发生
     * @param string $key       键名
     * @param boolean $isExpired 是否已经过期
     * @return mixed|false      键值,失败返回false
     */
    public function getDE($key, &$isExpired = null) {
        try {
            $timeValue = self::getValue($this->handler->get(self::timeKey($key)));
            $isExpired = self::isExpiredDE($timeValue);
            return self::getValue($this->handler->get($key));
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 删除键值
     * @param string $key   键名
     * @return boolean      是否成功
     */
    public function del($key) {
        try {
            $this->handler->delete(self::timeKey($key));
            $ret = $this->handler->delete($key);
            if (!$ret && $this->handler->get($key) === false) {
                return true;
            }
            return $ret;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 是否存在键值
     * @param string $key   键名
     * @return boolean      是否存在
     */
    public function has($key) {
        try {
            $value = $this->handler->get($key);
            //key存在
            if ($value !== false) {
                $timeValue = self::getValue($this->handler->get(self::timeKey($key)));
                //已过期
                if ($timeValue !== false && self::isExpired($timeValue)) {
                    return false;
                }
                return true;
            }
            return false;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 判断延迟过期的键值理论上是否存在
     * @param string $key   键名
     * @return boolean      是否存在
     */
    public function hasDE($key) {
        try {
            $value = $this->handler->get($key);
            if ($value !== false) {
                $timeValue = self::getValue($this->handler->get(self::timeKey($key)));
                //已过期
                if ($timeValue !== false && self::isExpiredDE($timeValue)) {
                    return false;
                }
                return true;
            }
            return false;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 获取生存剩余时间
     * @param string $key   键名
     * @return int|false    生存剩余时间(单位:秒) -1表示永不过期,-2表示键值不存在,失败返回false
     */
    public function ttl($key) {
        try {
            $timeValue = self::getValue($this->handler->get(self::timeKey($key)));
            if (isset($timeValue['expire_time'])) {
                $ttl = $timeValue['expire_time'] - time();
                return $ttl > 0 ? $ttl : -2;
            }
            $value = $this->handler->get($key);
            if ($value === false) {
                return -2;
            } else {
                return -1;
            }
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 获取延迟过期的键值理论生存剩余时间
     * @param string $key   键名
     * @return int|false    生存剩余时间(单位:秒) -1表示永不过期,-2表示键值不存在,失败返回false
     */
    public function ttlDE($key) {
        try {
            $timeValue = self::getValue($this->handler->get(self::timeKey($key)));
            if (isset($timeValue['expire_time'])) {
                if (isset($timeValue['delay_time'])) {
                    $ttl = $timeValue['expire_time'] - $timeValue['delay_time'] - time();
                } else {
                    $ttl = $timeValue['expire_time'] - time();
                }
                return $ttl > 0 ? $ttl : -2;
            }
            $value = $this->handler->get($key);
            if ($value === false) {
                return -2;
            } else {
                return -1;
            }
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 设置过期时间
     * @param string $key   键名
     * @param int $time     过期时间(单位:秒)。不大于0，则设为永不过期
     * @return boolean      是否成功
     */
    public function expire($key, $time) {
        try {
            $value = $this->handler->get($key);
            //值不存在,直接返回 false
            if ($value === false) {
                return false;
            }
            //设为永不过期
            if ($time <= 0) {
                if ($this->handler->set($key, $value, $this->compress($value))) {
                    $timeValue = $this->handler->get(self::timeKey($key));
                    //timeKey 存在
                    if ($timeValue !== false) {
                        return $this->handler->delete(self::timeKey($key));
                    }
                    return true;
                }
                return false;
            }
            //设置新的过期时间
            $exTime = $time <= 2592000 ? $time : time() + $time;
            if ($this->handler->set($key, $value, $this->compress($value), $exTime)) {
                $this->handler->set(self::timeKey($key), self::setValue(['expire_time' => time() + $time]), 0, $exTime);
                return true;
            }
            return false;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 以延迟过期的方式设置过期时间
     * @param string $key    键名
     * @param int $time      过期时间(单位:秒)。不大于0，则设为永不过期
     * @param int $delayTime 延迟过期时间，如果未设置，则使用配置中的设置
     * @return boolean       是否成功
     */
    public function expireDE($key, $time, $delayTime = null) {
        try {
            $value = $this->handler->get($key);
            //值不存在,直接返回 false
            if ($value === false) {
                return false;
            }
            //设为永不过期
            if ($time <= 0) {
                if ($this->handler->set($key, $value, $this->compress($value))) {
                    $timeValue = $this->handler->get(self::timeKey($key));
                    if ($timeValue !== false) {
                        return $this->handler->delete(self::timeKey($key));
                    }
                    return true;
                }
                return false;
            }
            if ($this->hasDE($key)) {
                $delayTime = $this->getDelayTime($delayTime);
                $exTime = $time + $delayTime <= 2592000 ? $time + $delayTime : time() + $time + $delayTime;
                //设置新的过期时间
                if ($this->handler->set($key, $value, $this->compress($value), $exTime)) {
                    $this->handler->set(self::timeKey($key), self::setValue(['expire_time' => time() + $time + $delayTime, 'delay_time' => $delayTime]), 0, $exTime);
                    return true;
                }
            }
            return false;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 移除指定键值的过期时间
     * @param string $key   键名
     * @return boolean      是否成功
     */
    public function persist($key) {
        try {
            $value = $this->handler->get($key);
            if ($value === false) {
                return false;
            }
            if ($this->handler->set($key, $value)) {
                $timeValue = $this->handler->get(self::timeKey($key));
                if ($timeValue !== false) {
                    return $this->handler->delete(self::timeKey($key));
                }
                return true;
            }
            return false;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 对指定键名设置锁标记（此锁并不对键值做修改限制,仅为键名的锁标记）;<br>
     * 此方法可用于防止惊群现象发生,在get方法获取键值无效时,先判断键名是否有锁标记,<br>
     * 如果已加锁,则不获取新值;<br>
     * 如果未加锁,则先设置锁，若设置失败说明锁已存在，若设置成功则获取新值,设置新的缓存
     * @param string $key   键名
     * @param int $time     加锁时间
     * @return boolean      是否成功
     */
    public function lock($key, $time = 60) {
        try {
            return $this->setnx(self::lockKey($key), 1, $time);
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 判断键名是否有锁标记;<br>
     * 此方法可用于防止惊群现象发生,在get方法获取键值无效时,判断键名是否有锁标记
     * @param string $key   键名
     * @return boolean      是否加锁
     */
    public function isLock($key) {
        try {
            return (boolean) $this->handler->get(self::lockKey($key));
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 对指定键名移除锁标记
     * @param string $key   键名
     * @return boolean      是否成功
     */
    public function unlock($key) {
        try {
            return $this->del(self::lockKey($key));
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 递增
     * @param string $key   键名
     * @param int $step     递增步长
     * @return int|false    递增后的值,失败返回false
     */
    public function incr($key, $step = 1) {
        if (!is_int($step)) {
            return false;
        }
        try {
            $value = $this->handler->get($key);
            if ($value === false) {
                if ($this->handler->set($key, $value = $step, 0)) {
                    return $value;
                }
            } else {
                //memcache会将数字存储为字符串
                if (!is_numeric($value)) {
                    return false;
                }
                $timeValue = self::getValue($this->handler->get(self::timeKey($key)));
                //未设置过期时间或未过期
                if ($timeValue === false || (isset($timeValue['expire_time']) && $timeValue['expire_time'] > time())) {
                    if ($step > 0) {
                        return $this->handler->increment($key, $step);
                    } else {
                        return $this->handler->decrement($key, -$step);
                    }
                }
                //已过期,重新设置
                if ($this->handler->set($key, $value = $step, 0)) {
                    return $value;
                }
            }
            return false;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 浮点数递增
     * @param string $key   键名
     * @param float $float  递增步长
     * @return float|false  递增后的值,失败返回false
     */
    public function incrByFloat($key, $float) {
        if (!is_numeric($float)) {
            return false;
        }
        try {
            $value = $this->handler->get($key);
            if ($value === false) {
                if ($this->handler->set($key, $value = $float, 0)) {
                    return $value;
                }
            } else {
                if (!is_numeric($value)) {
                    return false;
                }
                $timeValue = self::getValue($this->handler->get(self::timeKey($key)));
                //未设置过期时间
                if ($timeValue === false) {
                    if ($this->handler->set($key, $value += $float, 0)) {
                        return $value;
                    }
                    return false;
                }
                //未过期
                elseif (isset($timeValue['expire_time']) && $timeValue['expire_time'] > time()) {
                    $exTime = $timeValue['expire_time'] - time() <= 2592000 ? $timeValue['expire_time'] - time() : $timeValue['expire_time'];
                    if ($this->handler->set($key, $value += $float, 0, $exTime)) {
                        return $value;
                    }
                    return false;
                }
                //已过期,重新设置
                if ($this->handler->set($key, $value = $float, 0)) {
                    return $value;
                }
            }
            return false;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 递减
     * @param string $key   键名
     * @param int $step     递减步长
     * @return int|false    递减后的值,失败返回false
     */
    public function decr($key, $step = 1) {
        if (!is_int($step)) {
            return false;
        }
        try {
            $value = $this->handler->get($key);
            if ($value === false) {
                if ($this->handler->set($key, $value = -$step, 0)) {
                    return $value;
                }
            } else {
                //memcache会将数字存储为字符串
                if (!is_numeric($value)) {
                    return false;
                }
                $timeValue = self::getValue($this->handler->get(self::timeKey($key)));
                //未设置过期时间或未过期
                if ($timeValue === false || (isset($timeValue['expire_time']) && $timeValue['expire_time'] > time())) {
                    //memcache 新的元素的值不会小于0
                    if ($value < 0 || ($step > 0 && $value < $step)) {
                        if ($this->handler->set($key, $value -= $step, 0)) {
                            return $value;
                        }
                    } else {
                        if ($step > 0) {
                            $ret = $this->handler->decrement($key, $step);
                            return $ret;
                        } else {
                            return $this->handler->increment($key, -$step);
                        }
                    }
                }
                //已过期,重新设置
                if ($this->handler->set($key, $value = $step, 0)) {
                    return $value;
                }
            }
            return false;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 批量设置键值
     * @param array $sets   键值数组
     * @return boolean      是否成功
     */
    public function mSet($sets) {
        try {
            $oldSets = [];
            $status = true;
            foreach ($sets as $key => $value) {
                $value = self::setValue($value);
                $oldSets[$key] = $this->handler->get($key);
                $status = $this->handler->set($key, $value, $this->compress($value));
                if (!$status) {
                    break;
                }
            }
            //如果失败，尝试回滚，但不保证成功
            if (!$status) {
                foreach ($oldSets as $key => $value) {
                    if ($value === false) {
                        $this->handler->del($key);
                    } else {
                        $this->handler->set($key, $value, $this->compress($value));
                    }
                }
            }
            return $status;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 批量设置键值(当键名不存在时);<br>
     * 只有当键值全部设置成功时,才返回true,否则返回false并尝试回滚
     * @param array $sets   键值数组
     * @return boolean      是否成功
     */
    public function mSetNX($sets) {
        try {
            $keys = [];
            $status = true;
            foreach ($sets as $key => $value) {
                $value = self::setValue($value);
                $status = $this->handler->add($key, $value, $this->compress($value));
                if ($status) {
                    $keys[] = $key;
                } else {
                    break;
                }
            }
            //如果失败，尝试回滚，但不保证成功
            if (!$status) {
                foreach ($keys as $key) {
                    $this->handler->delete($key);
                }
            }
            return $status;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 批量获取键值
     * @param array $keys   键名数组
     * @return array|false  键值数组,失败返回false
     */
    public function mGet($keys) {
        try {
            $ret = [];
            $values = $this->handler->get($keys);
            foreach ($keys as $key) {
                $ret[$key] = isset($values[$key]) ? self::getValue($values[$key]) : false;
            }
            return $ret;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 批量判断键值是否存在
     * @param array $keys   键名数组
     * @return array  返回存在的keys
     */
    public function mHas($keys) {
        try {
            $hasKeys = [];
            foreach ($keys as $key) {
                if ($this->has($key)) {
                    $hasKeys[] = $key;
                }
            }
            return $hasKeys;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

    /**
     * 批量删除键值
     * @param array $keys   键名数组
     * @return boolean  是否成功
     */
    public function mDel($keys) {
        try {
            $status = true;
            foreach ($keys as $key) {
                $ret = $this->del($key);
                //如果有删除失败，则整个批量删除判断为失败，但继续执行完所有删除操作
                if (!$ret) {
                    $status = false;
                }
            }
            return $status;
        } catch (Exception $ex) {
            self::exception($ex);
            //连接状态置为false
            $this->isConnected = false;
        }
        return false;
    }

}
