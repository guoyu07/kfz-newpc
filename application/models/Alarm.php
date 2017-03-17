<?php
use kongfz\Model;
use storage\Db;
use services\Error;

/**
 * 运营数据报警监控
 *
 * @author  liubang <liubang@kongfz.com>
 */
class AlarmModel extends Model
{
    /** @var int email提醒 */
    const TYPE_EMAIL = 1;

    /** @var int 手机号码 */
    const TYPE_MOBILE = 2;

    /** @var int 数据日报接收 邮箱 */
    const TYPE_STATIS = 3;

    /** @var null | \Medoo\Medoo */
    private $alarmDb = null;

    /** @var string 当前model操作的数据库 */
    private static $database = 'adminMaster';

    /** @var string 当前model操作的表 */
    private static $table = 'alarm';

    use \kongfz\traits\Singleton;

    /**
     * @return AlarmModel
     */
    public static function singleton()
    {
        return self::instance();
    }


    private function _init_()
    {
        $this->alarmDb = Db::factory(self::$database);
    }

    /**
     * 添加一个监控报警,关联报警人和报警条件
     * @param int $adminId          报警人id
     * @param int $module           监控模块id
     * @param int $alarmConditionId 报警条件id
     * @param int $type             提醒类型
     * @return bool | int
     */
    public function addAlarm($adminId, $module, $alarmConditionId, $type = AlarmModel::TYPE_EMAIL)
    {
        $adminId = (int)$adminId;
        $module = (int)$module;
        $alarmConditionId = (int)$alarmConditionId;

        if ($adminId == 0) {
            Error::set('用户id不能为空');
            return false;
        }

        if ($module == 0) {
            Error::set('moduleId不能为空');
            return false;
        }

        if ($alarmConditionId == 0) {
            Error::set('报警条件不能为空');
            return false;
        }

        if ($this->alarmDb->insert(self::$table, [
            'moduleId' => $module,
            'cid'      => $alarmConditionId,
            'adminId'  => $adminId,
            'type'     => $type,
            'addTime'  => time()
        ])) {
            return $this->alarmDb->id();
        } else {
            Error::set('写入报警信息失败');
            return false;
        }
    }


    /**
     * 根据模块id获取所有监控内容
     * @param $moduleId
     * @return array|bool
     */
    public function getAlarmsByModuleId($moduleId)
    {
        $moduleId = (int)$moduleId;
        if ($moduleId == 0) {
            Error::set("moduleId不能为空");
            return false;
        }

        // select * from alarm left join alarmCondition using(cid) where alarm.moduleId=$moduleId;
        return $this->alarmDb->select(self::$table,
            [
                "[>]alarmCondition" => 'cid'
            ],
            [
                "alarm.moduleId" => $moduleId
            ]
        );
    }

    /**
     * 获取邮件|短信报警接收人
     * @param int $moduleId
     * @param int $type
     * @return array|bool
     */
    public function getAlarmRevieverId($moduleId, $type)
    {
        $moduleId = (int)$moduleId;

        if (!$moduleId) {
            Error::set("moduleId不能为空");
            return false;
        }

        return $this->alarmDb->select(self::$table, 'adminId',
            [
                'AND' => [
                    'moduleId' => $moduleId,
                    'type' => $type
                ]
            ]
        );
    }



}