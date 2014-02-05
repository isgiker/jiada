<?php

/**
 * @name Chaoshi_GoodscateModel
 * @desc 商品分类
 * @author root
 */
class Chaoshi_GoodscateModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
         
    
    //以下供外部控制器调用
    /*================================================================================================*/
    /**
     * 根据分类id获取分类名称
     * @param string $cateIds 多个id：1,2,3
     * @return array
     */
    public function getCatePathName($cateIds){
        $query = "select cateName from goods_categary where cateId in ($cateIds)";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    
    /**
     * 获取分类的子分类;如果$shopGcate有值则只显示店铺的分类数据
     * @param int $parentId 分类的父级节点id
     * @param string $shopGcate 店铺的所有分类id，字符串形式逗号分隔;默认为空
     * @return array
     */
    public function getNodeGcate($parentId=0, $shopGcate=null){
        $query = "select a.cateId as id, a.cateName as name, a.parentPath from goods_categary a where a.parentId=$parentId and a.status=1";
        if($shopGcate){
            $query .= " and a.cateId in($shopGcate)";
        }
        $query .=" order by a.sort asc,a.cateId desc ";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    
    /**
     * 获取分类信息
     * @param int $cateId 分类id
     * @return array
     */
    public function getGcateInfo($cateId){
        $query = "select * from goods_categary where cateId=$cateId";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }
}
