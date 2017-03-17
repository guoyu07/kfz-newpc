<?php

use kongfz\Controller;

class UserController extends Controller
{
    public function initDataAction()
    {
        $db = storage\Db::factory('adminMaster');
        //$db->debug();
        $res = $db->insert('sysAdmin', [
            'adminId'   => '3',
            'username'  => 'liubang',
            'password'  => \services\Auth::encript('liubang'),
            'email'     => 'liubang@kongfz.com',
            'mobile'    => '18515388535',
            'realName'  => '刘邦',
            'addTime'   => date("Y-m-d H:i:s", time()),
            'lastLogin' => date("Y-m-d H:i:s", time()),
            'lastIp'    => "127.0.0.1",
            'departmentId' => 1,
            'department'=> '技术部',
            'positionType' => 'php debuger',
            'positionName' => 'just php debuger',
        ]);
        var_dump($res);
    }


    public function initPrivilegesAction()
    {
        $db = storage\Db::factory('adminMaster');
        for ($i = 1; $i < 100; $i++) {
            $db->insert('privileges', [
                'pcode' => $i,
                'pdesc' => '这是第' . $i . '种权限'
            ]);
        }
    }

    public function initUserMidAction()
    {
        $db = storage\Db::factory('adminMaster');

        for ($i = 5; $i < 20; $i++) {
            $db->insert('userMid', [
                'adminId'   => 2,
                'pcode'     => $i
            ]);
        }
    }

    public function testGetUserInfoAction()
    {
        $sysAdminModel = SysAdminModel::singleton();
        $userInfo = $sysAdminModel->getUserInfoWithPrivilagesById(3);

        print_r($userInfo);
    }
}
