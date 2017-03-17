<?php

namespace widgetmodels;

/**
 * Category
 *
 * @author dongnan
 */
class Category extends \kongfz\ViewModel {

    use \kongfz\traits\Singleton;

    /**
     * 单例
     * @return \widgetmodels\Category
     */
    public static function singleton() {
        return self::instance();
    }

    public function hello() {
        echo "Hello, I'm Category!\n";
    }

    public static function main($query = []) {
        //根据query处理业务数据
        //$data = $model->execute($query);
        $data = [];
        return $data;
    }

}
