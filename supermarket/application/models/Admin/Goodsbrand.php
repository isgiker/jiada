<?php

/**
 * @name SampleModel
 * @desc sample数据获取类, 可以访问数据库，文件，其它系统等
 * @author root
 */
class Admin_GoodsbrandModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada_supermarket');
    }

    public function getGbrandList($cateId = null, $search = array()) {
        $query = "select * from goods_brand where 1=1";
        if ($cateId) {
            $query .=" and cateId=$cateId";
        }
        if (isset($search['status']) && $search['status'] != 'all' && $search['status'] > 0) {
            $query .=" and status = $search[status]";
        }
        $query .=" order by pinyin asc,brandId desc ";

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        return $rows;
    }
    
    public function getGbrandTotal($cateId = null, $search = array()){
        $query = "select count(brandId) from goods_brand where 1=1";
        if ($cateId) {
            $query .=" and cateId=$cateId";
        }
        if (isset($search['status']) && $search['status'] != 'all' && $search['status'] > 0) {
            $query .=" and status = $search[status]";
        }
        $query .=" order by pinyin asc,brandId desc ";

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
    
    public function getGbrandInfo($brandId){
        $query = "select * from goods_brand where brandId=$brandId";
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
    
    /**
     * 获取分类下的所有品牌
     */
    public function getCateBrand($cateId){
        $query = "SELECT * FROM goods_brand where  FIND_IN_SET('$cateId',childCateType)";
        $query .=" order by pinyin asc,brandId desc ";

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();
        return $rows;
    }

    public function add($data) {
        $time = time();
        if ($data['childCateType']) {
            $childCateType = implode(',', $data['childCateType']);
            $query = "insert goods_brand set brandName='$data[brandName]',pinyin='$data[pinyin]',cateId='$data[cateId]',childCateType='$childCateType',status='$data[status]',createTime='$time';";

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
        if ($data['childCateType']) {
            $childCateType = implode(',', $data['childCateType']);
            $query = "update goods_brand set brandName='$data[brandName]',pinyin='$data[pinyin]',cateId='$data[cateId]',childCateType='$childCateType',status='$data[status]' where brandId=$data[brandId];";

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
			 		"value":"brandName",
			  		"label":"商品品牌名称",
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
			 		"value":"pinyin",
			  		"label":"拼音",
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
	 						"value":"/^[A-Za-z0-9]{2,}$/",
	 						"message":"%s%长度为2位以上的字母、数字"
	 					}
		  			]	
				},
		  		{
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
			 		"value":"childCateType",
			  		"label":"商品类型",
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
