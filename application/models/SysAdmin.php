<?php

use kongfz\Model;
use storage\Db;

/**
 * Class SysAdminModel
 * 运营人员管理，暂定为独立的表，可能共用综合管理后台的用户表
 * @author  liubang <liubang@kongfz.com>
 */
class SysAdminModel extends Model {
    /** @var null | \Medoo\Medoo */
    private $db = null;


    use \kongfz\traits\Singleton;

    /**
     * @return \kongfz\traits\Singleton | SysAdminModel
     */
    public static function singleton() {
        return self::instance();
    }

    private function _init_() {
        $this->db = Db::factory('adminMaster');
    }

    /**
     * @param $data
     */
    public function addUser($data) {

    }

    /**
     * 查询用户基本信息
     * @param $adminId
     * @return array|bool
     */
    public function getUserInfoById($adminId) {
        $adminId = (int)$adminId;
        if ($adminId <= 0) {
            return false;
        }

        $userInfo = $this->db->select('sysAdmin',
            [
                "sysAdmin.adminId", "sysAdmin.username", "sysAdmin.email", "sysAdmin.mobile", "sysAdmin.realName",
                "sysAdmin.addTime", "sysAdmin.lastLogin", "sysAdmin.lastIp", "sysAdmin.status", "sysAdmin.isDelete",
            ],
            ['adminId' => $adminId]
        );

        return $userInfo;
    }

    /**
     * 查询用户信息（带权限）
     * @param $adminId
     * @return array|bool
     */
    public function getUserInfoWithPrivilagesById($adminId) {
        $adminId = (int)$adminId;
        if ($adminId <= 0) {
            return false;
        }

        $userInfo = $this->db->select('sysAdmin',
            ["[>]userMid" => "adminId", "[>]privileges" => "pcode"],
            [
                "sysAdmin.adminId", "sysAdmin.username", "sysAdmin.email", "sysAdmin.mobile", "sysAdmin.realName",
                "sysAdmin.addTime", "sysAdmin.lastLogin", "sysAdmin.lastIp", "sysAdmin.status", "sysAdmin.isDelete",
                "privileges.pcode", "privileges.pdesc"
            ],
            ["sysAdmin.adminId" => $adminId]
        );

        $result = [];
        if (is_array($userInfo) && !empty($userInfo)) {
            $result = $userInfo[0];
            $result['privileges'] = [];
            unset($result['pcode']);
            unset($result['pdesc']);
            foreach ($userInfo as $row) {
                if (!empty($row['pcode']) && !empty($row['pdesc'])) {
                    $result['privileges'][$row['pcode']] = $row['pdesc'];
                }
            }
        }
        return $result;
    }


}