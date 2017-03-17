<?php

use conf\Module;
use kongfz\Model;
use services\Error;
use services\Log;
use storage\Db;

/**
 * Class ModuleModel
 * 运营模块信息
 * @author  liubang <liubang@kongfz.com>
 */
class ModuleModel extends Model {

    /** @var int 推荐书店类型 */
    const DATA_SRC_RECOMMEND_SHOP_INFO = 1;

    /** @var int 商品类型 */
    const DATA_SRC_SHOP_ITEM = 2;

    /** @var int 拍主 */
    const DATA_SRC_AUCTION_USER = 3;

    /** @var int 拍品 */
    const DATA_SRC_AUCTION_ITEM = 4;

    /** @var int 轮播图 */
    const DATA_SRC_LUNBO_IMG = 5;

    /** @var int 广告位图 */
    const DATA_SRC_ADVERTISE_IMG = 6;

    /** @var int 书店类型 */
    const DATA_SRC_SHOP_INFO = 7;

    /** @var int isbn */
    const DATA_SRC_ISBN = 8;

    /** @var int 超链接 */
    const DATA_SRC_LINKS = 9;


    /** @var string 当前操作的库 */
    private static $database = 'adminMaster';

    /** @var string 当前操作的表 */
    private static $table = 'module';

    use \kongfz\traits\Singleton;

    /** @var null | \Medoo\Medoo */
    private $moduleDb = null;

    /**
     * @return ModuleModel
     */
    public static function singleton() {
        return self::instance();
    }

    private function _init_() {
        $this->moduleDb = Db::factory(self::$database);
    }

    /**
     * @param int $moduleId
     * @param int $switch
     *
     * @return bool
     */
    public function setModuleSyncData($moduleId, $switch = 0) {
        $old = $this->getModuleInfoById($moduleId);
        if (empty($old)) {
            Error::set('您操作的模块不存在或已删除，请刷新后重试');
            return false;
        }
        if ($switch) {
            if (!isset(\conf\Module::$moduleSyncMap[$moduleId])) {
                Error::set('您操作的模块不支持数据同步，如有疑问请联系系统管理员');
                return false;
            }
        }
        $params['params'] = $old['params'];
        $params['sync'] = [
            'switch' => $switch
        ];
        $result = $this->moduleDb->update(self::$table, ['params' => json_encode($params)], ['moduleId' => $moduleId]);
        if (false !== $result) {
            $recorder = new \services\Recorder($moduleId, 'editModule');
            $recorder->assign('moduleId', $moduleId)
                ->assign('message', \services\Recorder::diff(['params' => $params], ['params' => $old['params']]))
                ->recorde();

            return true;
        }
        return false;
    }

    /**
     * 切换模块显示或隐藏的状态
     *
     * @param int $moduleId
     * @param int $isHide
     *
     * @return bool|int
     */
    public function changeModuleShowStatus($moduleId, $isHide) {
        $moduleId = (int)$moduleId;
        $isHide = (int)$isHide;

        if (!$moduleId) {
            Error::set('moduleId不能为空');
            return false;
        }

        $old = $this->moduleDb->get(self::$table, [
            'moduleId',
            'pid',
            'title',
            'subtitle',
            'showMoreUrl',
            'datasource',
            'showType',
            'isHide',
            'isDelete',
            'addTime',
            'updateTime',
            'params'
        ], ['moduleId' => $moduleId]);
        if (empty($old) || !is_array($old)) {
            Error::set('要修改的模块不存在');
            return false;
        }

        if (false !== $this->moduleDb->update(self::$table, ['isHide' => $isHide], ['moduleId' => $moduleId])) {
            //日志
            $recorder = new \services\Recorder($moduleId, 'editModule');
            $recorder->assign('moduleId', $moduleId)
                ->assign('message', \services\Recorder::diff(['isHide' => $isHide], ['isHide' => $old['isHide']]))
                ->recorde();
            return true;
        } else {
            return false;
        }
    }


    /**
     * 设置模块
     *
     * @param int    $moduleId
     * @param string $title
     * @param string $subtitle
     * @param string $url
     *
     * @return bool|int
     */
    public function setModuleInfo($moduleId, $title = '', $subtitle = '', $url = '') {

        $moduleId = (int)$moduleId;
        if (!$moduleId) {
            Error::set('moduleId不能为空');
            return false;
        }

        if (empty($title) && empty($subtitle)) {
            Error::set('title或者subtitle不能为空');
            return false;
        }

        //获取原数据
        $old = $this->moduleDb->get(self::$table, [
            'moduleId',
            'pid',
            'title',
            'subtitle',
            'showMoreUrl',
            'datasource',
            'showType',
            'isHide',
            'isDelete',
            'addTime',
            'updateTime',
            'params'
        ], ['moduleId' => $moduleId]);
        if (empty($old)) {
            Error::set('您操作的模块不存在，请刷新页面后重试');
            return false;
        }
        $data = [];
        !empty($title) && $data['title'] = $title;
        !empty($subtitle) && $data['subtitle'] = $subtitle;
        $data['showMoreUrl'] = $url;

        $result = $this->moduleDb->update(self::$table, $data, ['moduleId' => $moduleId]);

        if ($result !== false) {
            // 添加操作日志
            $recorder = new \services\Recorder($moduleId, 'editModule');
            $recorder->assign('moduleId', $moduleId)
                ->assign('message', \services\Recorder::diff($data, $old))
                ->recorde();
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param $modules
     * @param $id
     *
     * @return array
     */
    private static function getParent($modules, $id) {
        $arr = [];
        foreach ($modules as $module) {
            if ($module['moduleId'] == $id) {
                $arr[] = $module;
                $arr = array_merge($arr, self::getParent($modules, $module['pid']));
            }
        }

        return $arr;
    }

    /**
     * @param array $modules
     * @param int   $pid
     *
     * @return array
     */
    private static function unlimitLevel($modules, $pid = 0) {
        $arr = [];
        foreach ($modules as $key => &$module) {
            if ($module['pid'] == $pid) {
                $module['updateTime'] = date("m/d/Y H:i:s", $module['updateTime']);
                if (!empty($module['params'])) {
                    $module['params'] = json_decode($module['params'], true);
                }

                $module['submodule'] = self::unlimitLevel($modules, $module['moduleId']);
                if (isset($module['params']['order'])) {
                    $arr[$module['params']['order']] = $module;
                } else {
                    $arr[] = $module;
                }
            }
        }

        return $arr;
    }


    /**
     * @param $moduleId
     *
     * @return array|bool
     */
    public function getModuleInfoWithSonById($moduleId) {
        $moduleId = (int)$moduleId;
        if (!$moduleId) {
            Error::set('moduleId不能为空');
            return false;
        }

        $modules = $this->moduleDb->select(self::$table, [
            'moduleId',
            'pid',
            'title',
            'subtitle',
            'showMoreUrl',
            'datasource',
            'showType',
            'isHide',
            'isDelete',
            'addTime',
            'updateTime',
            'params'
        ], ['isDelete' => 0]);
        $result = [];
        foreach ($modules as $module) {
            $module['updateTime'] = date("m/d/Y H:i:s", $module['updateTime']);
            if ($module['moduleId'] == $moduleId) {
                if (!empty($module['params'])) {
                    $module['params'] = json_decode($module['params'], true);
                }
                $result = $module;
                break;
            }
        }
        if (!empty($result)) {
            $son = self::unlimitLevel($modules, $moduleId);
            if (isset(Module::$showType[$moduleId])) {
                if (Module::$showType[$moduleId][$result['showType']] == Module::SHOW_TYPE_SUIJI_PAIXU) {
                    shuffle($son);
                } elseif (Module::$showType[$moduleId][$result['showType']] == Module::SHOW_TYPE_GUDING_PAIXU) {
                    ksort($son);
                }
            }
            $result['submodule'] = array_values($son);
        }

        return $result;
    }

    /**
     * 获取一个模块的所有父级模块
     *
     * @param $moduleId
     *
     * @return array | bool
     */
    public function getParentById($moduleId) {
        $moduleId = (int)$moduleId;
        if (!$moduleId) {
            Error::set('moduleId不能为空');
            return false;
        }

        $modules = $this->moduleDb->select(self::$table, [
            'moduleId',
            'pid',
            'title',
            'subtitle',
            'showMoreUrl',
            'datasource',
            'showType',
            'isHide',
            'isDelete',
            'addTime',
            'updateTime',
            'params'
        ]);

        return self::getParent($modules, $moduleId);
    }

    /**
     * 根据moduleId 获取moduleInfo
     *
     * @param $moduleId
     *
     * @return bool|mixed
     */
    public function getModuleInfoById($moduleId) {
        $moduleId = (int)$moduleId;
        if (!$moduleId) {
            Error::set('moduleId不能为空');
            return false;
        }
        $moduleInfo = $this->moduleDb->get(self::$table, [
            'moduleId',
            'pid',
            'title',
            'subtitle',
            'showMoreUrl',
            'datasource',
            'showType',
            'isHide',
            'isDelete',
            'addTime',
            'updateTime',
            'params'
        ], ['moduleId' => $moduleId]);

        if (!empty($moduleInfo['params'])) {
            $moduleInfo['params'] = json_decode($moduleInfo['params'], true);
        } else {
            $moduleInfo['params'] = [];
        }

        return $moduleInfo;
    }

    /**
     * 修改通栏广告位置显示与隐藏
     *
     * @param $moduleId
     * @param $id
     * @param $isHide
     *
     * @return boolean
     */
    public function changeTonglanParams($moduleId, $id, $isHide) {

        if (!$moduleId) {
            Error::set('moduleId不能为空');
            return false;
        }

        $moduleInfo = $this->getModuleInfoById($moduleId);
        $oldParams = $moduleInfo['params'];
        if (isset($moduleInfo['params']['tonglanConfig'][$id])) {
            if ($isHide != $moduleInfo['params']['tonglanConfig'][$id]) {
                $moduleInfo['params']['tonglanConfig'][$id] = $isHide;
            } else {
                return true;
            }
        } else {
            //如果不存在此配置，则直接配置上
            $moduleInfo['params']['tonglanConfig'][$id] = $isHide;
        }

        $params = json_encode($moduleInfo['params']);
        $arr = ['params' => $params];
        if (false !== $this->moduleDb->update(self::$table, $arr, ['moduleId' => $moduleId])) {
            // 添加日志
            $recorder = new \services\Recorder($moduleId, 'editModule');
            $recorder->assign('moduleId', $moduleId)
                ->assign('message', \services\Recorder::diff($arr, ['params' => json_encode($oldParams['params'])]))
                ->recorde();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改轮播图显示样式，图文轮播或纯图轮播
     *
     * @param $moduleId
     * @param $showType
     *
     * @return boolean
     */
    public function changeModuleShowType($moduleId, $showType) {

        $moduleId = (int)$moduleId;
        $showType = (int)$showType;

        if (!$moduleId) {
            Error::set('moduleId不能为空');
            return false;
        }

        $moduleInfo = $this->moduleDb->get(self::$table, '*', ['moduleId' => $moduleId]);
        if (!empty($moduleInfo) && is_array($moduleInfo)) {
            if ($moduleInfo['showType'] == $showType) {
                Error::set('showType未修改');
                return false;
            }
        }

        $arr = ['showType' => $showType];

        if (false !== $this->moduleDb->update(self::$table, $arr, ['moduleId' => $moduleId])) {
            // 添加日志
            $recorder = new \services\Recorder($moduleId, 'editModule');
            $recorder->assign('moduleId', $moduleId)
                ->assign('message', \services\Recorder::diff($arr, ['showType' => $moduleInfo['showType']]))
                ->recorde();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 添加子模块
     *
     * @param $pid
     * @param $data
     *
     * @return boolean
     */
    public function addModuleInfo($pid, $data) {
        $pid = (int)$pid;
        if (!$pid) {
            Error::set('moduleId不能为空');
            return false;
        }
        if ($data['title'] == '') {
            Error::set('tab名称不能为空');
            return false;
        }
        if ($data['isHide'] == '') {
            Error::set('是否显示此tab不能为空');
            return false;
        }

        //查询父模块信息
        $moduleInfo = $this->getModuleInfoById($pid);
        $datasource = $moduleInfo['datasource'];
        $params = [];
        isset($data['minPrice']) && ($params['price']['min'] = $data['minPrice']);
        isset($data['maxPrice']) && ($params['price']['max'] = $data['maxPrice']);
        isset($data['catId']) && ($params['catId'] = $data['catId']);
        isset($data['order']) && ($params['order'] = $data['order']);

        $arr = [
            'pid'         => $pid,
            'title'       => $data['title'],
            'subtitle'    => isset($data['subtitle']) ? $data['subtitle'] : '',
            'showMoreUrl' => isset($data['showMoreUrl']) ? $data['showMoreUrl'] : '',
            'isHide'      => $data['isHide'],
            'isDelete'    => '0',
            'datasource'  => isset($data['datasource']) ? $data['datasource'] : $datasource,
            'addTime'     => time(),
            'updateTime'  => time(),
            'params'      => json_encode($params)
        ];

        $sublings = $this->moduleDb->select(self::$table, '*', [
            'pid'      => $arr['pid'],
            'isDelete' => '0'
        ]);

        if (!empty($sublings)) {
            $tmp = false;
            foreach ($sublings as &$row) {
                $row['params'] = json_decode($row['params'], true);
                if (!$tmp && isset($row['params']['order']) && $row['params']['order'] == $data['order']) {
                    $tmp = true;
                }
            }
            if ($tmp) {
                foreach ($sublings as $ta) {
                    if (isset($ta['params']['order']) && $ta['params']['order'] >= $data['order']) {
                        $ta['params']['order']++;
                        $ta['params'] = json_encode($ta['params']);
                        $this->moduleDb->update(self::$table, $ta, ['moduleId' => $ta['moduleId']]);
                    }
                }
            }
        }

        $flag = $this->moduleDb->has(self::$table, [
            'AND' => [
                'moduleId' => Module::MODULEID
            ]
        ]);
        if ($flag) {
            if ($this->moduleDb->insert(self::$table, $arr)) {
                $moduleId = $this->moduleDb->id();
            } else {
                return false;
            }
        } else {
            $arr['moduleId'] = Module::MODULEID;
            if ($this->moduleDb->insert(self::$table, $arr)) {
                $moduleId = $this->moduleDb->id();
            } else {
                return false;
            }
        }
        //日志
        $recorder = new \services\Recorder($moduleId, 'addSubModule');
        $recorder->assign('moduleId', $moduleId)
            ->assign('message', \services\Recorder::arrToString($arr))
            ->recorde();
        return $moduleId;
    }

    /**
     * @param $moduleId
     * @param $data
     *
     * @return bool
     */
    public function editModuleInfo($moduleId, $data) {
        $moduleId = (int)$moduleId;
        if (!$moduleId) {
            Error::set('moduleId不能为空');
            return false;
        }

        $old = $this->moduleDb->get(self::$table, '*', [
            'AND' => [
                'moduleId' => $moduleId,
                'isDelete' => 0
            ]
        ]);

        if (empty($old)) {
            Error::set('您操作的数据不存在或已删除，请刷新后重试');
            return false;
        }

        $old['params'] = json_decode($old['params'], true);

        //父模块编号
        $pid = $old['pid'];
        //重新排序
        if ($old['params']['order'] != $data['order']) {
            $sublings = $this->moduleDb->select(self::$table, '*', [
                'pid'         => $pid,
                'moduleId[!]' => $old['moduleId']
            ]);

            if (!empty($sublings)) {
                $tmp = false;
                foreach ($sublings as &$row) {
                    $row['params'] = json_decode($row['params'], true);
                    if ($row['params']['order'] == $data['order']) {
                        $tmp = true;
                    }
                }

                if ($tmp) {
                    if ($old['params']['order'] > $data['order']) {
                        foreach ($sublings as $ta) {
                            if (isset($ta['params']['order']) && $ta['params']['order'] >= $data['order']
                                && $ta['params']['order'] < $old['params']['order']
                            ) {
                                $ta['params']['order']++;
                                $ta['params'] = json_encode($ta['params']);
                                $this->moduleDb->update(self::$table, $ta, ['moduleId' => $ta['moduleId']]);
                            }
                        }
                    } else {
                        if ($old['params']['order'] < $data['order']) {
                            foreach ($sublings as $ta) {
                                if (isset($ta['params']['order']) && $ta['params']['order'] <= $data['order']
                                    && $ta['params']['order'] > $old['params']['order']
                                ) {
                                    $ta['params']['order']--;
                                    $ta['params'] = json_encode($ta['params']);
                                    $this->moduleDb->update(self::$table, $ta, ['moduleId' => $ta['moduleId']]);
                                }
                            }
                        }
                    }
                }
            }
        }

        $params = [];
        if (isset($data['minPrice']) || isset($old['params']['min'])) {
            $params['price']['min'] = isset($data['minPrice']) ? $data['minPrice'] : $old['params']['min'];
        }
        if (isset($data['maxPrice']) || isset($old['params']['max'])) {
            $params['price']['max'] = isset($data['maxPrice']) ? $data['maxPrice'] : $old['params']['max'];
        }
        if (isset($data['catId']) || isset($old['params']['catId'])) {
            $params['catId'] = isset($data['catId']) ? $data['catId'] : $old['params']['catId'];
        }
        if (isset($data['order']) || isset($old['params']['order'])) {
            $params['order'] = isset($data['order']) ? $data['order'] : $old['params']['order'];
        }

        $arr = [];
        isset($data['title']) && !empty($data['title']) && ($arr['title'] = $data['title']);
        isset($data['subtitle']) && !empty($data['subtitle']) && ($arr['subtitle'] = $data['subtitle']);
        isset($data['showMoreUrl']) && !empty($data['showMoreUrl']) && ($arr['showMoreUrl'] = $data['showMoreUrl']);
        isset($data['isHide']) && ($arr['isHide'] = $data['isHide']);
        $arr['isDelete'] = $old['isDelete'];
        $arr['datasource'] = $old['datasource'];
        $arr['updateTime'] = time();
        $arr['params'] = json_encode($params);
        if (false !== $this->moduleDb->update(self::$table, $arr, ['moduleId' => $moduleId])) {
            //添加操作日志
            $recorder = new \services\Recorder($moduleId, 'editModule');
            $recorder->assign('moduleId', $moduleId)
                ->assign('message', \services\Recorder::diff($arr, $old))
                ->recorde();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除模块
     *
     * @param $moduleId
     *
     * @return bool
     */
    public function deleteModule($moduleId) {

        $old = $this->moduleDb->get(self::$table, '*', [
            'moduleId' => $moduleId,
            'isDelete' => '0'
        ]);
        if (empty($old)) {
            return true;
        }
        $old['params'] = json_decode($old['params'], true);
        $arr = [
            'isDelete' => '1'
        ];
        //排序
        $sublings = $this->moduleDb->select(self::$table, '*', [
            'pid'         => $old['pid'],
            'moduleId[!]' => $old['moduleId'],
            'isDelete'    => '0',
        ]);
        if (!empty($sublings)) {
            foreach ($sublings as $row) {
                $row['params'] = json_decode($row['params'], true);
                if (isset($row['params']['order']) && $row['params']['order'] >= $old['params']['order']) {
                    $row['params']['order']--;
                    $row['params'] = json_encode($row['params']);
                    $this->moduleDb->update(self::$table, $row, ['moduleId' => $row['moduleId']]);
                }
            }
        }

        if (false !== $this->moduleDb->update(self::$table, $arr, ['moduleId' => $moduleId])) {
            // 添加操作日志
            $recorder = new \services\Recorder($moduleId, 'deleteModule');
            $recorder->assign('moduleId', $moduleId)->recorde();
            return true;
        } else {
            return false;
        }
    }
}
