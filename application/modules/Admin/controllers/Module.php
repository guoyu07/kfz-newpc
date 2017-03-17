<?php
use http\Response;
use services\Data;
use services\Error;
use services\Module;

/**
 * Class ModuleController
 * 模块操作控制器，接口调用
 * @author  liubang <liubang@kongfz.com>
 */
class ModuleController extends AdminBaseController {

    public function init() {
        parent::init();

        //只能通过ajax访问
        if (!$this->getRequest()->isXmlHttpRequest()) {
            Response::_404();
        }
    }

    /**
     * 模块设置，设置标题/副标题
     *
     * 前端调用地址 :/admin/module/setModule
     * 参数 :
     *      moduleId    int     运营模块编号
     *      title       string  运营模块标题
     *      subTitle    string  运营模块副标题
     *      url         string   运营模块更多url
     */
    public function setModuleAction() {
        $moduleId = $this->getRequest()->getPost('moduleId', 0);
        $title = $this->getRequest()->getPost('title', '');
        $subtitle = $this->getRequest()->getPost('subtitle', '');
        $url = $this->getRequest()->getPost('url', '');
        if (!$moduleId) {
            Response::json(false, '模块号不能为空');
        }
        if (empty($title) && empty($subtitle)) {
            Response::json(false, '标题不能为空');
        }
        if (ModuleModel::singleton()->setModuleInfo($moduleId, $title, $subtitle, $url)) {
            Response::json(true);
        } else {
            Response::json(false, Error::getMessage());
        }

    }

    /**
     * 设置模块报警|数据日报接收人
     *
     * 前端调用地址 :/admin/module/setModuleReciever
     * 参数 :
     *      moduleId    int         运营模块编号
     *      adminId     int         运营人员编号
     *      type        int(1|2|3)  类型：　１ -> 邮件报警, 2 -> 短信报警, 3 -> 数据日报接收
     */
    public function setModuleRecieverAction() {

        $adminId = $this->getRequest()->getPost('adminId', 0);
        $moduleId = $this->getRequest()->getPost('moduleId', 0);
        $alarmConditionId = $this->getRequest()->getPost('alarmConditionId', 0);
        $type = $this->getRequest()->getPost('type', AlarmModel::TYPE_EMAIL);
        if (!$adminId || !$moduleId) {
            Response::json(false, '参数错误');
        }
        if (Module::setAlarmReciever($moduleId, $adminId, $alarmConditionId, $type)) {
            Response::json(true, '操作成功');
        } else {
            Response::json(false, Error::getMessage());
        }

    }

    /**
     * 获取模块报警|数据日报接收人
     *
     * 前端调用地址 :/admin/module/getModuleReciever
     * 参数 :
     *      moduleId    int         运营模块编号
     *      type        int(1|2|3)  类型：　１ -> 邮件报警, 2 -> 短信报警, 3 -> 数据日报接收
     */
    public function getModuleRecieverAction() {

        $moduleId = $this->getRequest()->getPost('moduleId', 0);
        $type = $this->getRequest()->getPost('type', AlarmModel::TYPE_EMAIL);
        if (!$moduleId) {
            Response::json(false, '参数错误');
        }
        $result = Module::getAlarmReciver($moduleId, $type);

        if (null === $result) {
            Response::json(false, Error::getMessage());
        } else {
            Response::json(true, $result);
        }
    }

    /**
     * 修改模块显示或隐藏的状态
     *
     * 前端调用地址 :/admin/module/changeModuleShowStatus
     * 参数 :
     *      moduleId    int
     *      isHide      int[0|1]
     */
    public function changeModuleShowStatusAction() {
        $moduleId = $this->_request->getPost("moduleId", 0);
        $isHide = $this->_request->getPost('isHide', 0);
        if (!$moduleId) {
            Response::json(false, '参数错误');
        }
        if (ModuleModel::singleton()->changeModuleShowStatus($moduleId, $isHide)) {
            Response::json(true);
        } else {
            Response::json(false, Error::getMessage());
        }
    }

    /**
     * 获取模块数据
     *
     * 前端调用地址 :/admin/module/getData
     * 参数 :
     *      moduleId       int      运营模块编号
     *      params {
     *          'isDefault': [0|1]   是否为默认
     *          'page': {
     *              'maxRowPerPage': //每页最多条数
     *              'requirePage':   //当前请求的页数
     *           },
     *          'status': [
     *              'draft', 'published', 'ended'
     *           ],
     *          'regularlyStart': [
     *              'progressing', 'countDown'
     *           ],
     *          'regularlyEnd': [
     *              'countDown', 'break'
     *           ],
     *          'keyWords': {
     *              'key1': 'xx',
     *              'key2': 'xx',
     *              ...
     *           },
     *          'order': {
     *              'updateTime': 'ASC/DESC',
     *              'order': 'ASC/DESC'
     *           }
     *
     *      }
     */
    public function getDataAction() {

        $moduleId = $this->getRequest()->getPost('moduleId', 0);
        $params = $this->getRequest()->getPost('params', []);

        if (!$moduleId) {
            Response::json(false, '参数错误');
        }

        $count = OperationConfigModel::singleton()->getCount($moduleId, $params);
        // pager
        $maxRowPerPage = !empty($params['page']['maxRowPerPage']) ? $params['page']['maxRowPerPage'] : 10;
        // 总页数
        $totalPage = ceil($count / $maxRowPerPage);
        $requirePage = !empty($params['page']['requirePage']) ? $params['page']['requirePage'] : 1;

        if ($requirePage > $totalPage) {
            $requirePage = $totalPage;
        }

        $params['limit'] = [(($requirePage == 0 ? 1 : $requirePage) - 1) * $maxRowPerPage, $maxRowPerPage];
        $result = Data::getData($moduleId, $params);

        if (false !== $result) {
            Response::json(true, '', $result, 0, [
                'page' => [
                    'pageNumber' => $requirePage,
                    'total'      => $count,
                    'pageSize'   => $maxRowPerPage
                ]
            ]);
        } else {
            Response::json(false, Error::getMessage());
        }

    }

    /**
     * 编辑模块数据
     *
     * 前端调用地址 :/admin/module/editModuleData
     * 参数 :
     *      moduleId    int     运营模块编号
     *      data        array   具体的数据内容
     */
    public function editModuleDataAction() {
        $moduleId = $this->getRequest()->getPost('moduleId', 0);
        $data = $this->getRequest()->getPost('data', []);
        if (!$moduleId || empty($data)) {
            Response::json(false, '参数错误');
        }
        //$data['moduleId'] = $moduleId;
        $moduleInfo = ModuleModel::singleton()->getModuleInfoById($moduleId);
        if (empty($moduleInfo)) {
            Response::json(false, '操作失败');
        }
        $result = Data::editData($moduleInfo, $data);
        Response::json((bool)$result, Error::getMessage());
    }

    /**
     * 模块数据修改排序
     *
     * 前端调用地址 :/admin/module/sortModuleData
     * 参数 :
     *      moduleId    int     运营模块编号
     *      confId      int     要排序的数据运营配置项id
     *      pos         int     修改后的数据顺序位置
     */
    public function sortModuleDataAction() {
        $moduleId = $this->getRequest()->getPost('moduleId', 0);
        $confId = $this->getRequest()->getPost("confId", 0);
        $position = $this->getRequest()->getPost('pos', 0);
        if (!$moduleId || !$confId || !$position) {
            Response::json(false, '参数错误');
        }
        if (OperationConfigModel::singleton()->sort($moduleId, $confId, $position)) {
            Response::json(true);
        } else {
            Response::json(false, Error::getMessage());
        }
    }


    /**
     * 添加模块数据
     *
     * 前端调用地址：/admin/module/addModuleData
     * 参数 :
     *      moduleId    int    运营模块编号
     *      data        array  具体数据内容
     *
     */
    public function addModuleDataAction() {

        $moduleId = $this->getRequest()->getPost('moduleId', 0);
        $data = $this->getRequest()->getPost('data', []);

        if (!$moduleId || empty($data)) {
            Response::json(false, '参数错误');
        }
        $moduleInfo = ModuleModel::singleton()->getModuleInfoById($moduleId);
        if (empty($moduleInfo)) {
            Response::json(false, Error::getMessage());
        }
        if (Data::addData($moduleInfo, $data)) {
            Response::json(true, '操作成功');
        }
        Response::json(false, Error::getMessage());
    }

    /**
     * 删除运营数据
     *
     * 前端调用地址 :/admin/module/deleteModuleData
     * 参数 :
     *      moduleId    int     运营区编号
     *      confId      int     运营数据id
     */
    public function deleteModuleDataAction() {

        $moduleId = $this->getRequest()->getPost('moduleId', 0);
        $confId = $this->getRequest()->getPost('confId', 0);
        if (!$moduleId || !$confId) {
            Response::json(false, '参数错误');
        }
        if (OperationConfigModel::singleton()->delete($moduleId, $confId)) {
            //删除索引表中的对应数据
            SearchModel::singleton()->deleteIndex($confId);
            Response::json(true);
        }
        Response::json(false, Error::getMessage());
    }

    /**
     * 设置通栏广告位置显示隐藏
     *
     * 前端调用地址 :/admin/module/editTonglan
     * 参数 :
     *      moduleId    int     运营模块编号
     *      id          int     通栏位置编号
     *      isHide      int     是否显示（0显示  1隐藏）
     */
    public function editTonglanAction() {

        $moduleId = $this->getRequest()->getPost('moduleId', 0);
        $id = $this->getRequest()->getPost('id', 0);
        $isHide = $this->getRequest()->getPost('isHide', 0);
        if (!$moduleId || !$id) {
            Response::json(false, '参数错误');
        }
        if (ModuleModel::singleton()->changeTonglanParams($moduleId, $id, $isHide)) {
            Response::json(true);
        }
        Response::json(false, Error::getMessage());
    }

    /**
     * 获取模块信息
     *
     * 前端调用地址 :/admin/module/getModuleInfo
     * 参数 :
     *      moduleId    int     模块编号
     */
    public function getModuleInfoAction() {

        $moduleId = $this->getRequest()->getPost('moduleId', 0);

        if (!$moduleId) {
            Response::json(false, '参数错误');
        }
        $moduleInfo = ModuleModel::singleton()->getModuleInfoById($moduleId);
        if (empty($moduleInfo)) {
            Response::json(false, Error::getMessage());
        }
        Response::json(true, '', $moduleInfo, 0, []);
    }

    /**
     * 修改模块展示
     *
     * 前端调用地址 :/admin/module/changeModuleShowType
     * 参数 :
     *      moduleId    int
     *      showType    int[0|1]   图文轮播|纯图轮播   或   tab随机排序|tab固定排序
     */
    public function changeModuleShowTypeAction() {
        $moduleId = $this->_request->getPost("moduleId", 0);
        $showType = $this->_request->getPost('showType', 0);
        if (!$moduleId) {
            Response::json(false, '参数错误');
        }
        if (ModuleModel::singleton()->changeModuleShowType($moduleId, $showType)) {
            Response::json(true);
        } else {
            Response::json(false, Error::getMessage());
        }
    }

    /**
     * 添加子模块
     *
     * 前端调用地址 :/admin/module/addModule
     * 参数 :
     *      moduleId     int    父模块编号
     *      data         array  具体数据内容
     */
    public function addModuleAction() {
        $pid = $this->_request->getPost('moduleId', 0);
        $data = $this->getRequest()->getPost('data', []);
        if (!$pid || empty($data)) {
            Response::json(false, '参数错误');
        }
        if (ModuleModel::singleton()->addModuleInfo($pid, $data)) {
            Response::json(true);
        } else {
            Response::json(false, Error::getMessage());
        }
    }

    /**
     * 编辑模块\修改模块排序
     *
     * 前端调用地址 :/admin/module/editModule
     * 参数 :
     *      moduleId     int    模块编号
     *      data         array  具体数据内容
     */
    public function editModuleAction() {
        $moduleId = $this->_request->getPost('moduleId', 0);
        $data = $this->getRequest()->getPost('data', []);
        if (!$moduleId || empty($data)) {
            Response::json(false, '参数错误');
        }
        if (ModuleModel::singleton()->editModuleInfo($moduleId, $data)) {
            Response::json(true);
        } else {
            Response::json(false, Error::getMessage());
        }

    }

    /**
     * 删除模块
     *
     * 前端调用地址 :/admin/module/deleteModule
     * 参数 :
     *      moduleId     int    模块编号
     */
    public function deleteModuleAction() {
        $moduleId = $this->_request->getPost('moduleId', 0);
        if (!$moduleId) {
            Response::json(false, '参数错误');
        }
        if (ModuleModel::singleton()->deleteModule($moduleId)) {
            Response::json(true);
        } else {
            Response::json(false, Error::getMessage());
        }
    }

    /**
     * 设置同步模块数据
     *
     * 前端调用地址：/admin/module/setSyncModule
     * 参数：
     *      moduleId    int 操作的模块id
     *      switch      int [0|1] 开关
     */
    public function setSyncModuleAction() {
        $moduleId = $this->_request->getPost("moduleId", 0);
        $switch = $this->_request->getPost('switch', 0);
        if (!$moduleId) {
            Response::json(false, '参数错误');
        }
        $result = ModuleModel::singleton()->setModuleSyncData($moduleId, $switch);
        if ($result) {
            Response::json(true);
        } else {
            Response::json(false, Error::getMessage());
        }

    }

    /**
     * 获取模块同步的模块信息
     */
    public function getModuleSyncInfoAction() {
        $moduleId = $this->_request->getPost('moduleId', 0);
        if (!$moduleId) {
            Response::json(false, '参数错误');
        }
        if (isset(\conf\Module::$moduleSyncMap[$moduleId])) {
            $moduleInfo = ModuleModel::singleton()->getModuleInfoById($moduleId);
            $moduleInfo['syncModule'] = ModuleModel::singleton()
                ->getModuleInfoById(\conf\Module::$moduleSyncMap[$moduleId]['syncModuleId']);
            $moduleInfo['syncModule']['syncModuleArea'] = \conf\Module::$moduleSyncMap[$moduleId]['syncModuleArea'];
            //防止模块没有初始化而导致js报错
            if (!isset($moduleId['params']['sync']['switch'])) {
                ModuleModel::singleton()->setModuleSyncData($moduleId, 0);
            }
            Response::json(true, '', $moduleInfo);
        }
        Response::json(false, '您操作的模块不支持数据同步操作，如有疑问，请联系系统管理员');
    }

    /**
     * 根据父模块id获取父子模块数据
     */
    public function getMainSubModuleInfoAction() {
        $params = $this->_request->getPost();
        if (!isset($params) || empty($params['mainModuleId'])) {
            Response::json(false, '参数错误');
        } else {
            $data = ModuleModel::singleton()->getModuleInfoWithSonById($params['mainModuleId']);
            Response::json(true, [], $data);
        }
    }

    /**
     * 根据isbn号获取商品信息
     *
     * 前端调用接口 :/admin/module/getItemInfoByIsbn
     * 参数：
     *      isbn   int  isbn号
     */
    public function getItemInfoByIsbnAction() {
        $isbn = $this->_request->getPost('isbn', 0);

        if (!$isbn) {
            Response::json(false, '参数错误');
        } else {
            //$site =\Yaf\Registry::get('g_config')->site->toArray();
            //\api\RpcClient::call($site['booklib'] . 'IsbnV2Interface/getBookByIsbnForBack/');

            Response::json(true, [], [
                'isbn' => '9787533256739',
                'itemName' => '红楼梦',
                'itemImg' => 'http://www.kfzimg.com/G03/M00/FB/1F/pYYBAFUd81mAcs_tAAJva0fErJ0222_n.jpg',
                'author' => '曹雪芹',
                'press' => '人民教育出版社',
                'jianjie' => '简介',
                'year' => '2014-02-05',
                'strictBindNum' => '123',
                'unStrictBindNum' => '21',
                'price' => '1.00',
                'minPrice' => '12.00',
                'maxPrice' => '23.12'
            ]);
        }
    }
}