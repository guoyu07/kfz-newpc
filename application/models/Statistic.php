<?php

use storage\Db;

/**
 * Class StatisticModel
 * 运营数据统计
 * @author  liubang <liubang@kongfz.com>
 */
class StatisticModel extends \kongfz\Model {
    private static $database = 'adminMaster';

    private static $table = 'statistic';

    /** @var null | \Medoo\Medoo */
    private $statisticDb = null;

    use \kongfz\traits\Singleton;

    /**
     * @return StatisticModel
     */
    public static function singleton() {
        return self::instance();
    }

    private function _init_() {
        $this->statisticDb = Db::factory(self::$database);
    }

    /**
     * 存储数据
     * @param $data
     * @return array|mixed
     */
    public function pushData($data) {

        return $this->statisticDb->insert(self::$table, [
                'dataId'     => $data['dataId'],
                'moduleId'   => $data['moduleId'],
                'agent'      => isset($data['agent']) ? $data['agent'] : '',
                'uuid'       => $data['uuid'],
                'ip'         => $data['ip'],
                'userId'     => isset($data['userId']) ? $data['userId'] : 0,
                'updateTime' => $data['updateTime']
            ]
        );
    }
}