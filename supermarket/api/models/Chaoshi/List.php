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
        
        if(isset($param['sort']) && $param['sort'] && is_array($param['sort'])){
            
            switch ($param['sort'][0]) {
                case 'sales':
                    $sort=' a.publishTime';
                    $sortway=$param['sort'][1];
                    break;
                case 'price':
                    $sort=' b.currentPrice';
                    $sortway=$param['sort'][1];
                    break;
                case 'commentcount':
                    $sort=' a.publishTime';
                    $sortway=$param['sort'][1];
                    break;
                case 'time':
                    $sort=' a.publishTime';
                    $sortway=$param['sort'][1];
                    break;

                default:
                    break;
            }
            
        }
        
        if (isset($param['searchCondition']) && $param['searchCondition'] && is_array($param['searchCondition'])) {
            $query = "select a.cateId,a.brandId,a.goodsName,a.packPic,b.priceId,b.originalPrice,b.discount,b.currentPrice,b.marketPrice from goods a, goods_price b";
            $query .=" where a.cateId=$param[cateId]  and a.onLine=1 and a.goodsId=b.goodsId and b.shopId='$param[shopId]' ";

            foreach ($param['searchCondition'] as $sc) {
                $sc = explode(':', $sc);
                $sc[1] = trim($sc[1]);
                if(!$sc[1])
                    continue;
                $query .= " and a.goodsId=(SELECT goodsId from goods_attribute_value where goodsId=a.goodsId and attrId='$sc[0]' and attrValue='$sc[1]')";
            }
        } else {
            $query = "select a.cateId,a.brandId,a.goodsName,a.packPic,b.priceId,b.originalPrice,b.discount,b.currentPrice,b.marketPrice from goods a, goods_price b ";
            $query .=" where a.cateId=$param[cateId]  and a.onLine=1 and a.goodsId=b.goodsId and b.shopId='$param[shopId]' ";
        }
        $query .=" order by $sort $sortway ";
        $this->setLimit($param['limit']);
        $this->setLimitStart($param['pageNum']);
        $query .=' limit ' . $this->getLimitStart() . ', ' . $this->getLimit();
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        
        return $rows;
    }
    
    public function getProductListTotal($param) {
        if (!$param)
            return false;
        
        if(isset($param['sort']) && $param['sort'] && is_array($param['sort'])){
            
            switch ($param['sort'][0]) {
                case 'sales':
                    $sort=' a.publishTime';
                    $sortway=$param['sort'][1];
                    break;
                case 'price':
                    $sort=' b.currentPrice';
                    $sortway=$param['sort'][1];
                    break;
                case 'commentcount':
                    $sort=' a.publishTime';
                    $sortway=$param['sort'][1];
                    break;
                case 'time':
                    $sort=' a.publishTime';
                    $sortway=$param['sort'][1];
                    break;

                default:
                    break;
            }
            
        }
        
        if (isset($param['searchCondition']) && $param['searchCondition'] && is_array($param['searchCondition'])) {
            $query = "select count(a.goodsId) from goods a, goods_price b";
            $query .=" where a.cateId=$param[cateId]  and a.onLine=1 and a.goodsId=b.goodsId and b.shopId='$param[shopId]' ";

            foreach ($param['searchCondition'] as $sc) {
                $sc = explode(':', $sc);
                $sc[1] = trim($sc[1]);
                if(!$sc[1])
                    continue;
                $query .= " and a.goodsId=(SELECT goodsId from goods_attribute_value where goodsId=a.goodsId and attrId='$sc[0]' and attrValue='$sc[1]')";
            }
        } else {
            $query = "select count(a.goodsId) from goods a, goods_price b ";
            $query .=" where a.cateId=$param[cateId]  and a.onLine=1 and a.goodsId=b.goodsId and b.shopId='$param[shopId]' ";
        }
        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
        return $num;
    }


    /**
     * 获取商品分类的属性
     * @param int $cateId 分类Id
     * @return boolean
     */
    public function getProductAttr($cateId){
        if (!$cateId)
            return false;
        $query = "select attrId,attrName,attrValues,attrInputType from goods_attribute where goodsCateId='$cateId' and searchTerms=1 ";
        $query .=" order by sort asc,attrId asc ";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        
        return $rows;
    }
    
    /**
     * 获取商品属性对应的商品id
     * @param array $attrs 前端商品属性搜索条件
     * @return array 商品Id数组
     */
    public function getAttrs($attrs) {
        $sc_str = '';
        foreach ($attrs as $k=>$sc) {
            $sc = explode(':', $sc);
            $sc[1] = trim($sc[1]);
            $attrs[$k]=$sc[0].':'.$sc[1];
            if (!$sc_str) {
                $sc_str .= " (attrId='$sc[0]' and attrValue='$sc[1]')";
            } else {
                $sc_str .= " or (attrId='$sc[0]' and  attrValue='$sc[1]')";
            }
        }
        
        if ($sc_str) {
            $query = "SELECT goodsId,attrId,attrValue from goods_attribute_value where $sc_str GROUP BY goodsId";
            $this->hydb->setQuery($query);
            $rows = $this->hydb->loadAssocList();
            $goodsId_Arr=array();
            $notIn=array();
            if(isset($rows[0]) && $rows[0]){
                foreach ($rows as $key => $item) {
                    $sc=$item['attrId'].':'.$item['attrValue'];
                    if(in_array($sc, $attrs)){
                        $goodsId_Arr[]=$item['goodsId'];
                    }else{
                        $notIn[]=$item['goodsId'];
                    }                   
                    
                }
            }
            //计算数组差集
            $result = array_diff($goodsId_Arr, $notIn);
            return $rows;
        }
        return false;
    }

}
