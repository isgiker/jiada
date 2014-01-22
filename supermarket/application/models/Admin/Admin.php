<?php

/**
 * @name Admin_AdminModel
 * @desc 管理员
 * @author Vic shiwei
 */
class Admin_AdminModel extends BasicModel {

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada_central');
    }

    public function getAdminList($search = array()) {
        $query = "select a.*,b.groupName from admin a,admin_group b where a.agroupId=b.agroupId ";
        if (isset($search['agroupId']) && $search['agroupId']) {
            $query .=" and a.agroupId = $search[agroupId]";
        }
        $query .=" order by a.createTime desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();

        return $rows;
    }

    public function getAdminTotal($search = array()) {
        $query = "select count(agroupId) from admin where 1=1 ";        
        if (isset($search['agroupId']) && $search['agroupId']) {
            $query .=" and agroupId = $search[agroupId]";
        }
        $this->db->setQuery($query);
        $num = $this->db->loadResult();
        return $num;
    }

    public function getAdminInfo($adminId) {
        $query = "select * from admin where adminId=$adminId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }

    public function add($data) {
        if (isset($data['password']) && $data['password']) {
            $password=strrev(sha1($data['password']));
            $password=md5($password);
        } else {
            return false;
        }
        $createTime = time();
        if (isset($data['status'])) {
            $status = $data['status'];
        } else {
            $status = 1;
        }
        $sql = "insert admin set userName='$data[userName]',agroupId='$data[agroupId]',realName='$data[realName]',password='$password',status='$status',createTime='$createTime'";
        $result = $this->db->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    public function edit($data) {
        if (isset($data['password']) && trim($data['password'])) {
            $password=strrev(sha1($data['password']));
            $password=md5($password);
        } else {
            $password='';
        }
        $createTime = time();
        if (isset($data['status'])) {
            $status = $data['status'];
        } else {
            $status = 1;
        }
        if($password){
            $sql = "update admin set userName='$data[userName]',agroupId='$data[agroupId]',realName='$data[realName]',password='$password',status='$status',createTime='$createTime' where adminId=$data[adminId]";
        }else{
            $sql = "update admin set userName='$data[userName]',agroupId='$data[agroupId]',realName='$data[realName]',status='$status',createTime='$createTime' where adminId=$data[adminId]";
        }
        
        $result = $this->db->query($sql);
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
			 		"value":"realName",
			  		"label":"真实姓名",
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
			 		"value":"userName",
			  		"label":"登录名",
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
	 					},
	 					{
		  					"name":"regex",
		  					"value":"/^[A-Za-z0-9_\\\\-]{3,20}$/",
			  				"message":"%s%应为3到20位的字母、数字、字符和下划线"
	  					}
		  			]	
		  		},
                                {
			 		"value":"password",
			  		"label":"密码",
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
	 					},						
	 					{
 						"name":"rangelength",
 						"value":"[6,20]",
 						"message":"%s%长度为6到20位"
 						}
		  			]	
		  		},
		  		{
			 		"value":"agroupId",
			  		"label":"用户组",
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
