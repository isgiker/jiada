<?php

/**
 * @name My_IndexModel
 * @desc 个人中心
 * @author Vic
 */
class My_IndexModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    public function getMyOrderNoList($orderNo_str, $limit){
        if (!$orderNo_str)
            return false;
        
        $query = "select orderNo,productAmount,orderAmount,actLower,actGiveaway,deliveryFee,payAmount,createTime,activityRemark from order_statistics where orderNo in ($orderNo_str)";
        $query .=" order by createTime desc ";
        $query .=' limit 0, '.$limit;
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    
    public function getMyOrderList($userId, $limit){
        if (!$userId)
            return false;
        
        $query = "select orderId,orderNo,shopId,productAmount,orderAmount,actLower,actGiveaway,deliveryFee,payAmount,orderStatus,shippingStatus,payStatus,payMode,shippingTime from `order` where userId='$userId'";
        $query .=" order by createTime desc ";
        $query .=' limit 0, '.$limit;
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    
    public function getOrderProductList($orderNo, $shopId, $userId){
        if (!$orderNo || !$shopId || !$userId)
            return false;
        
        $query = "select a.priceId,a.productId,a.productName,a.productNum,a.originalPrice,a.currentPrice,a.discount,(select packPic from `goods` where goodsId=a.productId ) as packPic from `order_product` a where a.orderNo='$orderNo' and a.userId='$userId' and a.shopId='$shopId'";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    

}
