<?php

/**
 * @name SampleModel
 * @desc sample数据获取类, 可以访问数据库，文件，其它系统等
 * @author root
 */
class Admin_AreaModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada_central');
    }

    public function getAreaList($search=array(), $parentId = 0, $tag = '') {
        $query = "select * from area where 1=1";
        $query .=" and parentId=$parentId";
        if(isset($search['public']) && $search['public']!='all' && $search['public']>0){
            $query .=" and public = $search[public]";
        }        
        $query .=" order by sort asc,areaId desc ";

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        //递归调用;	
        static $newrows = array();
        foreach ($rows as $key => $item) {
            $item['areaName'] = $tag . $item['areaName'];
            $newrows[] = $item;
            $this->getAreaList($search, $item['areaId'], $tag . "......&nbsp;|&nbsp;");
        }
        return array_slice($newrows, $this->getLimitStart(), $this->getLimit());
    }
    
    public function getAreaTotal($search=array()){
        $query = "select count(areaId) from area where 1=1";
        if(isset($search['public']) && $search['public']!='all' && $search['public']>0){
            $query .=" and public = $search[public]";
        }
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
    
    public function getAreaInfo($areaId){
        $query = "select * from area where areaId=$areaId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }

    public function add($data) {
        if($data['parentId']==0){
            $parentPath = 0;
        }else{
            $parentPath = $this->getParentPath($data['parentId']);
        }
        $sql = "insert area set areaName='$data[areaName]',pinyin='$data[pinyin]',parentId='$data[parentId]',parentPath='$parentPath',domain='',sort='$data[sort]',public='$data[public]'";
        $result = $this->db->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    public function edit($data) {
        if($data['parentId']==0){
            $parentPath = 0;
        }else{
            $parentPath = $this->getParentPath($data['parentId']);
        }
        $sql = "update area set areaName='$data[areaName]',pinyin='$data[pinyin]',parentId='$data[parentId]',parentPath='$parentPath',domain='',sort='$data[sort]',public='$data[public]' where areaId=$data[areaId]";
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
     * 根据areaId获取地区名称
     * @param string $areaIds 逗号分隔
     * @return array 数组
     */
    public function getAreaNames($areaIds){
        if(!$areaIds){
            return false;
        }
        
        $query = "select areaName from area where areaId in($areaIds)";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();
        $names='';
        if($rows){
            foreach($rows as $item){
                $names?$names.='/'.$item['areaName']:$names=$item['areaName'];
            }
        }
        return $names;
    }

    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"areaName",
			  		"label":"区域名称",
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
