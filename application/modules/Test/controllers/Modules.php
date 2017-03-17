<?php


use kongfz\Controller;

class ModulesController extends Controller
{

    public function initModuleDataAction() {
        $data = [
            [
                'moduleId' => '1',
                'pid'      => '0',
                'title'    => '好书推荐',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '2',
                'pid'      => '0',
                'title'    => '古籍',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '3',
                'pid'      => '2',
                'title'    => '拍卖大卖家',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '4',
                'pid'      => '2',
                'title'    => '拍品推荐',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '5',
                'pid'      => '2',
                'title'    => '书店推荐',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '6',
                'pid'      => '0',
                'title'    => '民国书刊',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '7',
                'pid'      => '6',
                'title'    => '拍卖大卖家',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '8',
                'pid'      => '6',
                'title'    => '拍品推荐',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '9',
                'pid'      => '6',
                'title'    => '书店推荐',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '10',
                'pid'      => '0',
                'title'    => '艺术品',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '11',
                'pid'      => '10',
                'title'    => '艺术品分类',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '12',
                'pid'      => '10',
                'title'    => '拍品推荐',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '13',
                'pid'      => '10',
                'title'    => '专场推荐',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '14',
                'pid'      => '0',
                'title'    => '轮播',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '15',
                'pid'      => '0',
                'title'    => '广告',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
            [
                'moduleId' => '16',
                'pid'      => '0',
                'title'    => '通栏广告',
                'showMoreUrl' => '',
                'datasource' => '',
                'addTime' => time(),
                'updateTime' => time()
            ],
        ];

        $db = storage\Db::factory('adminMaster');
        foreach ($data as $row) {
            $db->insert('module', $row);
        }
    }

    public function testGetModuleInfoWithSonByIdAction()
    {
        $module = ModuleModel::singleton();

        $result = $module->getModuleInfoWithSonById(10, false);

        print_r($result);
    }

    public function testGetParentByIdAction()
    {
        $module = ModuleModel::singleton();

        $result = $module->getParentById(15);

        print_r($result);
    }
    
    public function testAAction() {
    	$moduleId = $this->_request->get('moduleId');
    	
    	
    	if (empty($moduleId)) {
    		die("error");
    	}
    	
    	$result = ModuleModel::singleton()->getModuleCurrentOperateData($moduleId, 100, true);
    	print_r($result);
    	
    	return false;
    }
}