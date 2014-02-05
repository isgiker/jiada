<?php

/**
 * @name Chaoshi_GoodsbrandModel
 * @desc 商品品牌
 * @author Vic Shiwei
 */
class Chaoshi_GoodsbrandModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    /**
     * 获取分类（该商品类型）的所有品牌,如果$shopGbrands有值则只显示店铺的品牌数据
     * @param int $cateId 分类id
     * @param string $shopGbrands 店铺的所有品牌id，字符串形式逗号分隔;默认为空
     * @return array
     */
    public function getCateBrand($cateId, $shopGbrands=null){
        if(!$cateId) return false;
        $query = "SELECT a.brandId as id, a.brandName as name, a.cateId FROM goods_brand a where FIND_IN_SET('$cateId',a.childCateType)";
        if($shopGbrands){
            $query .= " and a.brandId in($shopGbrands)";
        }
        $query .=" order by a.pinyin asc, a.brandId desc ";

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    
}
