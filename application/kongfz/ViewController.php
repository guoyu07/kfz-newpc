<?php

namespace kongfz;

use Yaf\Registry as R;

/**
 * Base Controller
 *
 * @author dongnan
 */
abstract class ViewController extends Controller {

    public function init() {
        $this->initView();
        $site = R::get('g_config')->site->toArray();
        $this->_view->assign('site', $site);
    }

}
