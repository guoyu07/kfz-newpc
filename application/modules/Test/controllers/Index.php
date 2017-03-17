<?php

class IndexController extends \kongfz\Controller
{

    public function indexAction()
    {
        $this->display('index');
    }

    public function testDbAction()
    {
        $result = storage\Db::factory('adminMaster')->select('module',"*");

        var_dump($result);

        return false;
    }

    public function testReSortDataAction() {

        $db = storage\Db::factory('adminMaster');

        $moduleId = $this->_request->get('moduleId', 0);
        if (!$moduleId) {
            die("参数错误");
        }
        //$db->debug();
        $datas = $db->select('operationConfig', '*', [
            'AND' => [
                'moduleId' => $moduleId,
                'isDelete' => 0

            ],
            'ORDER' => [
                'order' => 'ASC'
            ]
        ]);

        for ($i = 0; $i < count($datas); $i++) {
            $db->update('operationConfig', ['order' => $i + 1], [
                'confId' => $datas[$i]['confId']
            ]);
        }

        echo 'OK';
        exit;

    }

    public function testGetOperationDataAction() {

        $data = \services\Data::singleton();

        $result = $data->getModuleCurrentOperateData(14, 2, true);

        print_r($result);die;
    }

    public function testBAction() {
        $data = ModuleModel::singleton()->getModuleInfoWithSonById(24);
        print_r($data);
        die;
    }
}