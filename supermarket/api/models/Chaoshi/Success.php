<?php

/**
 * @name Chaoshi_SuccessModel
 * @desc 购物车
 * @author Vic
 */
class Chaoshi_SuccessModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->ssodb = Factory::getDBO('local_jiada_sso');
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    /**
     * 获取订单信息
     * @param bigint|string $orderId 商品价格id。
     * @return array
     */
    public function getOrderInfo($param) {
        if (!isset($param['orderNo']) || !$param['orderNo'] || !isset($param['userId']) || !$param['userId']) {
            return false;
        }
        
        $query = "select a.*,b.payAmount,c.* from order_consignee a, order_statistics b,order_delivery c ";
        $query .=" where a.orderNo='$param[orderNo]' and a.userId='$param[userId]' and a.orderNo=b.orderNo and a.orderNo=c.orderNo";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }
}
