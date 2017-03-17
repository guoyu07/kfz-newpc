<?php
/**
 * Created by PhpStorm.
 * User: diao
 * Date: 17-1-22
 * Time: 下午12:02
 */

namespace consts;


class Cache
{
    /**
     * 书店联盟首页整体缓存
     */
    const SHOP_UNION_INDEX_KEY = 'shop_union_index_';
    /**
     * 书店联盟首页每个地区书店总数缓存
     */
    const SHOP_INDEX_AREA_SHOP_NUM_KEY = 'shop_index_area_shop_num_';
    /**
     * 爬虫IP
     */
    const SPIDER_IP = 'spider_ip_';

    /**
     * 客户端IP
     */
    const CLIENT_IP = 'client_ip_';

    const SQL = 'SQL_';

    public static $TIME = [
        self::SHOP_UNION_INDEX_KEY => 86400, //书店联盟首页整体缓存 【long_time】
        self::SHOP_INDEX_AREA_SHOP_NUM_KEY => 3600, //书店联盟首页每个地区书店总数缓存【long_time】
        self::CLIENT_IP => 60 // 客户端IP缓存过期时间
    ];
}