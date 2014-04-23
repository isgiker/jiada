<?php

/**
 * @name Chaoshi_DetailModel
 * @desc 商品详情
 * @author Vic
 */
class Chaoshi_DetailModel extends BasicModel {

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }

    /**
     * 根据商品价格id获取商品信息
     * @param bigint|string $priceId 商品价格id。
     * @return array
     */
    public function getGoodsInfo($priceId) {
        if (!$priceId) {
            return false;
        }
        $query = "select a.goodsId,a.cateId,a.brandId,a.goodsName,a.packPic,b.priceId,b.shopId,b.originalPrice,b.discount,b.currentPrice,b.marketPrice,b.status from goods a, goods_price b ";
        $query .=" where b.priceId='$priceId' and a.goodsId=b.goodsId ";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }
    
    /**
     * 根据商品价格id获取商品详情
     * @param bigint|string goodsId 商品id。
     * @return array
     */
    public function getGoodsDetail($goodsId) {
        if (!$goodsId) {
            return false;
        }
        $query = "select a.* from goods_detail a  where a.goodsId='$goodsId'";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }

    /**
     * 根据商品分类id获取分类信息
     * @param int $cateId
     * @return array 
     */
    public function getGoodsCateInfo($cateId) {
        if(!$cateId){
            return FALSE;
        }
        $query = "select * from goods_categary where cateId=$cateId";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }

    /**
     * 获取分类路径节点信息
     * @param string $cateIds 分类id，逗号分隔
     * @return type
     */
    public function getCateNodes($cateIds) {
        if (!$cateIds)
            return false;
        $query = "select cateId,cateName from goods_categary where cateId in ($cateIds)";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }

}
