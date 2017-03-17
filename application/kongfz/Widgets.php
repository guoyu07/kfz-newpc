<?php

namespace kongfz;

use Yaf\Registry as R;
use kongfz\Exception;

/**
 * Widgets
 *
 * @author dongnan
 */
class Widgets {

    use traits\Singleton;

    /**
     * 单例
     * @return \kongfz\Widgets
     */
    public static function singleton() {
        return self::instance();
    }

    /**
     * 模板文件后缀
     * @var String 
     */
    private $viewext;

    /**
     * 视图对象
     * @var View 
     */
    private $view;

    public function _init_() {
        $this->viewext = R::get('g_config')->application->view->ext;
        $this->view    = new View(WIDGETS_DIR);
        $site          = R::get('g_config')->site->toArray();
        $this->view->assign('site', $site);
    }

    public function getTemplateFile($template) {
        $ext = pathinfo($template, PATHINFO_EXTENSION);
        if ($ext) {
            if ($template[0] !== '/') {
                $templateFile = WIDGETS_DIR . $template;
            } else {
                $templateFile = $template;
            }
        } else {
            if ($template[0] !== '/') {
                $templateFile = WIDGETS_DIR . $template . '.' . $this->viewext;
            } else {
                $templateFile = $template . '.' . $this->viewext;
            }
        }
        return $templateFile;
    }

    /**
     * 渲染模板
     * @param string $template 模板
     * @param array $data      模板变量
     * @return string
     * @throws Exception
     */
    public function render($template, array $data = []) {
        $templateFile = $this->getTemplateFile($template);
        // 模板文件不存在直接返回
        if (!is_file($templateFile)) {
            throw new Exception("TEMPLATE '{$templateFile}' IS NOT EXIST!", TEMPLATE_NOT_FOUND);
        }
        return $this->view->render($templateFile, $data);
    }

    /**
     * 渲染页面小部件
     * @param string $widget
     * @param string $tpl
     * @param array $query
     * @return string
     * @throws Exception
     */
    public function widget($widget = null, $tpl = null, array $query = []) {
        $data        = [];
        $widgetClass = '\\widgetmodels\\' . ucfirst($widget);
        if (!class_exists($widgetClass)) {
            //throw new Exception("CLASS '{$widgetClass}' IS NOT EXIST!", CLASS_NOT_FOUND);
        } else {
            $widgetObject = $widgetClass::singleton();
            if (!method_exists($widgetObject, $tpl)) {
                //throw new Exception("METHOD '{$tpl}' OF CLASS '{$widgetClass}' IS NOT EXIST!", METHOD_NOT_FOUND);
            } else {
                $data = call_user_func([$widgetObject, $tpl], $query);
            }
        }
        return $this->render("{$widget}/{$tpl}/{$widget}", $data);
    }

}
