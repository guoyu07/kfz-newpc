<?php

namespace widgetmodels;

use services\Data;
use conf\Module;
/**
 * Ad
 *
 * @author dongnan
 */
class Ad extends \kongfz\ViewModel {

    use \kongfz\traits\Singleton;

    /**
     * 单例
     * @return \widgetmodels\Ad
     */
    public static function singleton() {
        return self::instance();
    }

    public function pmgs($query = []) {
        //根据query处理业务数据
        //$data = $model->execute($query);
//         $data = ['_METHOD_' => __METHOD__, 'test' => 'json', 'hello' => 'world'];

    	//获取广告位图信息
    	$data = Data::singleton()->getModuleCurrentOperateData(Module::A_GUANGGAO, '8');
    	$data['moduleId'] = Module::A_GUANGGAO;
    	if(is_array($data['data']) && !empty($data['data']) && count($data['data']) == 1) {
    		//只有一组广告，再取一组默认广告
    		array_push($data['data'], $data['defaultData'][0]);
    		unset($data['defaultData']);
    	} else if(is_array($data['data']) && !empty($data['data']) && count($data['data']) > 1) {
    		unset($data['defaultData']);
    	}
        return $data;
    }
    
    public function zhuanti($query = []) {
    	//根据query处理业务数据
    	//$data = $model->execute($query);
    	//         $data = ['_METHOD_' => __METHOD__, 'test' => 'json', 'hello' => 'world'];
    
    	//获取广告位图信息
    	$data = Data::singleton()->getModuleCurrentOperateData(Module::A_ZHUANTI, 3);
    	$data['moduleId'] = Module::A_ZHUANTI;
    	if(is_array($data['data']) && !empty($data['data']) && count($data['data']) < 3) {
    		//不足3条专题图时，用默认图补齐
    		foreach ($data['defaultData'] as $k => $v) {
    			if($k < (3 - count($data['data']))) {
    				array_push($data['data'], $data['defaultData'][$k]);
    			}
    		}
    		unset($data['defaultData']);
    	} else if(is_array($data['data']) && !empty($data['data']) && count($data['data']) == 3) {
    		unset($data['defaultData']);
    	} else {
    		foreach ($data['defaultData'] as $k => $v) {
    			if($k < 3) {
    				array_push($data['data'], $data['defaultData'][$k]);
    			}
    		}
    		unset($data['defaultData']);
    	}
    	return $data;
    }

}
