<?php

use kongfz\Model;
use services\Error;
use storage\Db;

/**
 * Class OperationConfigModel
 * 推广数据配置信息，从不同类型数据中抽象出的统一界面
 * status:
 *  1. 草稿。所有数据的默认状态，新添加的数据，不指定发布时间都认为是草稿
 *  2. 已发布。(1) 已发布，未结束。(2) 已发布，已结束(这里专指自动结束, 需要结合自动结束时间来判断)
 *  3. 已结束。手动结束
 * @author  liubang <liubang@kongfz.com>
 */
class OperationConfigModel extends Model {

    /** @var int 草稿 */
    const STATUS_DRAFT = 1;

    /** @var int 已发布 */
    const STATUS_PUBLISHED = 2;

    /** @var int 已结束(手动结束) */
    const STATUS_BREAK = 3;

    /** @var string 当前model操作的库 */
    private static $database = 'adminMaster';

    /** @var string 当前model操作的表 */
    private static $table = 'operationConfig';

    use \kongfz\traits\Singleton;

    /** @var null | Medoo\Medoo */
    private $operationConfigDb = null;

    /**
     * @return OperationConfigModel
     */
    public static function singleton() {
        return self::instance();
    }

    public static function getDatabaseName() {
        return self::$database;
    }

    public static function getTableName() {
        return self::$table;
    }

    /**
     * 修改数据
     * @param $moduleId
     * @param $data
     * @return bool
     */
    public function updateByConfId($moduleId, $data) {

        $moduleId = (int)$moduleId;
        if (!$moduleId) {
            Error::set('moduleId不能为空');
            return false;
        }

        if (empty($data['confId'])) {
            Error::set('confId不能为空');
            return false;
        }

        //过滤数据
        $arr = [
            'startTime'  => !empty($data['startTime']) ? strtotime($data['startTime']) : time(),
            'endTime'    => !empty($data['endTime']) ? strtotime($data['endTime']) : 0,
            'updateTime' => time(),
            'order'      => isset($data['order']) ? $data['order'] : '1',
        ];

        if (!empty($data['moduleId'])) {
            $arr['moduleId'] = $data['moduleId'];
        }

        if (!empty($data['startTime'])) {
            $arr['startTime'] = strtotime($data['startTime']);
            $arr['status'] = self::STATUS_PUBLISHED;
        } else {
            $arr['status'] = self::STATUS_DRAFT;
        }

        if (!empty($data['dataId'])) {
            $arr['dataId'] = $data['dataId'];
        }

        $old = $this->operationConfigDb->get(self::$table, '*', [
            'AND' => [
                'moduleId' => $moduleId,
                'confId'   => $data['confId'],
                'isDelete' => 0
            ]
        ]);

        if (empty($old)) {
            Error::set('您操作的数据不存在或已删除，请刷新后重试');
            return false;
        }

        $flag = $this->operationConfigDb->has(self::$table, [
            'AND' => [
                'moduleId'  => isset($arr['moduleId']) ? $arr['moduleId'] : $moduleId,
                'order'     => $data['order'],
                'isDefault' => $old['isDefault'],
                'isDelete'  => 0
            ]
        ]);
        $res1 = true;
        if (!$flag) {
            //do nothing
        } else {
            // move data from one module to another.
            if (!empty($arr['moduleId']) && $moduleId != $arr['moduleId']) {
                //delete data from origin module and insert to new one.
                $res1 = $this->operationConfigDb->update(self::$table, ['order[-]' => 1], [
                        'moduleId'  => $moduleId,
                        'order[>]'  => $old['order'],
                        'isDefault' => $old['isDefault'],
                        'isDelete'  => 0
                    ]) &&
                    $this->operationConfigDb->update(self::$table, ['order[+]' => 1], [
                        'moduleId'  => $arr['moduleId'],
                        'order[>=]' => $arr['order'],
                        'isDefault' => $old['isDefault'],
                        'isDelete'  => 0
                    ]);
            } else {
                if ($old['order'] > $data['order']) {
                    $res1 = $this->operationConfigDb->update(self::$table, ['order[+]' => 1], [
                        'AND' => [
                            'moduleId'  => $moduleId,
                            'order[<]'  => $old['order'],
                            'order[>=]' => $data['order'],
                            'isDefault' => $old['isDefault'],
                            'isDelete'  => 0
                        ]
                    ]);
                } elseif ($old['order'] < $data['order']) {
                    $res1 = $this->operationConfigDb->update(self::$table, ['order[-]' => 1], [
                        'AND' => [
                            'moduleId'  => $moduleId,
                            'order[>]'  => $old['order'],
                            'order[<=]' => $data['order'],
                            'isDefault' => $old['isDefault'],
                            'isDelete'  => 0
                        ]
                    ]);
                }
            }
        }
        if ($res1 && false !== $this->operationConfigDb->update(self::$table, $arr,
                ['AND' => ['confId' => $data['confId']]])
        ) {
            // 添加操作日志
            $recorder = new \services\Recorder($moduleId, 'editModuleData');
            $recorder->assign('moduleId', $moduleId)
                ->assign('message', \services\Recorder::diff($arr, $old))
                ->recorde();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 排序
     * @param $moduleId
     * @param $confId
     * @param $pos
     * @return bool
     */
    public function sort($moduleId, $confId, $pos) {
        $moduleId = (int)$moduleId;
        $confId = (int)$confId;
        $pos = (int)$pos;
        if ($moduleId == 0) {
            Error::set('moduleId不能为空');
            return false;
        }
        if ($confId == 0) {
            Error::set('confId不能为空');
            return false;
        }

        $old = $this->operationConfigDb->get(self::$table, '*', [
            'AND' => [
                'moduleId' => $moduleId,
                'confId'   => $confId
            ]
        ]);

        if (empty($old)) {
            Error::set('您要操作的数据不存在，请刷新后重新操作');
            return false;
        }

        //查看是否已占位
        $flag = $this->operationConfigDb->has(self::$table, [
            'AND' => [
                'moduleId'  => $moduleId,
                'isDefault' => $old['isDefault'],
                'order'     => $pos,
                'isDelete'  => 0
            ]
        ]);
        //如果没有，则直接插入
        if (!$flag) {
            return $this->operationConfigDb->update(self::$table, ['order' => $pos], [
                'AND' => [
                    'moduleId' => $moduleId,
                    'confId'   => $confId
                ]
            ]);
        } else {
            //使用事务
            $res = false;
            $this->operationConfigDb->action(function ($db) use ($old, $pos, &$res) {
                //if the order don't change
                if ($old['order'] == $pos) {
                    $res = true;
                    //do nothing.
                } else {
                    if ($old['order'] > $pos) {
                        $res = $db->update(self::$table, ['order[+]' => 1], [
                            'AND' => [
                                'moduleId'  => $old['moduleId'],
                                'order[<]'  => $old['order'],
                                'order[>=]' => $pos,
                                'isDefault' => $old['isDefault'],
                                'isDelete'  => 0
                            ]
                        ]);
                    } elseif ($old['order'] < $pos) {
                        $res = $db->update(self::$table, ['order[-]' => 1], [
                            'AND' => [
                                'moduleId'  => $old['moduleId'],
                                'order[>]'  => $old['order'],
                                'order[<=]' => $pos,
                                'isDefault' => $old['isDefault'],
                                'isDelete'  => 0
                            ]
                        ]);
                    }
                    if ($res) {
                        $res = $db->update(self::$table, ['order' => $pos], [
                            'AND' => [
                                'moduleId' => $old['moduleId'],
                                'confId'   => $old['confId']
                            ]
                        ]);
                        if ($res !== false) {
                            $res = true;
                        }
                    }
                }
                return $res;
            });

            // 日志
            $recorder = new \services\Recorder($moduleId, 'editModuleData');
            $recorder->assign('moduleId', $moduleId)
                ->assign('message', \services\Recorder::diff(['order' => $pos], ['order' => $old['order']]))
                ->recorde();
            return $res;
        }
    }

    /**
     * 获取模块数据的id
     * @param int   $moduleId
     * @param array $params
     * @return array|bool
     */
    public function getModuleDatas($moduleId, $params = []) {
        $moduleId = (int)$moduleId;

        if (!$moduleId) {
            Error::set('moduleId不能为空');
            return false;
        }

        // $where and $join are reference type
        $this->generateSearchCondition($moduleId, $params, $where, $join);
        if (empty($join)) {
            $result = $this->operationConfigDb->select(
                self::$table,
                ['confId', 'dataId', 'status', 'order', 'startTime', 'endTime', 'updateTime', 'isDefault', 'params'],
                $where
            );
        } else {
            $result = $this->operationConfigDb->select(
                self::$table,
                $join,
                [
                    'operationConfig.confId',
                    'dataId',
                    'status',
                    'order',
                    'startTime',
                    'endTime',
                    'updateTime',
                    'isDefault',
                    'params'
                ],
                $where
            );
        }

        $arr = [];
        foreach ($result as $row) {
            $arr[$row['confId']] = [
                'confId'     => $row['confId'],
                'dataId'     => $row['dataId'],
                'moduleId'   => $moduleId,
                'order'      => $row['order'],
                'status'     => self::getChineseFormStatus($row['status'], $row['endTime']),
                'startTime'  => date("m/d/Y H:i:s", $row['startTime']),
                'endTime'    => empty($row['endTime']) ? '' : date("m/d/Y H:i:s", $row['endTime']),
                'updateTime' => date("m/d/Y H:i:s", $row['updateTime']),
                'isDefault'  => $row['isDefault']
            ];
            if (!empty($row['params'])) {
                $row['params'] = json_decode($row['params'], true);
                if (isset($row['params']['extFields'])) {
                    $arr[$row['confId']] = array_merge($arr[$row['confId']], $row['params']['extFields']);
                }
            }
        }

        unset($result);
        //$result = array_combine(array_column($result, 'dataId'), $result);
        return $arr;
    }

    /**
     * @param int   $moduleId
     * @param array $params
     * @param array $where
     * @param array $jojn
     */
    public function generateSearchCondition($moduleId, $params, &$where = [], &$jojn = []) {
        $where = [
            'AND' => [
                'moduleId' => $moduleId,
                'isDelete' => 0
            ]
        ];

        if (!empty($params['keyWords']) && count($params) > 0) {
            $i = 0;
            foreach ($params['keyWords'] as $k => $v) {
                if (!empty($v)) {
                    $i++;
                    $where['AND']['AND #10']['OR']['AND #' . $i]['search.searchKey'] = $k;
                    $where['AND']['AND #10']['OR']['AND #' . $i]['search.searchVal[~]'] = $v;
                }
            }
            if ($i > 0) {
                $jojn = [
                    "[>]search" => "confId",
                ];
            }
        }

        if (!empty($params['and'])) {
            $where['AND'] = array_merge($where['AND'], $params['and']);
        }
        if (!empty($params['or'])) {
            $where['OR'] = $params['or'];
        }
        if (!empty($params['limit'])) {
            $where['LIMIT'] = $params['limit'];
        }

        if (!empty($params['order'])) {
            $where['ORDER'] = $params['order'];
        } else {
            $where['ORDER'] = [
                'order'      => 'ASC',
                'updateTime' => 'DESC'
            ];
        }

        if (isset($params['isDefault'])) {
            $where['AND']['isDefault'] = $params['isDefault'];
        }

        if (!empty($params['status'])) {
            if (count($params['status']) == 1) {
                //草稿
                in_array('draft', $params['status']) && ($where['AND']['status'] = self::STATUS_DRAFT);
                //发布中
                if (in_array('published', $params['status'])) {
                    $where['AND']['status'] = self::STATUS_PUBLISHED;
                    $where['AND']['OR']['endTime'] = 0;
                    $where['AND']['OR']['endTime[>=]'] = time();
                }
                //已结束
                if (in_array('ended', $params['status'])) {
                    $where['AND']['endTime[>]'] = 0;
                    $where['AND']['endTime[<=]'] = time();
                }
            } elseif (count($params['status']) > 1) {
                if (in_array('draft', $params['status'])) {
                    $where['AND']['OR']['AND #0']['status'] = self::STATUS_DRAFT;
                }
                //发布中
                if (in_array('published', $params['status'])) {
                    $where['AND']['OR']['AND #1']['status'] = self::STATUS_PUBLISHED;
                    $where['AND']['OR']['AND #1']['OR']['endTime[>=]'] = time();
                    $where['AND']['OR']['AND #1']['OR']['endTime'] = 0;
                }
                //已结束
                if (in_array('ended', $params['status'])) {
                    $where['AND']['OR']['AND #2']['endTime[>]'] = 0;
                    $where['AND']['OR']['AND #2']['endTime[<=]'] = time();
                }
            }
        } else {
            //产品要求，如果没有传参，则表示没有哪种状态在选择范围内，也就是返回空集
            $where['AND']['confId'] = 0;
            return;
        }
        if (!empty($params['regularlyStart'])) {
            //倒计时发布
            if ('countDown' === $params['regularlyStart']) {
                $where['AND']['AND #3']['status'] = self::STATUS_PUBLISHED;
                $where['AND']['AND #3']['startTime[>]'] = time();
            } else //发布中
            {
                if ('progressing' === $params['regularlyStart']) {
                    $where['AND']['AND #3']['status'] = self::STATUS_PUBLISHED;
                    $where['AND']['AND #3']['startTime[<=]'] = time();
                    $where['AND']['AND #3']['AND']['OR']['endTime[>]'] = time();
                    $where['AND']['AND #3']['AND']['OR']['endTime'] = 0;
                }
            }
        }
        if (!empty($params['regularlyEnd'])) {
            //倒计时结束
            if ('countDown' === $params['regularlyEnd']) {
                $where['AND']['AND #4']['status'] = self::STATUS_PUBLISHED;
                $where['AND']['AND #4']['startTime[<=]'] = time();
                $where['AND']['AND #4']['endTime[>]'] = time();
            } else //手动结束
            {
                if ('break' === $params['regularlyEnd']) {
                    $where['AND']['AND #4']['OR']['status'] = self::STATUS_BREAK;
                    $where['AND']['AND #4']['OR']['endTime'] = 0;
                }
            }
        }
    }

    /**
     * 获取运营数据的状态(汉字形式)
     * @param $statusCode
     * @param $endTime
     * @return string
     */
    public static function getChineseFormStatus($statusCode, $endTime) {
        $status = '';
        switch ($statusCode) {
            case self::STATUS_DRAFT:
                $status = '草稿';
                break;
            case self::STATUS_PUBLISHED:
                if ($endTime > 0 && $endTime <= time()) {
                    $status = '自动结束';
                } else {
                    $status = '发布中';
                }
                break;
            case self::STATUS_BREAK:
                $status = '手动结束';
                break;
        }
        return $status;
    }

    /**
     * @param $data
     * @return array|mixed
     */
    public function add($data) {

        if (empty($data['moduleId'])) {
            Error::set('moduleId不能为空');
            return false;
        }

        if (empty($data['dataId'])) {
            Error::set('dataId不能为空');
            return false;
        }

        $arr = [
            'moduleId'   => $data['moduleId'],
            'dataId'     => $data['dataId'],
            'startTime'  => !empty($data['startTime']) ? strtotime($data['startTime']) : time(),
            'endTime'    => !empty($data['endTime']) ? strtotime($data['endTime']) : 0,
            'addTime'    => time(),
            'updateTime' => time(),
            'order'      => isset($data['order']) ? $data['order'] : '1',
            'isDefault'  => isset($data['isDefault']) ? $data['isDefault'] : 0
        ];
        // 额外字段
        if (isset($data['extFields']) && !empty($data['extFields']) && is_array($data['extFields'])) {
            foreach ($data['extFields'] as $v) {
                $arr['params']['extFields'][$v] = isset($data[$v]) ? $data[$v] : '';
            }
        }

        if (!empty($arr['startTime']) && !empty($arr['endTime']) && $arr['startTime'] >= $arr['endTime']) {
            Error::set('开始时间不能大于结束时间');
            return false;
        }

        if (!empty($data['startTime'])) {
            $arr['status'] = self::STATUS_PUBLISHED;
        } else {
            $arr['status'] = self::STATUS_DRAFT;
        }

        $flag = $this->operationConfigDb->has(self::$table, [
            'AND' => [
                'moduleId' => $arr['moduleId'],
                'order'    => $arr['order'],
                'isDelete' => 0
            ]
        ]);

        $moduleId = $arr['moduleId'];
        if (isset($arr['params'])) {
            $arr['params'] = json_encode($arr['params']);
        }
        if (!$flag) {
            if ($this->operationConfigDb->insert(self::$table, $arr)) {
                $confId = $this->operationConfigDb->id();
                //添加操作日志
                $recorder = new \services\Recorder($moduleId, 'addSubModule');
                $recorder->assign('moduleId', $moduleId)
                    ->assign('message', \services\Recorder::diff($arr))
                    ->recorde();
                return $confId;
            }
        } else {
            $res = false;
            $this->operationConfigDb->action(function ($db) use ($arr, &$res) {
                $res1 = $db->update(self::$table, ['order[+]' => 1], [
                    'AND' => [
                        'moduleId'  => $arr['moduleId'],
                        'order[>=]' => $arr['order'],
                        'isDefault' => $arr['isDefault'],
                        'isDelete'  => 0
                    ]
                ]);

                if (false === $res1) {
                    $res = false;
                    return $res;
                }
                $res2 = $db->insert(self::$table, $arr);
                if (false === $res2) {
                    $res = false;
                } else {
                    $res = $db->id();
                }
                return $res;
            });

            // 添加操作日志
            $recorder = new \services\Recorder($moduleId, 'addModuleData');
            $recorder->assign('moduleId', $moduleId)
                ->assign('message', \services\Recorder::arrToString($arr))
                ->recorde();
            return $res;
        }
    }

    /**
     * 删除运营数据
     * @param $moduleId
     * @param $confId
     * @return bool
     */
    public function delete($moduleId, $confId) {

        if (empty($moduleId)) {
            Error::set('moduleId不能为空');
            return false;
        }

        if (empty($confId)) {
            Error::set('confId不能为空');
            return false;
        }

        $dataInfo = $this->operationConfigDb->get(self::$table, '*', [
            'AND' => [
                'moduleId' => $moduleId,
                'confId'   => $confId,
                'isDelete' => 0
            ]
        ]);

        if (empty($dataInfo)) {
            Error::set('您操作的数据不存在');
            return false;
        }

        $res = false;
        $this->operationConfigDb->action(function ($db) use ($dataInfo, &$res) {
            $res1 = $db->update(self::$table, ['order[-]' => 1], [
                'AND' => [
                    'moduleId'  => $dataInfo['moduleId'],
                    'order[>=]' => $dataInfo['order'],
                    'isDefault' => $dataInfo['isDefault'],
                    'isDelete'  => 0
                ]
            ]);

            if (false === $res1) {
                $res = false;
                return $res;
            }

            $arr = [
                'isDelete'   => 1,
                'updateTime' => time()
            ];
            $res2 = $db->update(self::$table, $arr, [
                'AND' => [
                    'moduleId' => $dataInfo['moduleId'],
                    'confId'   => $dataInfo['confId']
                ]
            ]);

            if (false === $res2) {
                $res = false;
            } else {
                $res = true;
            }
            return $res;
        });
        // 添加操作日志
        $recorder = new \services\Recorder($moduleId, 'deleteModuleData');
        $recorder->assign('moduleId', $moduleId)
            ->assign('confId', $confId)
            ->recorde();
        return $res;
    }

    /**
     * 统计总量
     * @param int   $moduleId
     * @param array $params
     * @return bool|int
     */
    public function getCount($moduleId, $params) {

        $this->generateSearchCondition($moduleId, $params, $where, $join);
        if (isset($where['LIMIT'])) {
            unset($where['LIMIT']);
        }
        if (isset($where['ORDER'])) {
            unset($where['ORDER']);
        }
        if (!empty($join)) {
            // 由于medoo暂不支持distinct查询，所以联表模糊查询很可能查出重复数据，因此这里不直接使用count，而是
            // 查出所有confId再去重
            $result = $this->operationConfigDb->select(self::$table, $join, ['confId'], $where);
            if (empty($result)) {
                return 0;
            }
            $result = array_unique(array_column($result, 'confId'));
            return count($result);
        } else {
            return $this->operationConfigDb->count(self::$table, $where);
        }
    }

    /**
     * @param $confId
     * @return bool|mixed
     */
    public function getDataIdByConfId($confId) {
        return $this->operationConfigDb->get(self::$table, 'dataId', ['confId' => $confId]);
    }

    private function _init_() {
        $this->operationConfigDb = Db::factory(self::$database);
    }

}