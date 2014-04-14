<?php

/**
 * @name Chaoshi_ShopModel
 * @desc 购物车
 * @author Vic
 */
class Chaoshi_ShopModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
        $this->ssodb = Factory::getDBO('local_jiada_sso');
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    public function getShopDelivery($param){
        if (!isset($param['shopId']) || !$param['shopId']) {
            return false;
        }
        
        $sql = "select * from shop_set_delivery where shopId='$param[shopId]';";
        $this->hydb->setQuery($sql);
        $rows = $this->hydb->loadAssocList();
        return $rows;
        
    }
    
    public function getShopDeliveryInfo($param){
        if (!isset($param['shopId']) || !$param['shopId'] || !isset($param['deliveryTimeOption']) || !$param['deliveryTimeOption']) {
            return false;
        }
        
        $sql = "select * from shop_set_delivery where dmId='$param[deliveryTimeOption]' and shopId='$param[shopId]';";
        $this->hydb->setQuery($sql);
        $rows = $this->hydb->loadAssoc();
        return $rows;
        
    }
    
    /**
     * 获取店铺设置的支付及付款方式
     * @param array $param
     * @param bigint shopId 数组元素
     */
    public function getShopPay($param) {
        if (!isset($param['shopId']) || !$param['shopId']) {
            return false;
        }
        $sql = "select * from shop_set_pay where shopId='$param[shopId]';";
        $this->hydb->setQuery($sql);
        $rows = $this->hydb->loadAssoc();
        $data=array();
        if($rows){
            if($rows['payMode']){
                $payMode=$this->getSysPayMode($rows['payMode']);
            }
            
            if($rows['payWay']){
                $payWay=$this->getSysPayWay($rows['payWay']);
            }
            
            if(isset($payMode) && $payMode){
                $data['shopPayMode']=$payMode;
            }
            if(isset($payWay) && $payWay){
                $data['shopPayWay']=$payWay;
            }
        }
        return $data;
    }
    
    /**
     * 根据店铺id关联系统的支付方式信息
     * @param string $payModeId 多个支付id，逗号分隔
     */
    private function getSysPayMode($payModeId){
        if (!$payModeId) {
            return false;
        }
        $sql = "select * from pay_mode where payModeId in($payModeId)";
        $this->db->setQuery($sql);
        $rows = $this->db->loadAssocList();
        return $rows;
    }
    
    /**
     * 根据店铺id关联系统的付款方式信息
     * @param string $payWayId 多个支付id，逗号分隔
     */
    private function getSysPayWay($payWayId){
        if (!$payWayId) {
            return false;
        }
        $sql = "select * from pay_way where payWayId in($payWayId)";
        $this->db->setQuery($sql);
        $rows = $this->db->loadAssocList();
        return $rows;
    }

}
