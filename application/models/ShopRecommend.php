<?php
use services\Error;
use storage\Db;

/**
 * Created by PhpStorm.
 * User: diao
 * Date: 17-2-14
 * Time: 下午12:26
 */
class ShopRecommendModel extends \kongfz\Model {
    private static $database = 'adminMaster';
    private static $table = 'shopRecommend';
    /** @var  null | \Medoo\Medoo */
    private $shopDB;

    use \kongfz\traits\Singleton;

    /**
     * @return ShopRecommendModel
     */
    public static function singleton() {
        return self::instance();
    }

    private function _init_() {
        $this->shopDB = Db::factory(self::$database);
    }

    public function add($params) {
        if (empty($params['shopName'])) {
            Error::set('shopName不能为空');
            return false;
        }
        if (empty($params['recommend'])) {
            Error::set('recommend不能为空');
            return false;
        }
        if (empty($params['dataId'])) {
            Error::set('shopId不能为空');
            return false;
        }

        $arr = [
            'shopName'  => $params['shopName'],
            'recommend' => $params['recommend'],
            'shopId'    => $params['dataId'],
            'addTime'   => time()
        ];
        if ($this->shopDB->insert(self::$table, $arr)) {
            $id = $this->shopDB->id();
            $recorder = new \services\Recorder($params['moduleId'], 'addModuleData');
            $recorder->assign('moduleId', $params['moduleId'])
                ->assign('message', \services\Recorder::arrToString($arr))
                ->recorde("添加了模块的推荐店铺数据，moduleId为：{moduleId}, 数据为：{message}");
            return $id;
        } else {
            Error::set('店铺信息保存失败');
            return false;
        }
    }

    public function getDataByIds($ids) {
        if (empty($ids)) {
            Error::set('params不能为空');
            return false;
        }
        $result = $this->shopDB->select(self::$table, ['id', 'shopId', 'shopName', 'recommend'], ['id' => $ids]);
        return $result;
    }

    public function editShopInfo($params) {

        if (empty($params['confId'])) {
            Error::set('confId不能为空');
            return false;
        }

        if (empty($params['recommend'])) {
            Error::set('recommend不能为空');
            return false;
        }

        $dataId = OperationConfigModel::singleton()->getDataIdByConfId($params['confId']);
        if (empty($dataId)) {
            Error::set('您操作的数据不存在或已删除，请刷新后重试');
            return false;
        }

        $old = $this->shopDB->get(self::$table, '*', ['id' => $dataId]);
        if (empty($old)) {
            Error::set('您操作的数据不存在或已删除，请刷新后重试');
            return false;
        }

        $arr = [
            'recommend' => $params['recommend']
        ];

        //比对修改前后的变化, 无变化则直接返回
        if ($old['recommend'] == $arr['recommend']) {
            return true;
        }

        $res = $this->shopDB->update(self::$table, $arr, ['id' => $dataId]);
        if (false !== $res) {
            //记录操作日志
            $recorder = new \services\Recorder($params['moduleId'], 'editModuleData');
            $recorder->assign('moduleId', $params['moduleId'])
                ->assign('id', $dataId)
                ->assign('message', \services\Recorder::diff($arr, $old))
                ->recorde('修改了模块的推荐店铺数据，moduleId为：{moduleId}, id为：{id}, 修改为：{message}');
            return true;
        }
        return false;
    }
}