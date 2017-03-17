<?php
/**
 * 专题页控制器
 * Created by PhpStorm.
 * User: tangrubing
 * Date: 17-2-21
 * Time: 下午3:09
 */

class IndexController extends \kongfz\ViewController {

    public function init()
    {
        parent::init();
    }

    public function indexAction() {
        $this->display('zhuanti');
    }
}