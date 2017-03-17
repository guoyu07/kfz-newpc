<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/2/13 下午3:13
 | @copyright : (c) kongfz.com
 | @license   : MIT (http://opensource.org/licenses/MIT)
 |------------------------------------------------------------------
 */

use storage\Db;

class SearchModel extends \kongfz\Model {
    /** @var string 当前model操作的库 */
    private static $database = 'adminMaster';

    /** @var string 当前model操作的表 */
    private static $table = 'search';

    /** @var null | Medoo\Medoo */
    private $searchDb = null;

    use \kongfz\traits\Singleton;

    /**
     * @return SearchModel
     */
    public static function singleton() {
        return self::instance();
    }

    private function _init_() {
        $this->searchDb = Db::factory(self::$database);
    }

    /**
     * 在搜索表中插入数据
     *
     * @param $confId
     * @param $searchKey
     * @param $searchVal
     *
     * @return bool|int
     */
    public function createIndex($confId, $searchKey, $searchVal) {
        $res = $this->searchDb->insert(self::$table, [
            'confId'    => $confId,
            'searchKey' => $searchKey,
            'searchVal' => $searchVal
        ]);

        return false === $res ? false : true;
    }

    /**
     * 更新搜索表中数据
     *
     * @param $confId
     * @param $searchKey
     * @param $searchVal
     *
     * @return bool|int
     */
    public function updateIndex($confId, $searchKey, $searchVal) {
        $arr = [
            'searchVal' => $searchVal
        ];
        $res = $this->searchDb->update(self::$table, $arr, [
            'confId'    => $confId,
            'searchKey' => $searchKey
        ]);

        return false === $res ? false : true;
    }

    /**
     * 获取搜索表中数据
     *
     * @param $confId
     * @param $searchKey
     *
     * @return bool|int
     */
    public function getIndex($confId, $searchKey) {
        $arr = ['searchVal'];
        $where = [
            'AND' => [
                'confId'    => $confId,
                'searchKey' => $searchKey
            ]
        ];
        $result = $this->searchDb->select(self::$table, $arr, $where);
        $searchVal = '';
        if (!empty($result) && is_array($result)) {
            foreach ($result as $k => $v) {
                $searchVal = $v['searchVal'];
            }
        }

        return $searchVal;
    }

    /**
     * 删除搜索表中数据
     *
     * @param $confId
     *
     * @return int|bool
     */
    public function deleteIndex($confId) {
        $where = [
            'AND' => [
                'confId' => $confId
            ]
        ];
        $result = $this->searchDb->delete(self::$table, $where);
        return $result;
    }
}