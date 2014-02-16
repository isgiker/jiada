<?php

/**
 * @name Chaoshi_GoodsbrandModel
 * @desc 商品品牌
 * @author Vic Shiwei
 */
class Chaoshi_GoodsbrandModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }

    public function getGbrandList($cateId = null, $search = array()) {
        $query = "select * from goods_brand where 1=1";
        if ($cateId) {
            $query .=" and cateId=$cateId";
        }
        if(isset($search['gcateChildren']) && $search['gcateChildren'] && $search['gcateChildren']!=$cateId){
            $query .=" and FIND_IN_SET($search[gcateChildren],childCateType)";
        }
        if (!isset($search['status']) || !$search['status']) {
            //默认显示已审核通过的
            $query .=" and status = 1";            
        }elseif (isset($search['status']) && $search['status'] !='all') {
            $query .=" and status = $search[status]";
        }
        $query .=" order by pinyin asc,brandId desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();

        return $rows;
    }
    
    public function getGbrandTotal($cateId = null, $search = array()){
        $query = "select count(brandId) from goods_brand where 1=1";
        if ($cateId) {
            $query .=" and cateId=$cateId";
        }
        if(isset($search['gcateChildren']) && $search['gcateChildren'] && $search['gcateChildren']!=$cateId){
            $query .=" and FIND_IN_SET($search[gcateChildren],childCateType)";
        }
        if (!isset($search['status']) || !$search['status']) {
            //默认显示已审核通过的
            $query .=" and status = 1";            
        }elseif (isset($search['status']) && $search['status'] !='all') {
            $query .=" and status = $search[status]";
        }

        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
        return $num;
    }
    
    public function getGcateInfo($cateId){
        $query = "select a.cateId,a.cateName from goods_categary a where a.cateId=$cateId";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }
    
    public function getGbrandInfo($brandId){
        $query = "select * from goods_brand where brandId=$brandId";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }
    
    public function childCateType($cateId){
        $query = "select cateId,cateName,parentPath from goods_categary where parentId=$cateId";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
 
    /**
     * 获取分类下（该商品类型）的所有品牌
     * @param int $cateId 分类id
     * @return array
     */
    public function getCateBrand($cateId){
        if(!$cateId) return false;
        $query = "SELECT a.*,a.brandId as id, a.brandName as name, a.cateId FROM goods_brand a where FIND_IN_SET('$cateId',a.childCateType)";
        $query .=" order by a.pinyin asc, a.brandId desc ";

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }

    public function add($data) {
        $time = time();
        if ($data['childCateType']) {
            $childCateType = implode(',', $data['childCateType']);
            $query = "replace into goods_brand set brandName='$data[brandName]',pinyin='$data[pinyin]',cateId='$data[cateId]',childCateType='$childCateType',status='$data[status]',createTime='$time';";

            $result = $this->hydb->query($query);
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

            $result = $this->hydb->query($query);
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
	 						"value":"/^[a-zA-Z_\\\\s?\\\/?]{2,}$/",
	 						"message":"%s%长度为2位以上的字母、数字、_、空格的组合"
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
