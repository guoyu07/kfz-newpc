<?php

namespace widgetmodels;

/**
 * Header
 *
 * @author dongnan
 */
class Header extends \kongfz\ViewModel {

    use \kongfz\traits\Singleton;

    /**
     * 单例
     * @return \widgetmodels\Header
     */
    public static function singleton() {
        return self::instance();
    }

    public function hello() {
        echo "Hello, I'm Header!\n";
    }

    public function main($query = []) {
        //根据query处理业务数据
        //$data = $model->execute($query);
        $data = [];
        return $data;
    }

}
