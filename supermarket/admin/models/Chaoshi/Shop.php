<?php

/**
 * @name Chaoshi_ShopModel
 * @desc 
 * @author Vic
 */
class Chaoshi_ShopModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }

    public function getShopList($search = array()) {
        $query = "select a.* from shop_basic a where 1=1";
        if (!isset($search['status']) || !$search['status']) {
            //默认显示已审核通过的
            $query .=" and a.status = 1";
        }elseif (isset($search['status']) && $search['status'] !='all') {
            $query .=" and a.status = $search[status]";
        }
        $query .=" order by a.createTime desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();

        return $rows;
    }
    
    public function getShopTotal($search=array()){
        $query = "select count(shopId) from shop_basic";
        $this->hydb->setQuery($query);
        $num = $this->hydb->loadResult();
        return $num;
    }

    /**
     * 行业树结构显示 parent category
     */
    function getTreeArea($cid=0, $parentId=0, $tag='&nbsp;&nbsp;') {
        $query = "select * from area where 1=1";
        $query .=" and parentId=$parentId";
        $query .=" and public = 1";
        $query .=" order by sort asc,pinyin asc,areaId desc ";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssocList();


        $strstr = '';
        foreach ($rows as $key => $item) {
            if ($cid == $item['areaId']) {
                $optionselect = ' selected="selected"';
            } else {
                $optionselect = '';
            }

            $strstr.="<option value='$item[areaId]' $optionselect>" . $tag . $item['areaName'] . "</option>";

            $strstr.=$this->getTreeArea($cid, $item['areaId'], $tag . "......&nbsp;|&nbsp;");
        }
        return $strstr;
    }
    
    public function getShopInfo($shopId){
        $query = "select * from shop_basic where shopId=$shopId";
        $this->hydb->setQuery($query);
        $rows = $this->hydb->loadAssoc();
        return $rows;
    }

    public function add($data) {
        $time=time();
        $sql = "insert storehouse set storehouseName='$data[storehouseName]',provinceId='$data[provinceId]',cityId='$data[cityId]',districtId='$data[districtId]',address='$data[address]',tel='$data[tel]',openedTime='$data[openedTime]',createTime='$time'";
        $result = $this->hydb->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    public function edit($data) {
        $sql = "update shop_basic set shopName='$data[shopName]',provinceId='$data[provinceId]',cityId='$data[cityId]',districtId='$data[districtId]',address='$data[address]',lng='$data[lng]',lat='$data[lat]',shopDesc='$data[shopDesc]',contact='$data[contact]',mobile='$data[mobile]',tel='$data[tel]',workingTime='$data[workingTime]',status='$data[status]' where shopId=$data[shopId]";
        $result = $this->hydb->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    private function getParentPath($parentId){
        $query = "select parentId from area where areaId = $parentId";
        $this->hydb->setQuery($query);
        $newParentId = $this->hydb->loadResult();
        
        static $parentPath = array();
        if($newParentId!=0){
            $parentPath[] = $this->getParentPath($newParentId);
        }
        
        if(!empty($parentPath)){
            $parentPath = implode(',', $parentPath);
            $result = $parentId.','.$parentPath;
        }else{
            $result = $parentId;
        }
        
        return $result;
    }

    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"storehouseName",
			  		"label":"仓库名称",
			  		"rules":[
                                                {
	  						"name":"trim"
	  					},
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
			 		"value":"address",
			  		"label":"详细地址",
			  		"rules":[
                                                {
	  						"name":"trim"
	  					},
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
			 		"value":"mobile",
			  		"label":"联系人手机",
			  		"rules":[
                                                {
	  						"name":"trim"
	  					},
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
			 		"value":"tel",
			  		"label":"联系电话",
			  		"rules":[
                                                {
	  						"name":"trim"
	  					},
						{
	  						"name":"clearxss"
	  					},
	 					{
	 						"name":"regex",
	 						"value":"/^\\\d{2,5}-\\\d{7,9}$/",
	 						"message":"%s%格式为：区号-号码"
	 					}
		  			]	
				},
                                {
                                        "value":"provinceId",
                                                "label":"省市",
                                                "rules":[
                                                        {
                                                                "name":"trim"
                                                        },
                                                        {
                                                                "name":"required",
                                                                "message":"%s%为必填项"
                                                        }
                                                ]
                                },
                                {
                                        "value":"cityId",
                                                "label":"城市",
                                                "rules":[
                                                        {
                                                                "name":"trim"
                                                        },
                                                        {
                                                                "name":"required",
                                                                "message":"%s%为必填项"
                                                        },
                                                        {
                                                                "name":"clearxss"
                                                        }
                                                ]
                                },
                                {
                                        "value":"districtId",
                                                "label":"区县",
                                                "rules":[
                                                        {
                                                                "name":"trim"
                                                        },
                                                        {
                                                                "name":"required",
                                                                "message":"%s%为必填项"
                                                        },
                                                        {
                                                                "name":"clearxss"
                                                        }
                                                ]
                                }
                                ]}';

        return $rules;
    }

}
