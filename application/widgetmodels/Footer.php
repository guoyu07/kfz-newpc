<?php

namespace widgetmodels;

/**
 * Footer
 *
 * @author dongnan
 */
class Footer extends \kongfz\ViewModel {

    use \kongfz\traits\Singleton;

    /**
     * 单例
     * @return \widgetmodels\Footer
     */
    public static function singleton() {
        return self::instance();
    }

    public function friendLink()
    {
        // 公共底部
        $db = \storage\Db::factory('kongv2Master');
        // 获取合作伙伴数据
        $partnersData = $db->select('friendLink', ['linkName', 'linkUrl'], ['AND' => ['showArea[=]' => 'kongfzIndexBottom', 'isHidden' => 0], 'ORDER' => ['sortOrder' => 'DESC']]);

        // 获取友情链接数据
        $friendData = $db->select('friendLink', ['linkName', 'linkUrl'], ['AND' => ['showArea[=]' => 'kongfzIndexFLink', 'isHidden' => 0], 'ORDER' => ['sortOrder' => 'DESC']]);

        // 获取推荐专题数据
        $topicData = $db->select('friendLink', ['linkName', 'linkUrl'], ['AND' => ['showArea[=]' => 'kongfzIndexZtLink', 'isHidden' => 0], 'ORDER' => ['sortOrder' => 'DESC']]);

        $data = ['partners' => $partnersData, 'friends' => $friendData, 'topics' => $topicData];
        return $data;
    }
}
