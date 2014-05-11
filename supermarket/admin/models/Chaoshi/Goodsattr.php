<?php

/**
 * @name Chaoshi_GoodsattrModel
 * @desc 商品属性
 * @author root
 */
class Chaoshi_GoodsattrModel extends BasicModel {

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada_chaoshi');
    }

    public function getGattrList($goodsCateId) {
        $query = "select a.*,(select cateName from goods_categary where cateId = a.goodsCateId)as cateName,(select attrCateName from goods_attribute_categary where attrCateId = a.attrCateId)as attrCateName from goods_attribute a where a.goodsCateId=$goodsCateId";
        $query .=" order by a.attrCateId,a.sort,a.createTime asc";

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        return $rows;
    }

    public function getGattrTotal($goodsCateId) {
        $query = "select count(attrId) from goods_attribute where goodsCateId=$goodsCateId";

        $this->db->setQuery($query);
        $num = $this->db->loadResult();
        return $num;
    }

    public function getGattrInfo($attrId) {
        $query = "select * from goods_attribute where attrId=$attrId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }
    
    public function getGcateInfo($cateId) {
        $query = "select cateId,cateName from goods_categary where cateId=$cateId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }

    public function getGattrCate($gCateId) {
        $query = "select * from goods_attribute_categary where goodsCateId=$gCateId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();
        return $rows;
    }

    public function add($data) {
        $time = time();
        $query = "insert goods_attribute set goodsCateId='$data[goodsCateId]',attrCateId='$data[attrCateId]',attrName='$data[attrName]',attrInputType='$data[attrInputType]',attrValues='$data[attrValues]',searchTerms='$data[searchTerms]',isRequired='$data[isRequired]',sort='$data[sort]',createTime='$time';";
        $result = $this->db->query($query);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    public function edit($data) {
        $query = "update goods_attribute set goodsCateId='$data[goodsCateId]',attrCateId='$data[attrCateId]',attrName='$data[attrName]',attrInputType='$data[attrInputType]',attrValues='$data[attrValues]',searchTerms='$data[searchTerms]',isRequired='$data[isRequired]',sort='$data[sort]' where attrId=$data[attrId];";
        $result = $this->db->query($query);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"attrName",
			  		"label":"属性名称",
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
			 		"value":"attrInputType",
			  		"label":"属性值录入方式",
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
	 						"value":"/^[A-Za-z]{2,}$/",
	 						"message":"%s%长度为2位以上的字母"
	 					}
		  			]	
				},
		  		{
			 		"value":"goodsCateId",
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
			 		"value":"attrCateId",
			  		"label":"属性分类",
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
			 		"value":"sort",
			  		"label":"排序",
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
				}
                                ]}';

        return $rules;
    }

}
