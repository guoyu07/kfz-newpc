<?php
use http\Response;

/**
 * 运营后台公共控制器
 *
 * @author  liubang <liubang@kongfz.com>
 */
class AdminBaseController extends kongfz\Controller
{
    public function init()
    {
        parent::init();
        //登录检查
//        $this->loginCheck();
//
//        //自动校验权限
//        $module = $this->getRequest()->getModuleName();
//        $controller = $this->getRequest()->getControllerName();
//        $action = $this->getRequest()->getActionName();
//        $key = $module . "::" . $controller . "Controller::" . $action . 'Action';
//        $code = \services\Privileges::getPrivilegeCode($key);
//        if (!empty($code)) {
//            $this->privilegeCheck($code);
//        }

        //根据权限生成可操作的导航
        $this->generateNav();
        /** @var array $site */
        $site = Yaf\Application::app()->getConfig()->site->toArray();
        $this->getView()->assign('site', $site);
    }


    protected function generateNav()
    {
        //TODO
    }


    /**
     * 登录校验
     */
    protected function loginCheck()
    {
        $adminId = isset($_SESSION['adminId']) ? $_SESSION['adminId'] : 0;

        if (!$adminId) {
            if ($this->getRequest()->isXmlHttpRequest()) {
                Response::json(false, '请登录', [], Response::ERR_AJAX_NO_LOGIN);
            } else {
                $this->redirect('/admin/login/index');
            }
        }
        exit;
    }


    /**
     * 权限校验
     * @param $code
     */
    protected function privilegeCheck($code)
    {
        if (!\services\Privileges::has($code)) {
            $this->illegal();
        }
    }


    /**
     * 非法操作处理
     */
    protected function illegal()
    {

        if ($this->getRequest()->isXmlHttpRequest()) {
            //TODO 待定
            Response::json(false, '您无权操作');
        }

        echo '无权访问！';

        //TODO 跳转到一个提示页面

        exit;

    }

}
