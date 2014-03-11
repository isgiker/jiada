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


}
