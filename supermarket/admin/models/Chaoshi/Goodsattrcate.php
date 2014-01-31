<?php

/**
 * @name Chaoshi_GoodsattrcateModel
 * @desc 商品属性分类
 * @author Vic Shi
 */
class Chaoshi_GoodsattrcateModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada_chaoshi');
    }

    public function getGattrcateList($goodsCateId) {
        $query = "select * from goods_attribute_categary where goodsCateId=$goodsCateId";
        $query .=" order by sort asc,attrCateId desc ";

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        return $rows;
    }
    
    public function getGattrcateTotal($goodsCateId){
        $query = "select count(attrCateId) from goods_attribute_categary where goodsCateId=$goodsCateId";

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
    
    public function getGattrcateInfo($attrCateId){
        $query = "select * from goods_attribute_categary where attrCateId=$attrCateId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }
    
    public function add($data) {
        if (isset($data['goodsCateId']) && $data['goodsCateId']) {
            $query = "insert goods_attribute_categary set attrCateName='$data[attrCateName]',sort='$data[sort]',goodsCateId='$data[goodsCateId]';";
            $result = $this->db->query($query);
            if ($result == false) {
                $error = $db->ErrorMsg();
                die("$error");
            }
        } else {
            return false;
        }
        return true;
    }

    public function edit($data) {
         if (isset($data['attrCateId']) && $data['attrCateId']) {
            $query = "update goods_attribute_categary set attrCateName='$data[attrCateName]',sort='$data[sort]',goodsCateId='$data[goodsCateId]' where attrCateId='$data[attrCateId]';";
            $result = $this->db->query($query);
            if ($result == false) {
                $error = $db->ErrorMsg();
                die("$error");
            }
        } else {
            return false;
        }
        return true;
    }
    
    public function del($data){
        $ids = implode(',', $data['checkIds']);
        if ($ids) {
            $query = "delete from goods_attribute_categary  where attrCateId in($ids);";
            $result = $this->db->query($query);
            if ($result == false) {
                $error = $db->ErrorMsg();
                die("$error");
            }
        } else {
            return false;
        }
        return true;
    }


    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"attrCateName",
			  		"label":"属性分类名称",
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
				}
                                ]}';

        return $rules;
    }

}
