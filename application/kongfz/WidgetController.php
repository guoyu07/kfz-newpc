<?php

namespace kongfz;

/**
 * WidgetController
 *
 * @author dongnan
 */
abstract class WidgetController extends \kongfz\Controller {

    /**
     * 处理请求
     * @return mixed
     * @throws Exception
     */
    public function action() {
        $tpl         = $this->getRequest()->action;
        $widget      = strtolower($this->getRequest()->controller);
        $widgetClass = '\\widgetmodels\\' . ucfirst($widget);
        if (!class_exists($widgetClass)) {
            throw new Exception("CLASS '{$widgetClass}' IS NOT EXIST!", CLASS_NOT_FOUND);
        }
        $widgetObject = $widgetClass::singleton();
        if (!method_exists($widgetObject, $tpl)) {
            throw new Exception("METHOD '{$tpl}' OF CLASS '{$widgetClass}' IS NOT EXIST!", METHOD_NOT_FOUND);
        }
        $query = $this->getRequest()->getQuery();
        $data  = call_user_func([$widgetObject, $tpl], $query);
        \http\Header::noCache();
        switch (REQUEST_SUFFIX) {
            case 'json':
                \http\Header::json();
                echo json_encode($data);
                break;
            case 'html':
            default:
                \http\Header::html();
                echo \kongfz\Widgets::singleton()->render("{$widget}/{$tpl}/{$widget}", $data);
                break;
        }
        exit;
    }

}
