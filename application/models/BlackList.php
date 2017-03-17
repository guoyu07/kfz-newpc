<?php

use kongfz\Model;
use storage\Db;

/**
 * Class BlackListModel
 * 运营后台黑名单
 * @author  liubang <liubang@kongfz.com>
 */
class BlackListModel extends Model {
    /** @var int 拍主 */
    const TYPE_AUCTIONER = 1;

    /** @var int 拍品 */
    const TYPE_AUCTION_ITEM = 2;

    /** @var int 店铺 */
    const TYPE_SHOP = 3;

    /** @var int 商品 */
    const TYPE_SHOP_ITEM = 4;

    /**
     * @var null | \Medoo\Medoo
     */
    private $blackListDb = null;

    use \kongfz\traits\Singleton;

    /**
     * @return \kongfz\traits\Singleton | BlackListModel
     */
    public static function singleton() {
        return self::instance();
    }

    /**
     * 添加黑名单
     * @param $id   int 黑名单实体对应的唯一id
     * @param $type int 黑名单数据类型
     * @return array|bool|mixed
     */
    public function addBlack($id, $type) {
        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }

        $arr = [
            'type'    => $type,
            'value'   => $id,
            'addTime' => time()
        ];

        if ($this->blackListDb->insert('blackList', $arr)) {
            return $this->blackListDb->id();
        } else {
            \services\Error::set('写入黑名单信息失败');
            return false;
        }
    }

    private function _init_() {
        $this->blackListDb = Db::factory('adminMaster');
    }

}