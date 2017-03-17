<?php

namespace storage;

use kongfz\Exception;
use Medoo\Medoo;
use Yaf\Registry;

class Db {
    /**
     * @var Medoo[]
     */
    private static $_ins = [];

    public static function factory($db) {
        $key = md5($db);
        if (!isset(static::$_ins[$key]) || !static::$_ins[$key] instanceof Medoo) {
            $config = Registry::get('g_config')->toArray();
            if (!isset($config['db'][$db])) {
                throw new Exception("在配置中找不到{$db}");
            }

            $dbconfig = [
                'database_type' => !empty($config['db'][$db]['type']) ? $config['db'][$db]['type'] : 'mysql',
                'database_name' => $config['db'][$db]['name'],
                'server'        => $config['db'][$db]['host'],
                'username'      => $config['db'][$db]['user'],
                'password'      => $config['db'][$db]['pass'],
                'charset'       => !empty($config['db'][$db]['char']) ? $config['db'][$db]['char'] : 'utf8',
                'port'          => !empty($config['db'][$db]['port']) ? $config['db'][$db]['port'] : '3306',
                'option'        => [
                    \PDO::ATTR_CASE         => \PDO::CASE_NATURAL,
                    \PDO::ATTR_ERRMODE      => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL
                ]
            ];
            $database = new Medoo($dbconfig);
            static::$_ins[$key] = $database;
        }

        return static::$_ins[$key];
    }
}
