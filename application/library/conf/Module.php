<?php

namespace conf;


class Module {
	
	/** 手动添加子模块的起始moduleId */
	const MODULEID = 500;


    /** 大首页－好书推荐 */
    const A_HAOSHUTUIJIAN = 1;

    /** 大首页－古籍 */
    const A_GUJI = 2;

    /** 大首页－古籍－拍卖大卖家 */
    const A_GUJI_PAIMAIDAMAIJIA = 3;

    /** 大首页－古籍－古籍珍本 */
    const A_GUJI_ZHENBEN = 4;

    /** 大首页－古籍－书店推荐 */
    const A_GUJI_SHUDIANTUIJIAN = 5;

    /** 大首页－民国书刊 */
    const A_MINGUOSHUKAN = 6;

    /** 大首页－民国书刊－拍卖大卖家 */
    const A_MINGGUO_DAMAIJIA = 7;

    /** 大首页－民国书刊－图书文献 */
    const A_MINGUO_TUSHUWENXIAN = 8;

    /** 大首页－民国书刊－书店推荐 */
    const A_MINGUO_SHUDIANTUIJIAN = 9;

    /** 大首页－民国书刊－民国期刊 */
    const A_MINGUO_QIKAN = 19;

    /** 大首页－艺术品 */
    const A_YISHUPIN = 10;

    /** 大首页－艺术品－分类 */
    const A_YISHUPIN_FENLEI = 11;

    /** 大首页－艺术品－拍品推荐 */
    const A_YISHUPIN_PAIPINTUIJIAN = 12;

    /** 大首页－艺术品－专场推荐 */
    const A_YISHUPIN_ZHUANCHANGTUIJIAN = 13;

    /** 大首页－轮播图 */
    const A_LUNBO = 14;

    /** 大首页－广告 */
    const A_GUANGGAO = 15;

    /** 大首页－通栏广告 */
    const A_TONGLAN_GUANGGAO = 16;

    /** 大首页－专题 */
    const A_ZHUANTI = 17;

    /** 大首页-旧书 */
    const A_JIUSHU = 29;

    /** 大首页-旧书-最受关注图书 */
    const A_JIUSHU_ZUISHOUGUANZHUTUSHU = 30;

    /** 大首页-旧书-书店推荐 */
    const A_JIUSHU_SHUDIANTUIJIAN= 31;

    /** 大首页-新书 */
    const A_XINSHU = 32;

    /** 大首页-新书-近期出版 */
    const A_XINSHU_JINQICHUBAN = 33;

    /** 大首页-新书-销售排行 */
    const A_XINSHU_XIAOSHOUPAIHANG = 34;
    
    /** 书店首页－轮播 */
    const B_LUNBO = 20;
    
    /** 书店首页－单张广告 */
    const B_GUANGGAO = 21;

    /** 书店首页－特色推荐 */
    const B_TESETUIJIAN = 22;
    
    /** 书店首页－超值低价 */
    const B_CHAOZHIDIJIA = 23;

    /** 书店首页-旧书店 有故事 */
    const B_JIUSHUDIANYOUGUSHI = 24;

    /** 书店首页-手快有手慢无 */
    const B_SHOUKUAIYOUSHOUMANWU = 35;


    /** 新书首页-轮播 */
    const C_LUNBO = 25;

    /** 新书首页-单张广告 */
    const C_GUANGGAO = 26;

    /** 新书首页-低价促销 */
    const C_DIJIACUXIAO = 27;

    /** 新书首页-推荐专题 */
    const C_TUIJIANZHUANTI = 28;

    /** 新书首页-近期出版 */
    const C_JINQICHUBAN = 36;

    /** 新书首页-新书热卖榜 */
    const C_XINSHUREMAIBANG = 37;
    
    
    
    
    /** 图文轮播  */
    const SHOW_TYPE_TUWEN_LUNBO = 0;
    
    /** 纯图轮播 */
    const SHOW_TYPE_CHUNTU_LUNBO = 1;
    
    /** 随机排序 */
    const SHOW_TYPE_SUIJI_PAIXU = 0;
    
    /** 固定排序 */
    const SHOW_TYPE_GUDING_PAIXU = 1;
    
    public static $showType = [
    	self::A_LUNBO => [
    		self::SHOW_TYPE_TUWEN_LUNBO, self::SHOW_TYPE_CHUNTU_LUNBO
    	],
    	self::B_CHAOZHIDIJIA => [
    		self::SHOW_TYPE_SUIJI_PAIXU, self::SHOW_TYPE_GUDING_PAIXU
    	],
        self::B_TESETUIJIAN => [
            self::SHOW_TYPE_SUIJI_PAIXU, self::SHOW_TYPE_GUDING_PAIXU
        ]
    ];


    public static $moduleSyncMap = [
        // 大首页-旧书-最受欢迎  =>  书店区-手快有手慢无
        self::A_JIUSHU_ZUISHOUGUANZHUTUSHU => [
            'syncModuleArea' => '书店区',
            'syncModuleId'   => self::B_SHOUKUAIYOUSHOUMANWU
        ],
        // 大首页-新书-近期出版  =>  新书首页-近期出版
        self::A_XINSHU_JINQICHUBAN => [
            'syncModuleArea' => '新书首页',
            'syncModuleId' => self::C_JINQICHUBAN
        ]
    ];
}