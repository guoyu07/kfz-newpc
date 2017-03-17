<?php

use conf\Module;

/**
 * 运营后台新书模块渲染控制器
 * User: tangrubing
 * Date: 17-2-21
 * Time: 上午10:36
 */

class XinshuController extends AdminBaseController {

    public function init()
    {
        parent::init();
    }

    //近期出版isbn池子
    public function jinqiChubanIndexAction() {
        $this->_view->assign('moduleId', Module::C_JINQICHUBAN);
        $this->display('jinqiChuban/index');
    }

    //近期出版模块设置
    public function jinqiChubanModuleAction() {
        $this->_view->assign('moduleId', Module::C_JINQICHUBAN);
        $this->display('jinqiChuban/module');
    }

    //新书热销榜isbn池子
    public function xinshuRexiaoIndexAction() {
        $this->_view->assign('moduleId', Module::C_XINSHUREMAIBANG);
        $this->display('xinshuRexiao/index');
    }

    //新书热销榜模块设置
    public function xinshuRexiaoModuleAction() {
        $this->_view->assign('moduleId', Module::C_XINSHUREMAIBANG);
        $this->display('xinshuRexiao/module');
    }

    //轮播图-池子
    public function bannerIndexAction() {
        $this->_view->assign('moduleId', Module::C_LUNBO);
        $this->display('banner/index');
    }

    //轮播图-模块设置
    public function bannerModuleAction() {
        $this->_view->assign('moduleId', Module::C_LUNBO);
        $this->display('banner/module');
    }

    //单张广告-池子
    public function singleIndexAction() {
        $this->_view->assign('moduleId', Module::C_GUANGGAO);
        $this->display('single/index');
    }

    //单张广告-模块设置
    public function singleModuleAction() {
        $this->_view->assign('moduleId', Module::C_GUANGGAO);
        $this->display('single/module');
    }

    //低价促销-模块设置
    public function diJiaCuXiaoModuleAction() {
        $this->_view->assign('moduleId', Module::C_DIJIACUXIAO);
        $this->display('diJiaCuXiao/module');
    }

    //推荐专题-池子
    public function tuiJianZhuanTiIndexAction() {
        $this->_view->assign('moduleId', Module::C_TUIJIANZHUANTI);
        $this->display('tuiJianZhuanTi/index');
    }

    //推荐专题-模块设置
    public function tuiJianZhuanTiModuleAction() {
        $this->_view->assign('moduleId', Module::C_TUIJIANZHUANTI);
        $this->display('tuiJianZhuanTi/module');
    }
}