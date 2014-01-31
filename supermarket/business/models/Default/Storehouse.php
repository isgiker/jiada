<?php

/**
 * @name Default_StorehouseModel
 * @desc 
 * @author Vic
 */
class Default_StorehouseModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
    }

    public function getStorehouseList() {
        $query = "select a.*,(select areaName from area where areaId=a.provinceId) as province,(select areaName from area where areaId=a.cityId) as city,(select areaName from area where areaId=a.districtId) as district from storehouse a";
        $query .=" order by a.createTime desc ";

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        return $rows;
    }
    
    public function getStorehouseTotal($search=array()){
        $query = "select count(storehouseId) from storehouse";
        $this->db->setQuery($query);
        $num = $this->db->loadResult();
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
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();


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
    
    public function getStorehouseInfo($storehouseId){
        $query = "select * from storehouse where storehouseId=$storehouseId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }
    
    /**
     * 获取省份
     */
    public function getAreas($parentId=0){
        $query = "select * from area where parentId=$parentId and public=1";
        $query .=" order by sort asc,areaId desc ";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();
        
        return $rows;
    }

    public function add($data) {
        $time=time();
        $sql = "insert storehouse set storehouseName='$data[storehouseName]',provinceId='$data[provinceId]',cityId='$data[cityId]',districtId='$data[districtId]',address='$data[address]',tel='$data[tel]',openedTime='$data[openedTime]',createTime='$time'";
        $result = $this->db->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    public function edit($data) {
        $sql = "update storehouse set storehouseName='$data[storehouseName]',provinceId='$data[provinceId]',cityId='$data[cityId]',districtId='$data[districtId]',address='$data[address]',tel='$data[tel]',openedTime='$data[openedTime]' where storehouseId=$data[storehouseId]";
        $result = $this->db->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    private function getParentPath($parentId){
        $query = "select parentId from area where areaId = $parentId";
        $this->db->setQuery($query);
        $newParentId = $this->db->loadResult();
        
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
	 						"name":"required",
	 						"message":"%s%为必填项"
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
