<?php

use kongfz\Model;
use storage\Db;

/**
 * Class PrivilegesModel
 * 运营数据权限管理，暂时考虑用数据文件存储，可能会独立成一张数据表
 * @author  liubang <liubang@kongfz.com>
 */
class PrivilegesModel extends Model {

    use \kongfz\traits\Singleton;

    /** @var null | \Medoo\Medoo */
    private $privilegeDb = null;


    /**
     * @return \kongfz\traits\Singleton | PrivilegesModel
     */
    public static function singleton() {
        return self::instance();
    }

    private function _init_() {
        $this->privilegeDb = Db::factory('adminMaster');
    }


}