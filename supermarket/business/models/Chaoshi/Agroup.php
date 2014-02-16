<?php

/**
 * @name Chaoshi_AgroupModel
 * @desc 用户组,每个商家都可以自定义用户组，暂不支持每个店铺自定义用户组。
 * @author Vic shiwei
 */
class Chaoshi_AgroupModel extends BasicModel {

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada_sso');
    }

    public function getAgroupList($search = array(), $parentId = 0, $tag = '') {
        $query = "select * from business_admin_group where businessId=$search[businessId]";
        if (isset($search['status'])) {
            $query .=" and status = $search[status]";
        }else{
            $query .=" and status = 1";
        }
        $query .=" and parentId=$parentId";
        $query .=" order by createTime desc ";

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        //递归调用;	
        static $newrows = array();
        if ($rows) {
            foreach ($rows as $key => $item) {
                $item['groupName'] = $tag . $item['groupName'];
                $newrows[] = $item;
                $this->getAgroupList($search, $item['agroupId'], $tag . "......&nbsp;|&nbsp;");
            }
        }
        
        return array_slice($newrows, $this->getLimitStart(), $this->getLimit());
    }

    public function getAgroupTotal($search = array()) {
        $query = "select count(agroupId) from business_admin_group where ";        
        if (isset($search['status'])) {
            $query .=" status = $search[status]";
        }else{
            $query .=" status = 1";
        }
        $this->db->setQuery($query);
        $num = $this->db->loadResult();
        return $num;
    }

    /**
     * 用户组树结构显示
     */
    function getTreeAgroup($cid = 0, $parentId = 0, $tag = '&nbsp;&nbsp;') {
        $query = "select * from business_admin_group where 1=1";
        $query .=" and parentId=$parentId";
        $query .=" and status = 1";
        $query .=" order by createTime desc ";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();


        $strstr = '';
        foreach ($rows as $key => $item) {
            if ($cid == $item['agroupId']) {
                $optionselect = ' selected="selected"';
            } else {
                $optionselect = '';
            }

            $strstr.="<option value='$item[agroupId]' $optionselect>" . $tag . $item['groupName'] . "</option>";

            $strstr.=$this->getTreeAgroup($cid, $item['agroupId'], $tag . "......&nbsp;|&nbsp;");
        }
        return $strstr;
    }

    public function getAgroupInfo($agroupId) {
        $query = "select * from business_admin_group where agroupId=$agroupId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }
    
    public function uuid_short(){
       $sql = "select uuid_short();";
       $this->db->setQuery($sql);
       $uuid_short = $this->db->loadResult();
       return $uuid_short;
    }

    public function add($data) {
        if (isset($data['acl']) && $data['acl']) {
            $acl = implode(',', $data['acl']);
        } else {
            $acl = '';
        }
        $createTime = time();
        if (isset($data['extends'])) {
            $extends = $data['extends'];
        } else {
            $extends = 0;
        }
        
        $agroupId=$this->uuid_short();
        
        $sql = "insert business_admin_group set agroupId='$agroupId',groupName='$data[groupName]',parentId='$data[parentId]',extends='$extends',acl='$acl',status='$data[status]',createTime='$createTime',businessId='$data[businessId]'";
        $result = $this->db->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    public function edit($data) {
        if (isset($data['acl']) && $data['acl']) {
            $acl = implode(',', $data['acl']);
        } else {
            $acl = '';
        }
        
        if (isset($data['extends'])) {
            $extends = $data['extends'];
        } else {
            $extends = 0;
        }
        $sql = "update business_admin_group set groupName='$data[groupName]',parentId='$data[parentId]',extends='$extends',acl='$acl',status='$data[status]' where agroupId='$data[agroupId]' and businessId='$data[businessId]'";
        $result = $this->db->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            $this->setErrorMsg($error);
            return false;
        }
        return true;
    }


    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[{
			 		"value":"groupName",
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
			 		"value":"parentId",
			  		"label":"父节点",
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
	 						"message":"必须是数字"
	 					}
		  			]	
				}
                                ]}';

        return $rules;
    }

}
