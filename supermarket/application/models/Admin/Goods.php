<?php

/**
 * @name Admin_GoodsModel
 * @desc 商品管理
 * @author Vic
 */
class Admin_GoodsModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada_supermarket');
    }

    public function getGoodsList($search = array()) {
        $query = "select * from goods where 1=1";
        if (isset($search['onLine']) && $search['onLine'] != 'all' && $search['onLine'] > 0) {
            $query .=" and status = $search[status]";
        }
        $query .=" order by publishTime desc ";

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        return $rows;
    }
    
    public function getGoodsTotal($search = array()){
        $query = "select count(goodsId) from goods where 1=1";
        if (isset($search['onLine']) && $search['onLine'] != 'all' && $search['onLine'] > 0) {
            $query .=" and status = $search[status]";
        }

        $this->db->setQuery($query);
        $num = $this->db->loadResult();
        return $num;
    }
    
    public function getGcateInfo($cateId){
        $query = "select cateId,cateName from goods_categary where cateId=$cateId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }
    
    public function getGoodsInfo($goodsId){
        $query = "select * from goods where goodsId=$goodsId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }
    
    public function childCateType($cateId){
        $query = "select cateId,cateName,parentPath from goods_categary where parentId=$cateId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();
        return $rows;
    }

    public function add($data) {
        $time = time();
        $query = "insert goods set cateId='$data[cateId]',brandId='$data[brandId]',goodsNme='$data[goodsNme]',originalPrice='$data[originalPrice]',discount='$data[discount]',currentPrice='$data[currentPrice]',marketPrice='$data[marketPrice]',activityStartTime='$data[activityStartTime]',activityEndTime='$data[activityEndTime]',onLine='$data[onLine]',publishTime='$time';";

        $result = $this->db->query($query);
        if ($result == false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        $goodsId = $this->db->insertid();
        return $goodsId;
    }

    public function edit($data) {
        $time = time();
        $query = "update goods set cateId='$data[cateId]',brandId='$data[brandId]',goodsNme='$data[goodsNme]',originalPrice='$data[originalPrice]',discount='$data[discount]',currentPrice='$data[currentPrice]',marketPrice='$data[marketPrice]',activityStartTime='$data[activityStartTime]',activityEndTime='$data[activityEndTime]',onLine='$data[onLine]',modifyTime='$time' where goodsId=$data[goodsId];";

        $result = $this->db->query($query);
        if ($result == false) {
            $error = $this->db->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"cateId",
			  		"label":"商品分类",
			  		"rules":[
						{
	  						"name":"clearxss"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	 					{
	 						"name":"number",
	 						"value":"/^[0-9]{1,}$/",
	 						"message":"%s%长度为1位或以上的数字"
	 					}
		  			]	
				},
                                {
			 		"value":"brandId",
			  		"label":"商品品牌",
			  		"rules":[
						{
	  						"name":"clearxss"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	 					{
	 						"name":"number",
	 						"value":"/^[0-9]{1,}$/",
	 						"message":"%s%长度为1位或以上的数字"
	 					}
		  			]	
				},
                                {
			 		"value":"goodsNme",
			  		"label":"商品名称",
			  		"rules":[	  					
						{
		  					"name":"clearxss"				
	 					},						
	 					{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					}
		  			]	
		  		},
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
				},
		  		{
			 		"value":"onLine",
			  		"label":"是否上架",
			  		"rules":[
						{
	  						"name":"clearxss"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					}
		  			]	
				}
                                ]}';

        return $rules;
    }

}
