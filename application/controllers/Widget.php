<?php

use Yaf\Registry as R;

class WidgetController extends \kongfz\Controller {

    public function init() {
        parent::init();
    }

    public function defaultAction() {
        $widget      = $this->_request->getParam('name');
        $tpl         = $this->_request->getParam('tpl');
        $suffix      = $this->_request->getParam('suffix');
        $widgetClass = '\\widgetmodels\\' . ucfirst($widget);
        if (!class_exists($widgetClass)) {
            throw new Exception("CLASS '{$widgetClass}' IS NOT EXIST!", CLASS_NOT_FOUND);
        }
        $widgetObject = $widgetClass::singleton();
        if (!method_exists($widgetObject, $tpl)) {
            throw new Exception("METHOD '{$tpl}' OF CLASS '{$widgetClass}' IS NOT EXIST!", METHOD_NOT_FOUND);
        }
        $query = $this->_request->getQuery();
        $data  = call_user_func([$widgetObject, $tpl], $query);
        \http\Header::noCache();
        switch ($suffix) {
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

    /**
     * dev环境前端调试用
     * @throws Exception
     */
    public function devAction() {
        $config = Yaf\Registry::get('g_config')->toArray();
        if ($config['application']['env'] !== 'dev') {
            //如果不是dev环境，直接跳转至首页
            $this->redirect('/');
            exit;
        }
        $widget   = $this->_request->getParam('name');
        $tpl      = $this->_request->getParam('tpl');
        $suffix   = $this->_request->getParam('suffix');
        $datafile = MODELS_DATA_DIR . "widgets/{$widget}/{$tpl}.json";
        if (file_exists($datafile)) {
            $data = json_decode(file_get_contents($datafile), true);
        } else {
            $data = [];
        }
        \http\Header::noCache();
        switch ($suffix) {
            case 'json':
                \http\Header::json();
                echo json_encode($data);
                break;
            case 'phtml':
            case 'html':
            default:
                \http\Header::html();
                $widgetData = [];
                $widgetName = "{$widget}/{$tpl}/{$widget}";
                $jsonfile   = WEBROOT_DIR . "widgets/{$widgetName}.json";
                if (file_exists($jsonfile)) {
                    $widgetData = json_decode(file_get_contents($jsonfile), true);
                }
                $widgetData['site'] = Yaf\Registry::get('g_config')->site->toArray();
                $content            = \kongfz\Widgets::singleton()->render("{$widgetName}.{$suffix}", (array) $data);
                if (isset($widgetData['widgetDeps']) && is_array($widgetData['widgetDeps'])) {
                    foreach ($widgetData['widgetDeps'] as $widgetDep) {
                        $tmpArr        = explode('/', $widgetDep);
                        $widgetDepName = "{$tmpArr[0]}/{$tmpArr[1]}/{$tmpArr[0]}";
                        if (!file_exists(WEBROOT_DIR . "widgets/{$widgetDepName}.{$suffix}")) {
                            throw new Exception("TEMPLATE 'widgets/{$widgetDepName}.{$suffix}' IS NOT EXIST!", TEMPLATE_NOT_FOUND);
                        }
                        $widgetDepHtml = \kongfz\Widgets::singleton()->render("{$widgetDepName}.{$suffix}", (array) $data);
                        if (file_exists(WEBROOT_DIR . "widgets/{$widgetDepName}.js")) {
                            $widgetData['jsDeps'][] = "widgets/{$widgetDepName}.js";
                        }
                        if (file_exists(WEBROOT_DIR . "widgets/{$widgetDepName}.less")) {
                            $widgetData['cssDeps'][] = "widgets/{$widgetDepName}.less";
                        } elseif (file_exists(WEBROOT_DIR . "widgets/{$widgetDepName}.css")) {
                            $widgetData['cssDeps'][] = "widgets/{$widgetDepName}.css";
                        }
                        $content = preg_replace("#@@include\(\s*'widgets/{$widgetDepName}\.{$suffix}'\s*\)#", $widgetDepHtml, $content);
                    }
                }
                if (file_exists(WEBROOT_DIR . "widgets/{$widgetName}.js")) {
                    $widgetData['jsDeps'][] = "widgets/{$widgetName}.js";
                }
                if (file_exists(WEBROOT_DIR . "widgets/{$widgetName}.less")) {
                    $widgetData['cssDeps'][] = "widgets/{$widgetName}.less";
                } elseif (file_exists(WEBROOT_DIR . "widgets/{$widgetName}.css")) {
                    $widgetData['cssDeps'][] = "widgets/{$widgetName}.css";
                }
                $layout = new \kongfz\Layout('widget');
                $layout->display($widgetData, $content);
                break;
        }
        exit;
    }

    /**
     * dev环境widget文档
     * @throws Exception
     */
    public function docAction() {
        $config = Yaf\Registry::get('g_config')->toArray();
        if ($config['application']['env'] !== 'dev') {
            //如果不是dev环境，直接跳转至首页
            $this->redirect('/');
            exit;
        }
        \http\Header::noCache();
        $parsedown = new Parsedown();
        $widgets   = [];
        $dirHandle = opendir(WIDGETS_DIR);
        while (($widget    = readdir($dirHandle)) !== false) {
            if (in_array($widget, ['.', '..'])) {
                continue;
            }
            if (is_dir(WIDGETS_DIR . $widget)) {
                $widgets[$widget] = [];
                $subDirHandle     = opendir(WIDGETS_DIR . $widget);
                while (($name             = readdir($subDirHandle)) !== false) {
                    if (in_array($name, ['.', '..'])) {
                        continue;
                    }
                    if (is_dir(WIDGETS_DIR . $widget . '/' . $name)) {
                        $widgetData = ['json' => [], 'readme' => ''];
                        $jsonfile   = WIDGETS_DIR . $widget . '/' . $name . "/{$widget}.json";
                        if (file_exists($jsonfile)) {
                            $widgetData['json'] = json_decode(file_get_contents($jsonfile), true);
                        }
                        $readmefile = WIDGETS_DIR . $widget . '/' . $name . "/README.md";
                        if (file_exists($readmefile)) {
                            $widgetData['readme'] = $parsedown->text(file_get_contents($readmefile));
                            if (file_exists(WIDGETS_DIR . $widget . '/' . $name . "/{$widget}.phtml")) {
                                $widgetData['readme'] = str_replace('<!--example|DO NOT CHANGE!-->', "<a href='/widget/{$widget}-{$name}.phtml' target='_blank'>模块预览</a>", $widgetData['readme']);
                            } elseif (file_exists(WIDGETS_DIR . $widget . '/' . $name . "/{$widget}.html")) {
                                $widgetData['readme'] = str_replace('<!--example|DO NOT CHANGE!-->', "<a href='/widget/{$widget}-{$name}.phtml' target='_blank'>模块预览</a>", $widgetData['readme']);
                            } else {
                                $widgetData['readme'] = str_replace('<!--example|DO NOT CHANGE!-->', "<span>模块暂时无法预览哦</span>", $widgetData['readme']);
                            }
                        }
                        if (!empty($widgetData['json']) || !empty($widgetData['readme'])) {
                            $widgets[$widget][$name] = $widgetData;
                        }
                    }
                }
                closedir($subDirHandle);
            }
        }
        closedir($dirHandle);

        $this->initView();
        $site = R::get('g_config')->site->toArray();
        $this->_view->assign('site', $site);
        $this->_view->assign('widgets', $widgets);
        $this->display('doc');
    }

}
