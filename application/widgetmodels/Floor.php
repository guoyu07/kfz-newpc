<?php
/**
 * Created by diao
 */

namespace widgetmodels;

use api\YarClient;
use conf\Module;
use conf\Redis;
use http\Response;
use services\Data;
use Yaf\Registry as R;

class Floor extends \kongfz\ViewModel
{
    use \kongfz\traits\Singleton;

    /**
     * 单例
     * @return \widgetmodels\Footer
     */
    public static function singleton()
    {
        return self::instance();
    }

    private $pmYarClient;
    private $xinyuYarClient;
    private $shopYarClient;
    private $site;
    private $redisObj;

    public function _init_()
    {
        $this->pmYarClient = new YarClient(R::get('g_config')->interface->pm);
        $this->xinyuYarClient = new YarClient(R::get('g_config')->interface->xinyu);
        $this->shopYarClient = new YarClient(R::get('g_config')->interface->shop);
        $this->site = R::get('g_config')->site->toArray();
        $redisConf = R::get('g_config')->cache->redis->toArray();
        $this->redisObj = new \Redis();
        if (!$this->redisObj->connect($redisConf['shop']['host'], $redisConf['shop']['port'])) {
            echo('Redis go away!!!');
        }
    }

    /**
     * 古籍模块
     */
    public function index_guji($query = [])
    {
        $ancientMainModuleData = \ModuleModel::singleton()->getModuleInfoById(Module::A_GUJI);
        $ancientSubModuleData = \ModuleModel::singleton()->getModuleInfoById(Module::A_GUJI_ZHENBEN);
        $shopModuleData = \ModuleModel::singleton()->getModuleInfoById(Module::A_GUJI_SHUDIANTUIJIAN);
        $sellerData = Data::singleton()->getModuleCurrentOperateData(Module::A_GUJI_PAIMAIDAMAIJIA)['data'];
        $itemData = Data::singleton()->getModuleCurrentOperateData(Module::A_GUJI_ZHENBEN)['data'];
        // 推荐书店
        $shopData = Data::singleton()->getModuleCurrentOperateData(Module::A_GUJI_SHUDIANTUIJIAN, 8, false);
        if (count($shopData['data']) < 8) {
            $shopData = array_merge($shopData['data'], $shopData['defaultData']);
            $shopData = array_slice($shopData, 0, 8);
        }

        $data = [];
        foreach ($sellerData as $item) {
            $data['nickname'][] = $item['nickName'];
            $data['userId'][] = $item['dataId'];
        }
        // 取运营在拍数据
        $unFinish = [];
        foreach ($itemData as $item) {
            if ($item['auctionStatus'] != 'unFinished') {
                continue;
            }
            $unFinish[] = $item;
        }
        $itemData = $unFinish;
        // 自动填充
        $itemData = $this->autoFillData($itemData);

        foreach ($itemData as $item) {
            $data['itemImg'][] = $item['smallImg'];
            $data['itemName'][] = $item['itemName'];
            $data['itemId'][] = $item['itemId'];
            $data['remainTime'][] = '剩余：' . strstr($item['lostTime'], '分', true) . '分';
            $data['bidNum'][] = $item['bidNum'];
            $data['maxPrice'][] = $item['maxPrice'];
            $data['beginPrice'][] = $item['beginPrice'];
        }
        foreach ($shopData as $item) {
            $data['shopName'][] = $item['shopName'];
            $data['shopId'][] = $item['shopId'];
            $data['recommend'][] = $item['recommend'];
        }

        $data['mainTitle'] = $ancientMainModuleData['title'];
        $data['subTitle'] = $ancientSubModuleData['title'];
        $data['subMoreLink'] = $ancientSubModuleData['showMoreUrl'];
        $data['shopModuleName'] = $shopModuleData['title'];
        $data['shopModuleMoreLink'] = $shopModuleData['showMoreUrl'];
        $data['parentId'] = Module::A_GUJI;

        //古籍下方的通栏广告，位置3
        $moduleData = Data::singleton()->getModuleCurrentOperateData(Module::A_TONGLAN_GUANGGAO, 5);
        if (!empty($moduleData['params']['tonglanConfig']) && is_array($moduleData['params']['tonglanConfig'])) {
            $count = $this->getTongLanShowCount('3', $moduleData['params']['tonglanConfig']);
            if ($moduleData['params']['tonglanConfig']['3'] == '0') {
                $data['tonglan'] = $this->getTonglanData($count, $moduleData['data'], $moduleData['defaultData']);
            }
        }

        return $data;
    }

    /**
     * 民国模块
     */
    public function index_mingguo($query = [])
    {
        $data = [];

        // 主模块
        $mainModuleData = \ModuleModel::singleton()->getModuleInfoById(Module::A_MINGUOSHUKAN);
        // 推荐书店模块
        $shopModuleData = \ModuleModel::singleton()->getModuleInfoById(Module::A_MINGUO_SHUDIANTUIJIAN);
        // 图书文献模块
        $tuShuWenXianModuleData = \ModuleModel::singleton()->getModuleInfoById(Module::A_MINGUO_TUSHUWENXIAN);
        // 期刊模块
        $qiKanModuleData = \ModuleModel::singleton()->getModuleInfoById(Module::A_MINGUO_QIKAN);
        // 推荐书店
        $shopData = Data::singleton()->getModuleCurrentOperateData(Module::A_MINGUO_SHUDIANTUIJIAN, 8, false);
        if (count($shopData['data']) < 8) {
            $shopData = array_merge($shopData['data'], $shopData['defaultData']);
            $shopData = array_slice($shopData, 0, 8);
        }

        // 图书文献
        $tuShuWenXianData = Data::singleton()->getModuleCurrentOperateData(Module::A_MINGUO_TUSHUWENXIAN)['data'];
        // 期刊
        $qiKanData = Data::singleton()->getModuleCurrentOperateData(Module::A_MINGUO_QIKAN)['data'];
        // 大卖家
        $sellerData = Data::singleton()->getModuleCurrentOperateData(Module::A_MINGGUO_DAMAIJIA)['data'];

        $data['title'] = $mainModuleData['title'];
        foreach ($sellerData as $item) {
            $data['nickName'][] = $item['nickName'];
            $data['userId'][] = $item['userId'];
        }

        $data['mainTitle'] = $mainModuleData['title'];
        $data['subTitle1'] = $tuShuWenXianModuleData['title'];
        $data['subMoreLink1'] = $tuShuWenXianModuleData['showMoreUrl'];
        $data['subTitle2'] = $qiKanModuleData['title'];
        $data['subMoreLink2'] = $qiKanModuleData['showMoreUrl'];
        $data['shopModuleName'] = $shopModuleData['title'];
        $data['parentId'] = Module::A_MINGUOSHUKAN;

        foreach ($shopData as $item) {
            $data['shopName'][] = $item['shopName'];
            $data['shopId'][] = $item['shopId'];
            $data['recommend'][] = $item['recommend'];
        }

        // 取运营在拍数据
        $unFinish = [];
        foreach ($tuShuWenXianData as $item) {
            if ($item['auctionStatus'] != 'unFinished') {
                continue;
            }
            $unFinish[] = $item;
        }
        $tuShuWenXianData = $unFinish;
        // 自动填充
        $tuShuWenXianData = $this->autoFillData($tuShuWenXianData, 5);
        foreach ($tuShuWenXianData as $item) {
            $data['itemImg1'][] = $item['smallImg'];
            $data['itemName1'][] = $item['itemName'];
            $data['itemId1'][] = $item['itemId'];
            $data['remainTime1'][] = '剩余：' . strstr($item['lostTime'], '分', true) . '分';
            $data['bidNum1'][] = $item['bidNum'];
            $data['maxPrice1'][] = $item['maxPrice'];
            $data['beginPrice1'][] = $item['beginPrice'];
        }

        // 取运营在拍数据
        $unFinish = [];
        foreach ($qiKanData as $item) {
            if ($item['auctionStatus'] != 'unFinished') {
                continue;
            }
            $unFinish[] = $item;
        }
        $qiKanData = $unFinish;
        // 自动填充
        $qiKanData = $this->autoFillData($qiKanData, 5);

        foreach ($qiKanData as $item) {
            $data['itemImg2'][] = $item['smallImg'];
            $data['itemName2'][] = $item['itemName'];
            $data['itemId2'][] = $item['itemId'];
            $data['remainTime2'][] = '剩余：' . strstr($item['lostTime'], '分', true) . '分';
            $data['bidNum2'][] = $item['bidNum'];
            $data['maxPrice2'][] = $item['maxPrice'];
            $data['beginPrice2'][] = $item['beginPrice'];
        }

        //民国旧书下方的通栏广告，位置4
        $moduleData = Data::singleton()->getModuleCurrentOperateData(Module::A_TONGLAN_GUANGGAO, 5);
        if (!empty($moduleData['params']['tonglanConfig']) && is_array($moduleData['params']['tonglanConfig'])) {
            $count = $this->getTongLanShowCount('4', $moduleData['params']['tonglanConfig']);
            if ($moduleData['params']['tonglanConfig']['4'] == '0') {
                $data['tonglan'] = $this->getTonglanData($count, $moduleData['data'], $moduleData['defaultData']);
            }
        }

        return $data;
    }


    /**
     * 艺术品模块
     * @param $query
     */
    public function index_art($query = [])
    {
        $data = [];
        // 主模块
        $mainModuleData = \ModuleModel::singleton()->getModuleInfoById(Module::A_YISHUPIN);
        // 子模块1，分类
        $subModuleData1 = \ModuleModel::singleton()->getModuleInfoById(Module::A_YISHUPIN_FENLEI);
        // 子模块2,拍品精选
        $subModuleData2 = \ModuleModel::singleton()->getModuleInfoById(Module::A_YISHUPIN_PAIPINTUIJIAN);
        // 子模块3,拍卖专场
        $subModuleData3 = \ModuleModel::singleton()->getModuleInfoById(Module::A_YISHUPIN_ZHUANCHANGTUIJIAN);
        // 拍品
        $itemData = Data::singleton()->getModuleCurrentOperateData(Module::A_YISHUPIN_PAIPINTUIJIAN)['data'];

        $data['mainTitle'] = $mainModuleData['title'];
        $data['subTitle'] = $subModuleData2['title'];
        $data['subMoreLink'] = $subModuleData2['showMoreUrl'];
        $data['specialTitle'] = $subModuleData3['title'];
        $data['specialMoreLink'] = $subModuleData3['showMoreUrl'];
        $data['parentId'] = Module::A_YISHUPIN;
        // 取运营在拍数据
        $unFinish = [];
        foreach ($itemData as $item) {
            if ($item['auctionStatus'] != 'unFinished') {
                continue;
            }
            $unFinish[] = $item;
        }
        $itemData = $unFinish;
        // 自动填充
        $itemData = $this->autoFillData($itemData);
        foreach ($itemData as $item) {
            $data['itemImg'][] = $item['smallImg'];
            $data['itemName'][] = $item['itemName'];
            $data['itemId'][] = $item['itemId'];
            $data['remainTime'][] = '剩余：' . strstr($item['lostTime'], '分', true) . '分';
            $data['bidNum'][] = $item['bidNum'];
            $data['maxPrice'][] = $item['maxPrice'];
            $data['beginPrice'][] = $item['beginPrice'];
        }
        //艺术品模块下方的通栏广告，位置5
        $moduleData = Data::singleton()->getModuleCurrentOperateData(Module::A_TONGLAN_GUANGGAO, 5);
        if (!empty($moduleData['params']['tonglanConfig']) && is_array($moduleData['params']['tonglanConfig'])) {
            $count = $this->getTongLanShowCount('5', $moduleData['params']['tonglanConfig']);
            if ($moduleData['params']['tonglanConfig']['5'] == '0') {
                $data['tonglan'] = $this->getTonglanData($count, $moduleData['data'], $moduleData['defaultData']);
            }
        }
        // 获取拍卖专场数据
        $result = $this->pmYarClient->getAuctionSpecialList([]);
        foreach ($result as $item) {
            $data['specialName'][] = $item['name'];
            $data['specialTime'][] = explode(' ', $item['beginTimeFormat'])[0] . '-' . $item['endTimeFormat'];
            $data['specialId'][] = $item['specialAreaId'];
        }
        return $data;
    }

    /**
     * 超值低价模块
     */
    public function shop_lowprice($query = [])
    {
        if ($this->redisObj->exists(Redis::SHOP_LOW_PRICE)) {
            return json_decode($this->redisObj->get(Redis::SHOP_LOW_PRICE), true);
        }
        $data = [];
        $data['parentId'] = Module::B_CHAOZHIDIJIA;
        $sourceData = Data::singleton()->getModuleCurrentOperateData(Module::B_CHAOZHIDIJIA, 12, true);
        $subSourceData = $sourceData['submodule'];
        // 超值低价模块标题
        $data['mainTitle'] = $sourceData['title'];

        foreach ($subSourceData as $item) {
            if ($item['isHide'] == '1') {
                continue;
            }

            $subDataArr = [];
            foreach ($item['data'] as $subData) {
                if ($subData['status'] != '发布中') {
                    continue;
                }
                $subDataArr[] = ['shopId' => $subData['shopId'], 'itemId' => $subData['itemId'], 'imgUrl' => $subData['imgUrl'], 'itemName' => $subData['itemName'], 'price' => $subData['price']];
            }
            if (count($subDataArr) < 14) {
                $userIds = array_unique(array_column($item['data'], 'userId'));
                if (count($userIds) > 14) {
                    $userIds = array_slice($userIds, 0, 14);
                }
                // 获取价格区间数据自动补齐
                $moduleInfo = \ModuleModel::singleton()->getModuleInfoById($item['moduleId']);
                $extInfo = json_decode($moduleInfo['params'], true);
                $result = $this->shopYarClient->getCurrentShopItem(['userIds' => $userIds, 'price' => $extInfo['price']]);
                if (!$result['status']) {
                    Response::json(false, '获取数据失败');
                } else {
                    $subDataArr = array_merge($subDataArr, $result['data']);
                }
            }

            $data['subData'][] = ['title' => $item['title'], 'data' => self::assoc_unique_arr($subDataArr, 'itemId')];
        }

        $this->redisObj->setex(Redis::SHOP_LOW_PRICE, 3600, json_encode($data));
        return $data;
    }

    /**
     * 旧书店 有故事
     */
    public function shop_shoplist($query = [])
    {
        if ($this->redisObj->exists(Redis::SHOP_OLD_STORY)) {
            return json_decode($this->redisObj->get(Redis::SHOP_OLD_STORY), true);
        }
        $data = [];
        $sourceData = Data::singleton()->getModuleCurrentOperateData(Module::B_JIUSHUDIANYOUGUSHI, 6, true);
        $subSourceData = $sourceData['submodule'];
        // 主模块标题
        $data['mainTitle'] = $sourceData['title'];
        $data['parentId'] = Module::B_JIUSHUDIANYOUGUSHI;
        foreach ($subSourceData as $item) {
            if ($item['isHide'] == '1') {
                continue;
            }
            // 如果书店不足10个取销量排行补齐
            if (count($item['data']) < 10) {
                $shopStaticData = $this->shopYarClient->getShopStaticData();
                $hotTotal = isset($shopStaticData['saleTop10'][3]) ? $shopStaticData['saleTop10'][3] : '';
                foreach ($hotTotal as $hot) {
                    $item['data'][] = [
                        'shopId' => $hot['shopId'],
                        'userId' => $hot['userId'],
                        'shopName' => $hot['shopName'],
                        'status' => '发布中'];
                }
            }
            $item['data'] = array_slice($item['data'], 0, 6);

            $subDataArr = [];
            $userIds = array_column($item['data'], 'userId');

            // 店铺评价数据
            $reviewData = $this->xinyuYarClient->getReviewInfos(['userIds' => $userIds]);
            if (!$reviewData['status']) {
                Response::json(false, '获取评价数据失败');
            }
            // 店铺下商品数据
            $shopItemData = $this->shopYarClient->getCurrentShopItem(['userIds' => $userIds]);
            if (!$shopItemData['status']) {
                Response::json(false, '获取店铺商品数据失败');
            }

            $i = 0;
            foreach ($item['data'] as $subData) {
                if ($subData['status'] != '发布中') {
                    continue;
                }

                $subDataArr[] = [
                    'shopId' => $subData['shopId'],
                    'goodRate' => $reviewData['status'] ? $reviewData['data'][$i]['ratingPercent'] : '',
                    'shopName' => $subData['shopName'],
                    'itemArr' => $shopItemData['data'][$i]];
                $i++;
            }
            $data['subData'][] = ['title' => $item['title'], 'data' => $subDataArr];
        }
        $this->redisObj->setex(Redis::SHOP_OLD_STORY, 3600, json_encode($data));
        return $data;
    }

    /**
     * 特色推荐模块
     * @param array $query
     */
    public function shop_tesetuijian($query = [])
    {
        if ($this->redisObj->exists(Redis::SHOP_SPECIAL_RECOMMEND)) {
            return json_decode($this->redisObj->get(Redis::SHOP_SPECIAL_RECOMMEND), true);
        }
        $data = [];
        $sourceData = Data::singleton()->getModuleCurrentOperateData(Module::B_TESETUIJIAN, 12, true);
        $subSourceData = $sourceData['submodule'];
        $data['mainTitle'] = $sourceData['title'];
        $data['subTitle'] = $sourceData['subtitle'];
        $data['parentId'] = Module::B_TESETUIJIAN;
        foreach ($subSourceData as $item) {
            $data['moreUrl'] = $subSourceData['showMoreUrl'];
            if ($item['isHide'] == '0' && count($item['data']) > 0 && count($item['data']) >= 12) {
                //存在显示的tab，并且tab中数据多于12条
                $data['subData'][] = ['title' => $item['title'], 'data' => $item['data']];
            } else if ($item['isHide'] == '0' && count($item['data']) > 0 && count($item['data']) < 12) {
                $data['subData'][] = ['title' => $item['title'], 'data' => $item['data']];
                //存在显示的tab，但tab中数据不足12条时，用36小时前加入过购物车的该tab分类的商品补齐
                $cartItemInfo = $this->shopYarClient->getCartItemInfo(['catId' => $item['params']['catId']]);
                if (!empty($cartItemInfo['data']) && is_array($cartItemInfo['data'])) {
                    $itemids = [];
                    foreach ($item['data'] as $key => $val) {
                        array_push($itemids, $val['itemId']);
                    }
                    foreach ($cartItemInfo['data'] as $k => $v) {
                        if (!in_array($v['itemId'], $itemids)) {
                            $cartItemInfo['data'][$k]['imgSrc'] = $this->site['img'] . $v['imgUrl'];
                            array_push($data['subData']['data'], $cartItemInfo['data'][$k]);
                        }
                    }
                }
            }
        }
        if (isset($data['subData']) && !empty($data['subData']) && is_array($data['subData'])) {
            $this->redisObj->setex(Redis::SHOP_SPECIAL_RECOMMEND, 3600, json_encode($data));
            return $data;
        } else {
            $data['moreUrl'] = $this->site['shop'] . "Cxianzhuang/";
            //所有tab都设置为隐藏或所有tab下都没数据时，取12条36小时前加入购物车中的商品
            $cartItemInfo = $this->shopYarClient->getCartItemInfo(['catId' => '']);
            $cartData = [];
            if (!empty($cartItemInfo['data']) && is_array($cartItemInfo['data'])) {
                $cartData = array_slice($cartItemInfo['data'], 0, 12);
                foreach ($cartData as $s => $t) {
                    $cartData[$s]['imgSrc'] = $this->site['img'] . $t['imgUrl'];
                }
            }
            $data['subData'][] = ['title' => '', 'data' => $cartData];
            $this->redisObj->setex(Redis::SHOP_SPECIAL_RECOMMEND, 3600, json_encode($data));
            return $data;
        }
    }

    /**
     * 手快有，手慢无
     * @param array $query
     */
    public function shop_jueban($query = [])
    {
        $data = [];
        $data['mainTitle'] = "手快有 手慢无";
        $data['subTitle'] = "独家 精品 绝版";

        return $data;
    }

    /**
     *新书首页-推荐专题
     * @param array $query
     */
    public function xinshu_banner($query = [])
    {
        $sourceData = Data::singleton()->getModuleCurrentOperateData(Module::C_TUIJIANZHUANTI, 3);
        $data['isHide'] = $sourceData['isHide'];
        $data['moduleId'] = Module::C_TUIJIANZHUANTI;
        if ($sourceData['isHide'] == '0') {
            //模块显示 并且 池子中真实数据大于等于3条
            if (is_array($sourceData['data']) && count($sourceData['data']) >= 3) {
                $data['mainTitle'] = $sourceData['title'];
                $data['subTitle'] = $sourceData['subtitle'];
                $data['moreUrl'] = $sourceData['showMoreUrl'];
                $data['noLine'] = '1';     //标题下方没有横线
                $data['data'] = $sourceData['data'];
            } else {
                //模块显示 但 池子中真实数据不足3条，取专题列表页池子中的数据补齐3条

            }
        }
        return $data;
    }

    /**
     * 新书首页-特色精品推荐
     */
    public function xinshu_tesejingpin($query = [])
    {
        $data = [];
        $data['mainTitle'] = "特色精品推荐";

        return $data;
    }

    /**
     * 新书首页-近期出版
     * @param array $query
     */
    public function xinshu_jinqichuban($query = [])
    {
        $data = [];
        $data['mainTitle'] = "近期出版";

        return $data;
    }

    /**
     * 新书首页-新书热卖榜
     * @param array $query
     * @return array
     */
    public function xinshu_hotsale($query = [])
    {
        $data = [];
        $data['mainTitle'] = "新书热卖榜";

        return $data;
    }

    /**
     * 新书首页-低价促销
     * @param array $query
     */
    public function xinshu_dijiacuxiao($query = [])
    {
        $data = [];
        $data['mainTitle'] = "低价促销";
        $data['moduleId'] = Module::C_DIJIACUXIAO;

        return $data;
    }

    /**
     * 获取某个通栏位置的数据
     * @param $count          当前通栏位置前有几个位置处于显示状态
     * @param $data           真实数据
     * @param $defaultData    默认数据
     */
    private function getTonglanData($count, $data, $defaultData)
    {

        $key = $count + 1;     //需要显示池子中的第几条记录
        $dataCount = count($data);    //真实数据条数
        $defaultCount = count($defaultData);   //默认数据条数

        if (isset($data[$count])) {
            return $data[$count];
        } else if (($dataCount < $key) && !empty($defaultData) && $defaultCount >= ($key - $dataCount)) {
            $k = $key - $dataCount - 1;
            return $defaultData[$k];
        }

        return [];
    }

    /**
     * 获取当前通栏位置前有几个位置处于显示状态
     * @param $curPos  通栏位置号
     * @param $config  通栏位置配置信息
     */
    private function getTongLanShowCount($curPos, $config)
    {
        $count = 0;
        foreach ($config as $k => $v) {
            if ($k < $curPos && $v == '0') {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 自动填充拍品数据
     * @param $auctionArr
     * @return array
     */
    private function autoFillData($auctionArr, $num = 10)
    {
        // 从今日热点数据进行补充
        if (count($auctionArr) < 10) {
            $result = $this->pmYarClient->getHotAuctionData(['type' => 'todayHot']);
            foreach ($result as &$item) {
                $item['smallImg'] = $item['img'];
                $timeDiff = $item['endTime'] - time();
                $item['lostTime'] = $this->formatDiffTime($timeDiff);
            }
            $auctionArr = array_merge($auctionArr, $result);
        }

        // 从三日热点数据进行补充
        if (count($auctionArr) < 10) {
            $result = $this->pmYarClient->getHotAuctionData(['type' => 'hot']);
            foreach ($result as &$item) {
                $item['smallImg'] = $item['img'];
                $timeDiff = $item['endTime'] - time();
                $item['lostTime'] = $this->formatDiffTime($timeDiff);
            }
            $auctionArr = array_merge($auctionArr, $result);
        }

        // 只取10条数据
        $auctionArr = array_slice($auctionArr, 0, $num);
        return $auctionArr;
    }

    /**
     * 格式化时间差值
     * @param $diff
     * @return string
     */
    private function formatDiffTime($diff)
    {
        $d = intval($diff / 86400);
        $h = intval(($diff % 86400) / 3600);
        $m = ceil(($diff % 86400 % 3600) / 60);
        $timeCn = '';
        $timeCn .= $d ? $d . '天' : '';
        $timeCn .= $h ? $h . '时' : '';
        $timeCn .= $m . '分';
        return $timeCn;
    }

    /**
     * 关联数组根据某一个键值去重
     * @param $arr
     * @param $key
     * @return mixed
     */
    private function assoc_unique_arr($arr, $key)
    {
        $checkArr = [];
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $checkArr)) {
                unset($arr[$k]);
            } else {
                $checkArr[] = $arr[$k][$key];
            }
        }
        return $arr;
    }
}