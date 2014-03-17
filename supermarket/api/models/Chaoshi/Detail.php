<?php

/**
 * @name Chaoshi_DetailModel
 * @desc 商品详情
 * @author Vic
 */
class Chaoshi_DetailModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    /**
     * 根据商品价格id获取商品信息
     * @param bigint|string $priceId 商品价格id。
     * @return array
     */
    public function getGoodsInfo($priceId){
        if(!$priceId){
            return false;
        }
        $query = "select a.cateId,a.brandId,a.goodsName,a.packPic,b.priceId,b.originalPrice,b.discount,b.currentPrice,b.marketPrice,b.status from goods a, goods_price b ";
        $query .=" where b.priceId='$priceId' and a.goodsId=b.goodsId ";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }
    

}
