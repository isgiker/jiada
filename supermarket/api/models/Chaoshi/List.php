<?php

/**
 * @name Chaoshi_ListModel
 * @desc 商品详情
 * @author Vic
 */
class Chaoshi_ListModel extends BasicModel {

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }

    /**
     * 获取分类路径节点信息
     * @param string $cateIds 分类id，逗号分隔
     * @return type
     */
    public function getCategaryList($cateId) {
        if (!$cateId)
            return false;
        $query = "select cateId,cateName,parentPath from goods_categary where parentId=$cateId";
        $query .=" and status = 1";
        $query .=" order by sort asc,cateId desc ";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        
        //递归调用;	
        if($rows){
            foreach ($rows as $key => $item) {
                $childs=$this->getCategaryChild($item['cateId']);
                $rows[$key]['child'] = $childs;

            }
        }
        return $rows;
    }
    
    public function getCategaryChild($cateId){
        if (!$cateId)
            return false;
        $query = "select cateId,cateName,parentPath from goods_categary where parentId=$cateId";
        $query .=" and status = 1";
        $query .=" order by sort asc,cateId desc ";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        
        return $rows;
    }
    
    public function getCategaryInfo($cateId){
        if (!$cateId)
            return false;
        $query = "select * from goods_categary where cateId=$cateId";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }
    
    public function getProductList($param) {
        if (!$param)
            return false;

        $query = "select a.cateId,a.brandId,a.goodsName,a.packPic,b.priceId,b.originalPrice,b.discount,b.currentPrice,b.marketPrice from goods a, goods_price b ";
        $query .=" where a.cateId=$param[cateId]  and a.onLine=1 and a.goodsId=b.goodsId and b.shopId='$param[shopId]' ";
        $query .=" order by a.publishTime desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        
        return $rows;
    }

}
