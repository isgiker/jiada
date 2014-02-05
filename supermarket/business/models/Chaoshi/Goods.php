<?php

/**
 * @name Chaoshi_GoodsModel
 * @desc 商品管理
 * @author Vic
 */
class Chaoshi_GoodsModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    /**
     * 条件：只显示已上架的商品
     * @param type $search
     * @return type
     */
    public function getGoodsList($search = array()) {
        $query = "select a.*,b.cateName,c.brandName, d.priceId from goods a left join goods_price d on a.goodsId=d.goodsId ,goods_categary b,goods_brand c where  a.onLine = 1 and a.cateId=b.cateId and a.brandId=c.brandId";
        if (isset($search['cateId']) && $search['cateId']) {
            $query .=" and a.cateId = $search[cateId]";
        }
        if (isset($search['brandId']) && $search['brandId']) {
            $query .=" and a.brandId = $search[brandId]";
        }
        if (isset($search['goodsName']) && $search['goodsName']) {
            $query .=" and instr(a.goodsName, '$search[goodsName]')";
        }

        $query .=" order by a.publishTime desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();

        return $rows;
    }
    
    public function getGoodsTotal($search = array()){
        $query = "select count(a.goodsId) from goods a left join goods_price d on a.goodsId=d.goodsId ,goods_categary b,goods_brand c where  a.onLine = 1 and a.cateId=b.cateId and a.brandId=c.brandId";
        if (isset($search['cateId']) && $search['cateId']) {
            $query .=" and a.cateId = $search[cateId]";
        }
        if (isset($search['brandId']) && $search['brandId']) {
            $query .=" and a.brandId = $search[brandId]";
        }
        if (isset($search['goodsName']) && $search['goodsName']) {
            $query .=" and instr(a.goodsName, '$search[goodsName]')";
        }

        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
        return $num;
    }
   
    public function getGoodsInfo($goodsId){
        $query = "select a.* ,b.goodsDesc,b.goodsPics,b.goodsRemark from goods a left join goods_detail b on a.goodsId=b.goodsId where a.goodsId=$goodsId ";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }
    
    
    public function uuid_short(){
       $sql = "select uuid_short();";
       $this->hydb->setQuery($sql);
       $uuid_short = $this->hydb->loadResult();
       return $uuid_short;
    }
    
    /**
     * 发布商品
     * @param type $data
     * @return boolean
     */
    public function price($data) {
        if(!$data['shopId'] || !$data['goodsId']){
            return false;
        }
        $time = time();
        $priceId=$this->uuid_short();
        $activityStartTime = strtotime($data['activityStartTime']);
        $activityEndTime = strtotime($data['activityEndTime']);
        $query = "replace into goods_price set priceId='$priceId',shopId='$data[shopId]',goodsId='$data[goodsId]',originalPrice='$data[originalPrice]',discount='$data[discount]',currentPrice='$data[currentPrice]',marketPrice='$data[marketPrice]',activityStartTime='$activityStartTime',activityEndTime='$activityEndTime',status='1';";

        $result = $this->hydb->query($query);
        if ($result == false) {
            $error = $this->hydb->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    public function getPriceRules() {
        $rules = '{"validation":[
		  		{
			 		"value":"originalPrice",
			  		"label":"商品原价",
			  		"rules":[
						{
	  						"name":"clearxss"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	 					{
	 						"name":"regex",
	 						"value":"/^([0-9]+)[.]([0-9]{1,2})$/",
	 						"message":"%s%必须为数字格式为：19.88"
	 					}
		  			]	
				},
                                {
			 		"value":"currentPrice",
			  		"label":"商品现价",
			  		"rules":[
						{
	  						"name":"clearxss"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	 					{
	 						"name":"regex",
	 						"value":"/^([0-9]+)[.]([0-9]{1,2})$/",
	 						"message":"%s%必须为数字格式为：19.88"
	 					}
		  			]	
				},
                                {
			 		"value":"discount",
			  		"label":"折扣比例",
			  		"rules":[
						{
	  						"name":"clearxss"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	 					{
	 						"name":"regex",
	 						"value":"/^([0]?)[.]*([0-9]{0,2})$/",
	 						"message":"%s%必须为数字格式为：0.85"
	 					}
		  			]	
				}
                                ]}';

        return $rules;
    }
    

}
