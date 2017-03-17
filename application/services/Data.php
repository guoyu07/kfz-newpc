<?php

namespace services;

use conf\Module;
use kongfz\Exception;
use kongfz\traits\Singleton;

class Data {

    use Singleton;

    /**
     * @return Data
     */
    public static function singleton() {
        return self::instance();
    }

    /** @var array */
    private static $dataSourceMap = [
        \ModuleModel::DATA_SRC_RECOMMEND_SHOP_INFO => [
            'class'        => 'RecommendShop',
            'linkedIdName' => 'id'
        ],
        \ModuleModel::DATA_SRC_SHOP_ITEM           => [
            'class'        => 'ShopItem',
            'linkedIdName' => 'itemId'
        ],
        \ModuleModel::DATA_SRC_AUCTION_USER        => [
            'class'        => 'User',
            'linkedIdName' => 'userId'
        ],
        \ModuleModel::DATA_SRC_AUCTION_ITEM        => [
            'class'        => 'AuctionItem',
            'linkedIdName' => 'itemId'
        ],
        \ModuleModel::DATA_SRC_LUNBO_IMG           => [
            'class'        => 'LunboImg',
            'linkedIdName' => 'imgId'
        ],
        \ModuleModel::DATA_SRC_ADVERTISE_IMG       => [
            'class'        => 'Advertise',
            'linkedIdName' => 'imgId'
        ],
        \ModuleModel::DATA_SRC_SHOP_INFO           => [
            'class'        => 'Shop',
            'linkedIdName' => 'shopId'
        ],
        \ModuleModel::DATA_SRC_ISBN                => [
            'class'        => 'Isbn',
            'linkedIdName' => 'isbn'
        ],
        \ModuleModel::DATA_SRC_LINKS               => [
            'class'        => 'Links',
            'linkedIdName' => 'linkId'
        ]
    ];


    /**
     * 格式化输出的数据,将运营后台存储的dataId和获取的详细运营数据联系起来
     * @param $datas
     * @param $result
     * @param $linkedIdName
     * @return array
     */
    private static function formatResultData($datas, $result, $linkedIdName) {
        $arr = [];
        foreach ($datas as $data) {
            if (!empty($result) && is_array($result)) {
                foreach ($result as $row) {
                    if ($data['dataId'] == $row[$linkedIdName]) {
                        $arr[] = \array_merge($data, $row);
                        break;
                    }
                }
            } else {
                $arr[] = $data;
            }
        }
        return $arr;
    }

    /**
     * @param $moduleId
     * @param $params
     * @return array|bool
     * @throws Exception
     */
    public static function getData($moduleId, $params) {
        $moduleInfo = \ModuleModel::singleton()->getModuleInfoById($moduleId);
        $dataSource = $moduleInfo['datasource'];
        // 同步其他模块数据的情况
        if (isset($moduleInfo['params']['sync']['switch']) && $moduleInfo['params']['sync']['switch'] == '1'
            && isset(Module::$moduleSyncMap[$moduleId]['syncModuleId'])) {
            $syncModuleId = Module::$moduleSyncMap[$moduleId]['syncModuleId'];
            $syncModuleInfo = \ModuleModel::singleton()->getModuleInfoById($syncModuleId);
            // 重新定义数据源
            $dataSource = $syncModuleInfo['datasource'];
            $datas = \OperationConfigModel::singleton()->getModuleDatas($syncModuleId, $params);
        } else {
            $datas = \OperationConfigModel::singleton()->getModuleDatas($moduleId, $params);
        }
        if (false === $datas) {
            return false;
        }
        if (empty($datas)) {
            return [];
        }
        //$dataIds = \array_column($datas, 'dataId');
        if (isset(self::$dataSourceMap[$dataSource])) {
            $dataSourceMap = self::$dataSourceMap[$dataSource];
            /** @var \services\data\DataInterface $class */
            $class = 'services\\data\\' . $dataSourceMap['class'];
            $linkedIdName = $dataSourceMap['linkedIdName'];
            if (\class_exists($class)) {
                $result = \call_user_func($class . '::getByIds', $datas);
                if (\is_array($result)) {
                    $arr = self::formatResultData($datas, $result, $linkedIdName);
                    return $arr;
                } else {
                    $arr = self::formatResultData($datas, [], $linkedIdName);
                    return $arr;
                }
            } else {
                throw new Exception("{$class}不存在");
                Error::set('系统错误');
                return false;
            }

        } else {
            Error::set('您操作的数据有误，刷新后重试');
            return false;
        }
    }

    /**
     * @param $moduleInfo
     * @param $data
     * @return bool|mixed
     * @throws Exception
     */
    public static function editData($moduleInfo, $data) {
        if (isset(self::$dataSourceMap[$moduleInfo['datasource']])) {
            $dataSourceMap = self::$dataSourceMap[$moduleInfo['datasource']];
            /** @var \services\data\DataInterface $class */
            $class = 'services\\data\\' . $dataSourceMap['class'];
            if (\class_exists($class)) {
                $result = \call_user_func($class . '::edit', $moduleInfo, $data);
                return $result;
            } else {
                throw new Exception("{$class}不存在");
                Error::set('系统错误！');
                return false;
            }
        } else {
            Error::set('您操作的数据有误，请刷新后重试');
            return false;
        }
    }

    /**
     * @param $moduleInfo
     * @param $data
     * @return bool|mixed
     * @throws Exception
     */
    public static function addData($moduleInfo, $data) {
        if (isset(self::$dataSourceMap[$moduleInfo['datasource']])) {
            $dataSourceMap = self::$dataSourceMap[$moduleInfo['datasource']];
            /** @var \services\data\DataInterface $class */
            $class = 'services\\data\\' . $dataSourceMap['class'];
            if (\class_exists($class)) {
                $result = \call_user_func($class . '::add', $moduleInfo, $data);
                return $result;
            } else {
                throw new Exception("{$class}不存在");
                Error::set('系统错误');
                return false;
            }
        } else {
            Error::set('您操作的数据有误，请刷新后重试');
            return false;
        }
    }


    /**
     * 调取运营模块当前的运营数据
     * @param int   $moduleId 运营模块编号
     * @param int   $limit    获取的数量
     * @param bool  $depth    是否调取所有子模块数据
     * @param array $params   预留，其他筛选参数
     * @return array
     */
    public function getModuleCurrentOperateData($moduleId, $limit = null, $depth = false, $params = []) {
        $moduleInfo = \ModuleModel::singleton()->getModuleInfoWithSonById($moduleId);
        /**
         * 递归获取子模块数据
         * @param $moduleInfo
         * @return mixed
         */
        $func = function ($moduleInfo) use ($limit, $depth, $params, &$func) {
            $p = [
                'and'    => [
                    'isDefault'     => 0,
                    'status'        => \OperationConfigModel::STATUS_PUBLISHED,
                    'startTime[<=]' => \time(),
                    'OR'            => [
                        'endTime'    => 0,
                        'endTime[>]' => \time()
                    ]
                ],
                //必填参数，否则会返回空集，原因请移步OperationConfigModel::generateSearchCondition()方法
                'status' => ['published']
            ];

            if (is_int($limit)) {
                $p['limit'] = $limit;
            }
            // get the datas of current module.
            $moduleInfo['data'] = self::getData($moduleInfo['moduleId'], \array_merge($p, $params));
            unset($p);
            // get the default datas of current module.
            $moduleInfo['defaultData'] = self::getData($moduleInfo['moduleId'], [
                'isDefault' => 1,
                'status'    => ['draft', 'published', 'ended']
            ]);

            if ($depth && !empty($moduleInfo['submodule'])) {
                foreach ($moduleInfo['submodule'] as $k => $submodule) {
                    $moduleInfo['submodule'][$k] = $func($submodule);
                }
            }
            return $moduleInfo;
        };

        return $func($moduleInfo);
    }
}