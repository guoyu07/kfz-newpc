<?php

use conf\Module;
use services\Data;

/**
 * Class IndexController
 * 运营后台大首页模块页面渲染控制器
 * @author  liubang <liubang@kongfz.com>
 */
class IndexController extends AdminBaseController {
    public function init() {
        parent::init();
        $site = Yaf\Registry::get('g_config')->site->toArray();
        $this->_view->assign("site", $site);
    }

    public function indexAction() {
        $this->display('index');
    }

    //好书推荐isbn池子
    public function haoshuTuijianIsbnAction() {
        $this->_view->assign('moduleId', Module::A_HAOSHUTUIJIAN);
        $this->display('haoshuTuijian/isbn');
    }

    //好书推荐模块设置
    public function haoshuTuijianModuleAction() {
        //好书推荐模块moduleId
        $this->_view->assign('moduleId', Module::A_HAOSHUTUIJIAN);
        $this->display('haoshuTuijian/module');
    }

    //新书主栏isbn池子
    public function xinshuIsbnAction() {
        // 新书moduleId
        $this->_view->assign('parentId', Module::A_XINSHU);
        //近期出版moduleId
        $this->_view->assign('moduleId1', Module::A_XINSHU_JINQICHUBAN);
        //销售排行moduleId
        $this->_view->assign('moduleId2', Module::A_XINSHU_XIAOSHOUPAIHANG);
        $this->display('xinshu/isbn');
    }

    //新书右侧isbn池子
    public function xinshuRpondAction() {
        // 新书moduleId
        $this->_view->assign('parentId', Module::A_XINSHU);
        //近期出版moduleId
        $this->_view->assign('moduleId1', Module::A_XINSHU_JINQICHUBAN);
        //销售排行moduleId
        $this->_view->assign('moduleId2', Module::A_XINSHU_XIAOSHOUPAIHANG);
        $this->display('xinshu/rpond');
    }

    //新书url池子
    public function xinshuIndexAction() {
        // 新书moduleId
        $this->_view->assign('parentId', Module::A_XINSHU);
        //近期出版moduleId
        $this->_view->assign('moduleId1', Module::A_XINSHU_JINQICHUBAN);
        //销售排行moduleId
        $this->_view->assign('moduleId2', Module::A_XINSHU_XIAOSHOUPAIHANG);
        $this->display('xinshu/index');
    }

    //新书模块设置
    public function xinshuModuleAction() {
        // 新书moduleId
        $this->_view->assign('parentId', Module::A_XINSHU);
        //近期出版moduleId
        $this->_view->assign('moduleId1', Module::A_XINSHU_JINQICHUBAN);
        //销售排行moduleId
        $this->_view->assign('moduleId2', Module::A_XINSHU_XIAOSHOUPAIHANG);
        $this->display('xinshu/module');
    }

    //旧书url池子
    public function jiushuIndexAction() {
        //旧书moduleId
        $this->_view->assign('parentId', Module::A_JIUSHU);
        //最受欢迎moduleId
        $this->_view->assign('moduleId1', Module::A_JIUSHU_ZUISHOUGUANZHUTUSHU);
        //书店推荐moduleId
        $this->_view->assign('moduleId2', Module::A_JIUSHU_SHUDIANTUIJIAN);
        $this->display('jiushu/index');
    }

    //旧书主栏isbn池子
    public function jiushuIsbnAction() {
        //旧书moduleId
        $this->_view->assign('parentId', Module::A_JIUSHU);
        //最受欢迎moduleId
        $this->_view->assign('moduleId1', Module::A_JIUSHU_ZUISHOUGUANZHUTUSHU);
        //书店推荐moduleId
        $this->_view->assign('moduleId2', Module::A_JIUSHU_SHUDIANTUIJIAN);
        $this->display('jiushu/isbn');
    }

    //旧书右侧书店池子
    public function jiushuRpondAction() {
        //旧书moduleId
        $this->_view->assign('parentId', Module::A_JIUSHU);
        //最受欢迎moduleId
        $this->_view->assign('moduleId1', Module::A_JIUSHU_ZUISHOUGUANZHUTUSHU);
        //书店推荐moduleId
        $this->_view->assign('moduleId2', Module::A_JIUSHU_SHUDIANTUIJIAN);
        $this->display('jiushu/rpond');
    }

    //旧书模块设置
    public function jiushuModuleAction() {
        //旧书moduleId
        $this->_view->assign('parentId', Module::A_JIUSHU);
        //最受欢迎moduleId
        $this->_view->assign('moduleId1', Module::A_JIUSHU_ZUISHOUGUANZHUTUSHU);
        //书店推荐moduleId
        $this->_view->assign('moduleId2', Module::A_JIUSHU_SHUDIANTUIJIAN);
        $this->display('jiushu/module');
    }


    // 轮播图
    public function bannerIndexAction() {
        $this->_view->assign('moduleId', Module::A_LUNBO);
        $this->display('banner/index');
    }

    //轮播图模块设置
    public function bannerModuleAction() {
        $this->_view->assign('moduleId', Module::A_LUNBO);
        $this->display('banner/module');
    }

    //广告位图
    public function advertisementIndexAction() {
        $this->_view->assign('moduleId', Module::A_GUANGGAO);
        $this->display('advertisement/index');
    }

    //广告位图模块设置
    public function advertisementModuleAction() {
        $this->_view->assign('moduleId', Module::A_GUANGGAO);
        $this->display('advertisement/module');
    }

    //通栏广告图
    public function tonglanIndexAction() {
        $this->_view->assign('moduleId', Module::A_TONGLAN_GUANGGAO);
        $this->display('tonglan/index');
    }

    //通栏广告图模块设置
    public function tonglanModuleAction() {
        $this->_view->assign('moduleId', Module::A_TONGLAN_GUANGGAO);
        $this->display('tonglan/module');
    }

    //三张专题图
    public function zhuantiIndexAction() {
        $this->_view->assign('moduleId', Module::A_ZHUANTI);
        $this->display('zhuanti/index');
    }

    //三张专题图模块设置
    public function zhuantiModuleAction() {
        $this->_view->assign('moduleId', Module::A_ZHUANTI);
        $this->display('zhuanti/module');
    }


    //民国书刊右侧书店
    public function periodicalRpondAction() {
        //民国书刊顶级模块id
        $this->_view->assign("parentId", Module::A_MINGUOSHUKAN);
        $this->_view->assign('moduleId', Module::A_MINGUO_SHUDIANTUIJIAN);
        $this->display('periodical/rpond');
    }

    //民国书刊拍品
    public function periodicalAuctionAction() {
        //民国书刊顶级模块id
        $this->_view->assign("parentId", Module::A_MINGUOSHUKAN);
        $this->_view->assign('moduleId1', Module::A_MINGUO_TUSHUWENXIAN);
        $this->_view->assign('moduleId2', Module::A_MINGUO_QIKAN);
        $this->display('periodical/auction');
    }

    //民国书刊模块设置
    public function periodicalModuleAction() {
        // 民国模块
        $this->_view->assign('moduleId', Module::A_MINGUOSHUKAN);
        $this->_view->assign('moduleId1', Module::A_MINGUO_SHUDIANTUIJIAN);
        $this->display('periodical/module');
    }

    //民国书刊
    public function periodicalIndexAction() {
        //民国书刊顶级模块id
        $this->_view->assign("parentId", Module::A_MINGUOSHUKAN);
        //民国书刊大卖家id
        $this->_view->assign('moduleId', Module::A_MINGGUO_DAMAIJIA);
        $this->display('periodical/index');
    }


    //古籍书店推荐视图渲染
    public function ancientRpondAction() {
        //古籍顶级模块id
        $this->_view->assign('parentId', Module::A_GUJI);
        $this->_view->assign('moduleId', Module::A_GUJI_SHUDIANTUIJIAN);
        $this->display('ancient/rpond');
    }

    // 古籍拍品池子
    public function ancientAuctionAction() {
        //古籍顶级模块id
        $this->_view->assign('parentId', Module::A_GUJI);
        $this->_view->assign('moduleId', Module::A_GUJI_ZHENBEN);
        $this->display('ancient/auction');
    }

    // 古籍模块设置
    public function ancientModuleAction() {
        // 古籍模块
        $this->_view->assign('moduleId', Module::A_GUJI);
        // 古籍书店推荐
        $this->_view->assign('moduleId1', Module::A_GUJI_SHUDIANTUIJIAN);
        $this->display('ancient/module');
    }

    //古籍拍主池子
    public function ancientIndexAction() {
        //古籍顶级模块id
        $this->_view->assign('parentId', Module::A_GUJI);
        $this->_view->assign('moduleId', Module::A_GUJI_PAIMAIDAMAIJIA);
        $this->display('ancient/index');
    }

    //艺术品模块设置
    public function artworkModuleAction() {
        $this->_view->assign('moduleId', Module::A_YISHUPIN);
        $this->display('artwork/module');
    }

    //艺术品-拍品池子
    public function artworkAuctionAction() {
        //艺术品顶级模块id
        $this->_view->assign('parentId', Module::A_YISHUPIN);
        $this->_view->assign('moduleId', Module::A_YISHUPIN_PAIPINTUIJIAN);
        $this->display('artwork/auction');
    }


    // Don't be too serious, just for test.
    public function getModuleDataAction() {
        $data = Data::singleton()->getModuleCurrentOperateData(9, 100, true);
        print_r($data);
    }

}
