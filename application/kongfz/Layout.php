<?php

namespace kongfz;

/**
 * Layout
 *
 * @author DongNan <dongyh@126.com>
 */
class Layout {

    /**
     * 布局模板路径
     * @var string 
     */
    private $layout = '';

    /**
     * 模板文件后缀
     * @var string 
     */
    private $viewext;

    /**
     *
     * @var View 
     */
    private $view;

    /**
     * 构造Layout对象
     * @param string $layout    layout模板名称
     */
    public function __construct($layout = 'default') {
        $this->view    = new View(LAYOUT_DIR);
        $this->view->setScriptPath(LAYOUT_DIR);
        $this->viewext = \Yaf\Registry::get('g_config')->application->view->ext;
        $ext           = pathinfo($layout, PATHINFO_EXTENSION);
        if ($ext) {
            $this->layout = $layout;
        } else {
            $this->layout = $layout . '.' . $this->viewext;
        }
    }

    /**
     * 渲染模板
     * @param array $data       layout模板数据
     * @param string $content   页面内容
     * @return string 渲染后的页面
     */
    public function render(array $data = [], $content = '') {
        return $this->view->render($this->layout, array_merge($data, ['_CONTENT_' => $content]));
    }

    /**
     * 渲染并输出模板
     * @param array $data       layout模板数据
     * @param string $content   页面内容
     * @return boolean
     */
    public function display(array $data = [], $content = '') {
        $this->view->display($this->layout, array_merge($data, ['_CONTENT_' => $content]));
    }

}
