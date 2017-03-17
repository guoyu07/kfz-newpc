<?php
use api\YarClient;
use http\Response;

class AuctionController extends AdminBaseController
{
    private $api;
    public function init()
    {
        $this->api = Yaf\Registry::get('g_config')->interface->toArray();
        parent::init();
    }

    /**
     * 获取拍卖商品接口
     */
    public function getAuctionItemAction()
    {
        $itemData = $this->_request->getPost();
        if (isset($itemData) && !empty($itemData)) {
            $search = array_keys($itemData);
            switch ($search[0]) {
                // 通过Id调用远程接口搜拍品
                case 'itemId':
                    $id = $itemData['itemId'];
                    $client = new YarClient($this->api['pm']);
                    $result = $client->getAuctionItemByItemIds([$id]);
                    if ($result['status'] === 1 && !empty($result['data'][0])) {
                        $result = $result['data'][0];
                        $itemData['itemName'] = $result['itemName'];
                        $itemData['endTimeDate'] = $result['endTimeDate'];
                        $itemData['beginPrice'] = $result['beginPrice'];
                        $itemData['minAddPrice'] = $result['minAddPrice'];
                        $itemData['viewedNum'] = $result['viewedNum'];
                        $itemData['smallImg'] = $result['smallImg'];
                        $itemData['bidNum'] = $result['bidNum'];
                        $itemData['maxPrice'] = $result['maxPrice'];
                        $itemData['status'] = $result['status'];
                        $itemData['nickname'] = $result['nickname'];
                        $userData = self::getAuctioneerInfoByName($itemData);
                        Response::json(true, [], array_merge($itemData, array_merge($itemData, $userData)));
                    } else {
                        Response::json(false, '数据不存在');
                    }
                    break;
            }
        } else {
            Response::json(false, '参数缺失');
        }
    }

    /**
     * 获取拍卖用户信息接口
     */
    public function getAuctioneerInfoByNameAction()
    {
        $data = $this->_request->getPost();
        if (!isset($data['nickname']) || empty($data['nickname'])) {
            Response::json(false, '需要输入拍主昵称');
        } else {
            Response::json(true, [], self::getAuctioneerInfoByName($data));
        }

    }


    private function getAuctioneerInfoByName($params)
    {
        $client = new YarClient($this->api['pm']);
        $result = $client->call('getAuctioneerInfoByNickName', ['nickname' => $params['nickname']]);
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