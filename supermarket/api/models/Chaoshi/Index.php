<?php

/**
 * @name Chaoshi_IndexModel
 * @desc 商品分类
 * @author Vic
 */
class Chaoshi_IndexModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    /**
     * 获取分类下的商品类型
     * @param int $cateId 分类id
     * @param int $limit 条数
     * @return array
     */
    public function getGoodsType($cateId, $limit){
        if(!$cateId || !$limit){
            return false;
        }
        $query = "select a.cateId,a.cateName,a.pinyin from goods_categary a where FIND_IN_SET('$cateId',a.parentPath) and a.childNums=0 and a.status=1";
        $query .=" order by a.sort asc,a.createTime desc ";
        $query .=' limit 0, '.$limit;

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    
    /**
     * 根据分类(商品类型)/多个分类(商品类型)获取旗下的商品
     * @param string $shopId,多个店铺id用逗号分隔。
     * @param string $catesId 分类(商品类型)id,分类(商品类型)id;多个分类id用逗号分隔。
     * @param int $limit 条数
     * @return array
     */
    public function getCatesGoods($shopId, $catesId, $limit){
        if(!$catesId || !$limit){
            return false;
        }
        $query = "select a.cateId,a.brandId,a.goodsName,a.packPic,b.priceId,b.originalPrice,b.discount,b.currentPrice,b.marketPrice from goods a, goods_price b ";
        $query .=" where a.cateId in ('$catesId')  and a.onLine=1 and a.goodsId=b.goodsId and b.shopId='$shopId' ";
        $query .=" order by a.publishTime desc ";
        $query .=' limit 0, '.$limit;

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    
    
    /**
     * 获取分类下的品牌（暂且按照品牌销量排序）
     * @param int $cateId 分类id
     * @param int $limit 条数
     * @return array|json
     */
    public function getCatesBrand($cateId, $limit){
        if(!$cateId || !$limit){
            return false;
        }
        $query = "select a.brandId,a.brandName,a.pinyin from goods_brand a where a.cateId='$cateId' and a.status=1";
        $query .=" order by a.sales desc";
        $query .=' limit 0, '.$limit;

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    
    

}
