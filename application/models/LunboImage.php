<?php
use kongfz\Model;
use services\Error;

/**
 * Class LunboImageModel
 * 运营后台轮播图数据
 * @author  liubang <liubang@kongfz.com>
 */
class LunboImageModel extends Model {

    use \kongfz\traits\Singleton;

    /** @var string 当前操作的库 */
    private static $database = 'adminMaster';

    /** @var string 当前操作的表 */
    private static $table = 'lunboImage';

    /** @var null | \Medoo\Medoo */
    private $imgDb = null;

    /**
     * @return LunboImageModel
     */
    public static function singleton() {
        return self::instance();
    }

    private function _init_() {
        $this->imgDb = storage\Db::factory(self::$database);
    }

    /**
     * 修改轮播图信息
     * @param $data
     * @return bool|int
     */
    public function updateLunboImgInfo($data) {

        if (empty($data['confId'])) {
            Error::set('confId不能为空');
            return false;
        }


        if (empty($data['imgUrl'])) {
            Error::set('图片地址不能为空');
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

        if (!empty($data['bgcolor'])) {
            if (!preg_match('/#[0-9a-f]{6}/is', $data['bgcolor'])) {
                Error::set('图片背景色格式错误');
                return false;
            }
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
            'description' => !empty($data['description']) ? $data['description'] : '',
            'imgUrl'      => $data['imgUrl'],
            'linkUrl'     => $data['linkUrl'],
            'bgcolor'     => !empty($data['bgcolor']) ? $data['bgcolor'] : '#ffffff',
        ];

        //比对修改前后的变化，记录日志
        if ($old['description'] == $arr['description'] && $old['imgUrl'] == $arr['imgUrl']
            && $old['linkUrl'] == $arr['linkUrl'] && $old['bgcolor'] == $arr['bgcolor']) {
            return true;
        }

        $res = $this->imgDb->update(self::$table, $arr, [
            'imgId' => $dataId
        ]);
        if (false !== $res) {
            $recorder = new \services\Recorder($data['moduleId'], 'editModuleData');
            $recorder->assign('moduleId', $data['moduleId'])
                ->assign('message', \services\Recorder::diff($arr, $old))
                ->recorde();
            return true;
        }
        return false;
    }

    public function addImg($data) {

        if (empty($data['imgUrl'])) {
            Error::set('图片地址不能为空');
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

        if (!empty($data['bgcolor'])) {
            if (!preg_match('/#[0-9a-f]{6}/is', $data['bgcolor'])) {
                Error::set('图片背景色格式错误');
                return false;
            }
        }

        $arr = [
            'description' => !empty($data['description']) ? $data['description'] : '',
            'imgUrl'      => $data['imgUrl'],
            'linkUrl'     => $data['linkUrl'],
            'bgcolor'     => !empty($data['bgcolor']) ? $data['bgcolor'] : '#ffffff',
            'addTime'     => time()
        ];

        if ($this->imgDb->insert(self::$table, $arr)) {
            $imgId = $this->imgDb->id();
            //写日志
            $recorder = new \services\Recorder($data['moduleId'], 'addModuleData');
            $recorder->assign('moduleId', $data['moduleId'])
                ->assign('message', \services\Recorder::arrToString($arr))
                ->recorde();
            return $imgId;
        } else {
            Error::set('写入图片信息失败');
            return false;
        }
    }

    /**
     * 获取轮播图
     * @param $ids
     * @return array
     */
    public function getImgByIds($ids) {
        $fields = [
            'imgId', 'description', 'imgUrl', 'linkUrl', 'bgcolor', 'addTime', 'params'
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
            /** @var array $site */
            $site = Yaf\Registry::get('g_config')->site->toArray();
            $prefix = $site['dav'] . 'newpc/';
            foreach ($result as &$row) {
                !empty($row['addTime']) && ($row['addTime'] = date('Y-m-d H:i:s', $row['addTime']));
                //!empty($row['updateTime']) && ($row['updateTime'] = date('Y-m-d H:i:s', $row['updateTime']));
                $row['prefix'] = $prefix;
                $row['imgUrl'] = str_replace($prefix, '', $row['imgUrl']);
            }
        } else {
            $result = [];
        }
        //$result = array_combine(array_column($result, 'imgId'), $result);
        return $result;
    }
}