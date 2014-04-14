<?php

/**
 * @name ShopController
 * @author Vic Shiwei
 * @desc 获取商家店铺相关的设置信息
 */
class ShopController extends Core_Controller_Api{
    public $_config;
    protected $model;

    public function init() {
        parent::init();
        $this->_config = Yaf_Registry::get('_CONFIG');
        Yaf_Loader::import('phprpc/server/phprpc_server.php');
        Yaf_Loader::import('phprpc/server/dhparams.php');
        Yaf_Loader::import('phprpc/common/bigint.php');
        Yaf_Loader::import('phprpc/common/compat.php');
        Yaf_Loader::import('phprpc/common/phprpc_date.php');
        Yaf_Loader::import('phprpc/common/xxtea.php');
        $this->model = new Chaoshi_ShopModel();
    }
    
    public function indexAction() {
        //禁止缓存
//        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->_layout = false;        
        $phprpcServer = new PHPRPC_Server();
        $phprpcServer->add(array('getShopPay','getShopDelivery','getShopDeliveryInfo'),  $this);
        
        $phprpcServer->start();
    }
    
    /**
     * 获取店铺设置的支付和付款方式
     * @param array $param
     * @param bigint shopId 数组元素
     */
    public function getShopPay($param){
        //验证参数
        if (!isset($param['shopId']) || !$param['shopId']) {
            return $this->errorMessage('请求参数错误！');
        }
        
        //根据参数获取数据
        $shopPay = $this->model->getShopPay($param);
        if(!$shopPay){
            return $this->errorMessage('该店铺（仓库）还没有设置支付和付款方式！');
        }

        return $this->returnData($shopPay);
    }
    
    
    /**
     * 获取店铺设置的配送方式（配送规则）
     * @param array $param
     * @param bigint shopId 数组元素
     */
    public function getShopDelivery($param){
        //验证参数
        if (!isset($param['shopId']) || !$param['shopId']) {
            return $this->errorMessage('请求参数错误！');
        }
        
        //根据参数获取数据
        $shopDelivery = $this->model->getShopDelivery($param);
        if(!$shopDelivery){
            return $this->errorMessage('该店铺（仓库）还没有设置配送方式！');
        }

        return $this->returnData($shopDelivery);
    }
    
    /**
     * 获取店铺设置的配送方式信息
     * @param array $param
     * @param bigint shopId 数组元素
     * @param bigint deliveryTimeOption 配送方式id
     */
    public function getShopDeliveryInfo($param){
        //验证参数
        if (!isset($param['shopId']) || !$param['shopId'] || !isset($param['deliveryTimeOption']) || !$param['deliveryTimeOption']) {
            return $this->errorMessage('请求参数错误！');
        }
        
        //根据参数获取数据
        $shopDeliveryInfo = $this->model->getShopDeliveryInfo($param);
        if(!$shopDeliveryInfo){
            return $this->errorMessage('没有找到可匹配的数据！');
        }

        return $this->returnData($shopDeliveryInfo);
    }
}
