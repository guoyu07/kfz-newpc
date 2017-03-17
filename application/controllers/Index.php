<?php

class IndexController extends \kongfz\ViewController {

    public function init() {
        parent::init();
    }

    public function indexAction() {
        $this->display('index');
    }
    
    public function shopAction() {
    	$this->display('shop');
    }
    
    public function xinshuAction() {
    	$this->display('xinshu');
    }

    public function testAction() {
        var_dump(Yaf\VERSION);
    }

}
