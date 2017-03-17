<?php
namespace services;

class Module {

    /**
     * 设置报警邮件|短信接收人
     * @param $moduleId
     * @param $adminId
     * @param $alarmConditionId
     * @param $type
     * @return bool|int
     */
    public static function setAlarmReciever($moduleId, $adminId, $alarmConditionId, $type) {
        return \AlarmModel::singleton()->addAlarm($adminId, $moduleId, $alarmConditionId, $type);
    }

    /**
     * @param int $moduleId
     * @param int $type
     * @return array|bool|null
     */
    public static function getAlarmReciver($moduleId, $type) {
        $adminIds = \AlarmModel::singleton()->getAlarmRevieverId($moduleId, $type);
        if (!empty($adminIds)) {
            return \SysAdminModel::singleton()->getUserInfoById($adminIds);
        } else {
            return null;
        }
    }

}