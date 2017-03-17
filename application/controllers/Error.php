<?php

/**
 * Description of Error
 *
 * @author dongnan
 */
class ErrorController extends \kongfz\Controller {

    public function init() {
        
    }

    /**
     * 异常处理
     */
    public function errorAction() {
        $exception = $this->getRequest()->getException();
        
//        switch ($exception->getCode()) {
//            case Yaf\ERR\NOTFOUD\MODULE:
//            case Yaf\ERR\NOTFOUD\CONTROLLER:
//            case Yaf\ERR\NOTFOUD\ACTION:
//            case Yaf\ERR\NOTFOUD\VIEW:
//                //404
//                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
//                break;
//        }
        var_dump($exception);
    }

}
