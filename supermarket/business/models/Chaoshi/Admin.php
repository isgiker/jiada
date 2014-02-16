<?php

/**
 * @name Chaoshi_AdminModel
 * @desc 管理员,每个商家都可以为店铺建立管理员账号
 * @author Vic shiwei
 */
class Chaoshi_AdminModel extends BasicModel {

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada_sso');
    }

    public function getAdminList($search = array()) {
        $query = "select a.*,b.groupName from business_shop_admin a,business_admin_group b where a.businessId=$search[businessId] and a.agroupId=b.agroupId ";
        if (isset($search['shopId']) && $search['shopId']) {
            $query .=" and a.shopId = $search[shopId]";
        }
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
        $query = "select count(a.adminId) from business_shop_admin a,business_admin_group b where a.businessId=$search[businessId] and a.agroupId=b.agroupId ";
        if (isset($search['shopId']) && $search['shopId']) {
            $query .=" and a.shopId = $search[shopId]";
        }
        if (isset($search['agroupId']) && $search['agroupId']) {
            $query .=" and a.agroupId = $search[agroupId]";
        }
        $this->db->setQuery($query);
        $num = $this->db->loadResult();
        return $num;
    }

    public function getAdminInfo($adminId) {
        $query = "select * from business_shop_admin where adminId=$adminId";
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
        $adminId=$this->uuid_short();
        $sql = "insert business_shop_admin set adminId='$adminId',userName='$data[userName]',agroupId='$data[agroupId]',realName='$data[realName]',password='$password',status='$status',createTime='$createTime',shopId='$data[shopId]',businessId='$data[businessId]'";
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
            $sql = "update business_shop_admin set userName='$data[userName]',agroupId='$data[agroupId]',realName='$data[realName]',password='$password',status='$status',createTime='$createTime',shopId='$data[shopId]' where adminId='$data[adminId]' and businessId='$data[businessId]'";
        }else{
            $sql = "update business_shop_admin set userName='$data[userName]',agroupId='$data[agroupId]',realName='$data[realName]',status='$status',createTime='$createTime',shopId='$data[shopId]' where adminId=$data[adminId] and businessId='$data[businessId]'";
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
				},
		  		{
			 		"value":"shopId",
			  		"label":"所属店铺",
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
