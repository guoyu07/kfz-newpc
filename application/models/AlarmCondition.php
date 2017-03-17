<?php

use kongfz\Model;
use storage\Db;
use services\Error;

/**
 * Class AlarmConditionModel
 * 运营数据报警监控规则
 *
 * @author  liubang <liubang@kongfz.com>
 */
class AlarmConditionModel extends Model
{

    /** @var int, 比较类型，小于 < */
    const TYPE_LESS_THEN = 1;

    /** @var int, 比较类型，不大于 <= */
    const TYPE_NOT_MORE_THEN = 2;

    /** @var int, 比较类型，大于 > */
    const TYPE_MORE_THEN = 3;

    /** @var int, 比较类型，不小于 >= */
    const TYPE_NOT_LESS_THEN = 4;

    /** @var int 高级，短信+邮件 */
    const LEVEL_HIGHT = 1;

    /** @var int 低级，仅邮件 */
    const LEVEL_LOW = 2;

    /** @var null | \Medoo\Medoo */
    private $alarmConditionDb = null;

    use \kongfz\traits\Singleton;

    /**
     * @return \kongfz\traits\Singleton | AlarmConditionModel
     */
    public static function singleton()
    {
        return self::instance();
    }

    private function _init_()
    {
        $this->alarmConditionDb = Db::factory('adminMaster');
    }


    /**
     * 添加一个报警条件
     * @param int    $moduleId 模块id
     * @param string $title    条件名
     * @param int    $num      对比数据
     * @param int    $type     对比类型
     * @return array|bool|mixed
     */
    public function addAlarmCondition($moduleId, $title, $num, $type = AlarmConditionModel::TYPE_LESS_THEN)
    {
        $moduleId = (int)$moduleId;
        $num = (int)$num;

        if ($moduleId == 0) {
            Error::set('模块id不能为空');
            return false;
        }

        if ($num == 0) {
            Error::set('对比数据不能为空');
            return false;
        }
        if ($this->alarmConditionDb->insert('alarmCondition', [
            'moduleId'       => $moduleId,
            'title'          => trim($title),
            'comparisonType' => $type,
            'comparisonNum'  => $num,
            'addTime'        => time()
        ])) {
            return $this->alarmConditionDb->id();
        } else {
            Error::set('写入报警条件信息失败');
            return false;
        }
    }
}