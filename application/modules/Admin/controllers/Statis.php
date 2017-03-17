<?php

use http\Response;

/**
 * Class StatisController
 * 运营数据统计接口
 * @author  liubang <liubang@kongfz.com>
 */
class StatisController extends \kongfz\Controller
{
    public function init()
    {
        parent::init();
    }

    public function pushAction()
    {
        $data = $this->getRequest()->getParams();

        if (empty($data['moduleId'])) {
            Response::json(false, 'moduleId不能为空');
        }

        if (empty($data['dataId'])) {
            Response::json(false, 'dataId不能为空');
        }

        if (\services\Statis::push($data)) {
            Response::json(true);
        }

        Response::json(false);
    }
}