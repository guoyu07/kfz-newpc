<?php

use conf\Module;
use Yaf\Registry as R;
use api\YarClient;
use http\Response;

/**
 * Class ShopController
 * 运营后台书店模块视图渲染控制器
 * @author  liubang <liubang@kongfz.com>
 */
class ShopController extends AdminBaseController
{
    private $shopYarClient;
    private $xinyuYarClient;
    private $pmYarClient;

    public function init()
    {
        $this->shopYarClient = new YarClient(R::get('g_config')->interface->shop);
        $this->xinyuYarClient = new YarClient(R::get('g_config')->interface->xinyu);
        $this->pmYarClient = new YarClient(R::get('g_config')->interface->pm);
        parent::init();
    }

    //手快有 手慢无isbn池子
    public function kuaiYouManWuIndexAction() {
        $this->_view->assign('moduleId', Module::B_SHOUKUAIYOUSHOUMANWU);
        $this->display('kuaiYouManWu/index');
    }

    //手快有 手慢无模块设置
    public function kuaiYouManWuModuleAction() {
        $this->_view->assign('moduleId', Module::B_SHOUKUAIYOUSHOUMANWU);
        $this->display('kuaiYouManWu/module');
    }

    //轮播图-池子
    public function bannerIndexAction()
    {
        $this->_view->assign('moduleId', Module::B_LUNBO);
        $this->display('banner/index');
    }

    //轮播图-模块设置
    public function bannerModuleAction()
    {
        $this->_view->assign('moduleId', Module::B_LUNBO);
        $this->display('banner/module');
    }

    // 超值低价-商品池子
    public function dijiaIndexAction()
    {
        $this->_view->assign('moduleId', Module::B_CHAOZHIDIJIA);
        $this->display('dijia/index');
    }

    // 超值低价-模块设置
    public function dijiaModuleAction()
    {
        $this->_view->assign('moduleId', Module::B_CHAOZHIDIJIA);
        $this->display('dijia/module');
    }

    // 特色推荐-商品池子
    public function teseTuijianIndexAction() {
        $this->_view->assign('moduleId', Module::B_TESETUIJIAN);
        $this->display('teseTuiJian/index');
    }

    // 特色推荐-模块设置
    public function teseTuijianModuleAction() {
        $this->_view->assign('moduleId', Module::B_TESETUIJIAN);
        $this->display('teseTuiJian/module');
    }

    // 单张广告图-池子
    public function singleIndexAction()
    {
        $this->_view->assign('moduleId', Module::B_GUANGGAO);
        $this->display('single/index');
    }

    // 单张广告图-模块设置
    public function singleModuleAction()
    {
        $this->_view->assign('moduleId', Module::B_GUANGGAO);
        $this->display('single/module');
    }

    //旧书店 有故事
    public function oldShopWithStoryIndexAction()
    {
        $this->_view->assign('moduleId', Module::B_JIUSHUDIANYOUGUSHI);
        $this->display('oldShopWithStory/index');
    }

    //旧书店 有故事-模块设置
    public function oldShopWithStoryModuleAction()
    {
        $this->_view->assign('moduleId', Module::B_JIUSHUDIANYOUGUSHI);
        $this->display('oldShopWithStory/module');
    }

    /**
     * 根据店铺和商品id获取商品信息
     */
    public function getItemInfoByIdAction()
    {
        $itemId = $this->_request->getPost('itemId', '');
        $shopId = $this->_request->getPost('shopId', '');
        $sourceItemData = $this->shopYarClient->getItemInfoByItemIds([['itemId' => $itemId, 'shopId' => $shopId]]);
        $params['userIds'] = [$sourceItemData['data'][0]['userId']];
        $sourceReviewData = $this->xinyuYarClient->getReviewInfos($params);

        if ($sourceItemData['status'] != 1 || $sourceReviewData['status'] != 1) {
            Response::json(false, '数据返回失败');
        } else {
            $data['imgUrl'] = $sourceItemData['data'][0]['imgUrl'];
            $data['itemId'] = $sourceItemData['data'][0]['itemId'];
            $data['itemName'] = $sourceItemData['data'][0]['itemName'];
            $data['catName'] = $sourceItemData['data'][0]['catName'];
            $data['number'] = $sourceItemData['data'][0]['number'];
            $data['price'] = $sourceItemData['data'][0]['price'];
            $data['shopName'] = $sourceItemData['data'][0]['shopName'];
            $data['credit'] = $sourceReviewData['data'][0]['goodRatingCount'];
            $data['goodRate'] = $sourceReviewData['data'][0]['ratingPercent'];
            Response::json(true, [], $data);
        }
    }

    /**
     * 获取店铺数据
     */
    public function getShopInfoAction()
    {
        $data = $this->_request->getPost();
        if (!isset($data['param']) || empty($data['param'])) {
            Response::json(false, '请输入书店ID或书店昵称');
        } else {
            // 根据店铺名称进行搜索
            if (!preg_match('/^[0-9]\d+$/', $data['param'])) {
                $result = $this->shopYarClient->call('getShopInfoByShopName', $data);
                if ($result['status']) {
                    $shopData['shopId'] = $result['data']['shopId'];
                    $shopData['shopName'] = $result['data']['shopName'];
                    $userData['nickname'] = $result['data']['nickname'];
                    $userData = self::getAuctioneerInfoByName($userData);
                    Response::json(true, [], array_merge($shopData, $userData));
                } else {
                    Response::json(false, '数据返回失败');
                }
            } else {
                // 根据店铺id进行搜索
                $result = $this->shopYarClient->call('getShopInfoByShopIds', $data);
                if (!$result['status']) {
                    Response::json(false, '数据返回失败');
                } else {
                    $shopData['shopId'] = $result['data'][0]['shopId'];
                    $shopData['shopName'] = $result['data'][0]['shopName'];
                    $userData['nickname'] = $result['data'][0]['nickname'];
                    $userData = self::getAuctioneerInfoByName($userData);
                    Response::json(true, [], array_merge($shopData, $userData));
                }
            }
        }
    }

    private function getAuctioneerInfoByName($params)
    {
        $result = $this->pmYarClient->call('getAuctioneerInfoByNickName', ['nickname' => $params['nickname']]);
        if ($result['status'] === 1) {
            $result = $result['data'];
            $data['nickname'] = $params['nickname'];
            $data['rate'] = $result['$reviewSellerInfo']['ratingPercent'];
            $data['credit'] = $result['$reviewSellerInfo']['goodRatingCount'];
            $data['auctionLevel'] = $result['class'];
            $data['userId'] = $result['userId'];
            return $data;
        } else {
            Response::json(false, '获取数据失败!!!');
        }
    }
}