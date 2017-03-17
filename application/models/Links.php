<?php
/*
 |------------------------------------------------------------------
 | kfz-newpc
 |------------------------------------------------------------------
 | @author    : liubang
 | @date      : 2017/3/1 上午10:48
 | @copyright : (c) kongfz.com
 |------------------------------------------------------------------
 */

use services\Error as E;

class LinksModel extends \kongfz\Model
{
    /** @var string 当前model操作的数据库 */
    private static $database = 'adminMaster';

    /** @var string 当前model操作的表 */
    private static $table = 'links';

    /** @var null | \Medoo\Medoo */
    private $linksDb = null;

    use \kongfz\traits\Singleton;

    private function _init_() {
        $this->linksDb = storage\Db::factory(self::$database);
    }

    /** @return LinksModel */
    public static function singleton() {
        return self::instance();
    }

    /**
     * @param $data
     *
     * @return bool|int|string
     */
    public function addLinks($data) {
        if (empty($data['linkTitle'])) {
            E::set('链接标题不能为空');
            return false;
        }

        if (empty($data['linkUrl'])) {
            E::set('链接不能为空');
            return false;
        }

        if (!preg_match('/^(http|https):\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/i',
            $data['linkUrl'])
        ) {
            E::set('链接地址格式错误');
            return false;
        }

        $arr = [
            'linkTitle' => $data['linkTitle'],
            'linkUrl' => $data['linkUrl']
        ];

        if (false !== $this->linksDb->insert(self::$table, $arr)) {
            //这里的获取id必须紧跟insert之后，否则会取到其他insert后的id
            $id = $this->linksDb->id();
            $recorder = new \services\Recorder($data['moduleId'], 'addModuleData');
            $recorder->assign('moduleId', $data['moduleId'])
                ->assign('message', \services\Recorder::arrToString($arr))
                ->recorde('添加了模块的超链接数据，moduleId为：{moduleId}，数据为：{message}');
            return $id;
        }

        return false;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function update($data) {
        if (empty($data['linkTitle'])) {
            E::set('链接标题不能为空');
            return false;
        }

        if (empty($data['linkUrl'])) {
            E::set('链接不能为空');
            return false;
        }

        if (!preg_match('/^(http|https):\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/i',
            $data['linkUrl'])
        ) {
            E::set('链接地址格式错误');
            return false;
        }
        $dataId = OperationConfigModel::singleton()->getDataIdByConfId($data['confId']);
        $old = $this->linksDb->get(self::$table, ['linkId' => $dataId]);
        if (empty($old)) {
            E::set('您操作的数据不存在或已删除，请刷新后重试');
            return false;
        }
        $arr = [
            'linkTitle' => $data['linkTitle'],
            'linkUrl' => $data['linkUrl']
        ];

        $res = $this->linksDb->update(self::$table, $arr, ['linkId' => $dataId]);
        if (false !== $res) {
            $recorder = new \services\Recorder($data['moduleId'], 'editModuleData');
            $recorder->assign('moduleId', $data['moduleId'])
                ->assign('message', \services\Recorder::diff($arr, $old))
                ->recorde();
            return true;
        }
        return false;
    }

    /**
     * 批量获取超链接
     * @param $linkIds
     *
     * @return array
     */
    public function getLinkByIds($linkIds) {
        $where = [
            'AND' => [
                'linkId' => $linkIds,
            ],
            'ORDER' => [
                'linkId' => $linkIds
            ]
        ];

        $result = $this->linksDb->select(self::$table, '*', $where);
        return $result;
    }
}