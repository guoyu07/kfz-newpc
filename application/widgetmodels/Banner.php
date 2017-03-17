<?php

namespace widgetmodels;

use services\Data;
use conf\Module;
/**
 * Banner
 *
 * @author dongnan
 */
class Banner extends \kongfz\ViewModel {

    use \kongfz\traits\Singleton;

    /**
     * 单例
     * @return \widgetmodels\Banner
     */
    public static function singleton() {
        return self::instance();
    }

    /**
     * 轮播图
     * @param array $query
     * @return array
     */
    public function full($query = []) {
    	//获取轮播图信息
        $data = [];
        if($query['pos'] == 'index') {
            //大首页
            $data = Data::singleton()->getModuleCurrentOperateData(Module::A_LUNBO, 8);
            $data['moduleId'] = Module::A_LUNBO;
        } else if($query['pos'] == 'shop') {
            //书店首页
            $data = Data::singleton()->getModuleCurrentOperateData(Module::B_LUNBO, 8);
            $data['moduleId'] = Module::B_LUNBO;
            if($data['isHide'] == '1') {
                return [];
            }
        } else if($query['pos'] == 'xinshu') {
            //新书首页
            $data = Data::singleton()->getModuleCurrentOperateData(Module::C_LUNBO, 8);
            $data['moduleId'] = Module::C_LUNBO;
            if($data['isHide'] == '1') {
                return [];
            }
        }

    	if(!empty($data['data']) && is_array($data['data'])) {
    		unset($data['defaultData']);
    	} else {
    		$data['data'] = $data['defaultData'];
    		unset($data['defaultData']);
    	}
        return $data;
    }

    /**
     * 单张广告
     * @param array $query
     * @return array
     */
    public function banner_1pic($query = []) {
        //单张广告
        $data = [];
        if($query['pos'] == 'shop') {
            $data = Data::singleton()->getModuleCurrentOperateData(Module::B_GUANGGAO, 1);
            $data['moduleId'] = Module::B_GUANGGAO;
        } else if($query['pos'] == 'xinshu') {
            $data = Data::singleton()->getModuleCurrentOperateData(Module::C_GUANGGAO, 1);
            $data['moduleId'] = Module::C_GUANGGAO;
        }

        if(!empty($data['data']) && is_array($data['data'])) {
            unset($data['defaultData']);
        } else {
            $data['data'] = $data['defaultData'];
            unset($data['defaultData']);
        }
        return $data;
    }

}
