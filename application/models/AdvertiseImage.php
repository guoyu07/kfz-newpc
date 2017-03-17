<?php
use kongfz\Model;
use services\Error;

/**
 * Class AdvertiseImageModel
 * 运营后台广告图数据
 * @author  liubang <liubang@kongfz.com>
 */
class AdvertiseImageModel extends Model {

    use \kongfz\traits\Singleton;

    /** @var string 当前操作的库 */
    private static $database = 'adminMaster';

    /** @var string 当前操作的表 */
    private static $table = 'advertiseImage';

    /** @var null | \Medoo\Medoo */
    private $imgDb = null;

    /**
     * @return AdvertiseImageModel
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

    private function _init_() {
        $this->imgDb = storage\Db::factory(self::$database);
    }

    /**
     * 修改广告图信息
     * @param int $showType
     * @param $data
     * @return bool|int
     */
    public function updateAdvertiseImgInfo($showType, $data) {

        if ($showType == '0') {
            if (!isset($data['title']) || empty($data['title'])) {
                Error::set('标题不能为空');
                return false;
            }
            if (!isset($data['secondDesc']) || empty($data['secondDesc'])) {
                Error::set('描述2不能为空');
                return false;
            }
        } else {
            if (empty($data['imgUrl'])) {
                Error::set('图片地址不能为空');
                return false;
            }
        }

        if (empty($data['confId'])) {
            Error::set('confId不能为空');
            return false;
        }

        if (empty($data['firstDesc'])) {
            Error::set('广告描述1不能为空');
            return false;
        }

        if (empty($data['linkUrl'])) {
            Error::set('链接地址不能为空');
            return false;
        }

        if (!preg_match('/^(http|https):\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/i',
            $data['linkUrl'])
        ) {
            Error::set('链接地址格式错误');
            return false;
        }

        $dataId = OperationConfigModel::singleton()->getDataIdByConfId($data['confId']);

        if (empty($dataId)) {
            Error::set('您操作的数据不存在或已删除，请刷新后重试');
            return false;
        }

        $old = $this->imgDb->get(self::$table, '*', ['imgId' => $dataId]);

        if (empty($old)) {
            Error::set('您操作的数据不存在或已删除，请刷新后重试');
            return false;
        }

        //过滤数据
        $arr = [
            'title'      => !empty($data['title']) ? $data['title'] : '',
            'firstDesc'  => !empty($data['firstDesc']) ? $data['firstDesc'] : '',
            'secondDesc' => !empty($data['secondDesc']) ? $data['secondDesc'] : '',
            'imgUrl'     => (isset($data['imgUrl']) && !empty($data['imgUrl'])) ? $data['imgUrl'] : '',
            'linkUrl'    => $data['linkUrl'],
            'addTime'    => time()
        ];

        $res = $this->imgDb->update(self::$table, $arr, [
            'imgId' => $dataId
        ]);

        if (false !== $res) {
            $recorder = new \services\Recorder($data['moduleId'], 'editModuleData');
            $recorder->assign('moduleId', $data['moduleId'])
                ->assign('message', \services\Recorder::diff($arr, $old))
                ->recorde();
            return true;
        } else {
            Error::set('修改广告图信息失败');
            return false;
        }
    }

    public function addImg($data, $showType) {
        if ($showType == '0') {
            if (!isset($data['title']) || empty($data['title'])) {
                Error::set('标题不能为空');
                return false;
            }
            if (!isset($data['secondDesc']) || empty($data['secondDesc'])) {
                Error::set('描述2不能为空');
                return false;
            }
        } else {
            if (empty($data['imgUrl'])) {
                Error::set('图片地址不能为空');
                return false;
            }
        }

        if (!isset($data['firstDesc']) || empty($data['firstDesc'])) {
            Error::set('描述1不能为空');
            return false;
        }

        if (empty($data['linkUrl'])) {
            Error::set('链接地址不能为空');
            return false;
        }

        if (!preg_match('/^(http|https):\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/i',
            $data['linkUrl'])
        ) {
            Error::set('链接地址格式错误');
            return false;
        }

        $arr = [
            'title'      => !empty($data['title']) ? $data['title'] : '',
            'firstDesc'  => !empty($data['firstDesc']) ? $data['firstDesc'] : '',
            'secondDesc' => !empty($data['secondDesc']) ? $data['secondDesc'] : '',
            'imgUrl'     => (isset($data['imgUrl']) && !empty($data['imgUrl'])) ? $data['imgUrl'] : '',
            'linkUrl'    => $data['linkUrl'],
            'addTime'    => time()
        ];

        if ($this->imgDb->insert(self::$table, $arr)) {
            $imgId = $this->imgDb->id();
            //日志
            $recorder = new \services\Recorder($data['moduleId'], 'addModuleData');
            $recorder->assign('moduleId', $data['moduleId'])
                ->assign('message', \services\Recorder::arrToString($arr))
                ->recorde();
            return $imgId;
        } else {
            Error::set("添加图片信息失败");
            return false;
        }
    }

    /**
     * 获取广告图
     * @param $ids
     * @return array
     */
    public function getImgByIds($ids) {
        $fields = [
            'imgId', 'title', 'firstDesc', 'secondDesc', 'imgUrl', 'linkUrl', 'addTime', 'params'
        ];
        $where = [
            'AND'   => [
                'imgId'    => $ids,
                'isDelete' => '0'
            ],
            'ORDER' => [
                'imgId' => $ids
            ]
        ];
        $result = $this->imgDb->select(self::$table, $fields, $where);

        if (!empty($result)) {
            $site = Yaf\Registry::get('g_config')->site->toArray();
            $prefix = $site['dav'] . 'newpc/';
            foreach ($result as &$row) {
                !empty($row['addTime']) && ($row['addTime'] = date('Y-m-d H:i:s', $row['addTime']));
                $row['prefix'] = $prefix;
                if (!empty($row['imgUrl'])) {
                    $row['imgUrl'] = str_replace($prefix, '', $row['imgUrl']);
                }
            }
        } else {
            $result = [];
        }
        $result = array_combine(array_column($result, 'imgId'), $result);
        return $result;
    }
}