<?php

/**
 * @name Default_StorehouseModel
 * @desc 商家
 * @author Vic
 */
class Default_BusinessModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->dbsso = Factory::getDBO('local_jiada_sso');
    }

    public function getBusinessList($search = array()) {
        $query = "select a.* from business a where 1=1 ";
        if (!isset($search['status']) || !$search['status']) {
            //默认显示已审核通过的
            $query .=" and a.status = 1";            
        }elseif (isset($search['status']) && $search['status'] !='all') {
            $query .=" and a.status = $search[status]";
        }
        $query .=" order by a.createTime desc ";
        $query .=' limit '.$this->getLimitStart().', '.$this->getLimit();

        $this->dbsso->setQuery($query);
        $rows = $this->dbsso->loadAssocList();

        return $rows;
    }
    
    public function getBusinessTotal($search=array()){
        $query = "select count(a.businessId) from business a where 1=1 ";
        if (!isset($search['status']) || !$search['status']) {
            //默认显示已审核通过的
            $query .=" and a.status = 1";            
        }elseif (isset($search['status']) && $search['status'] !='all') {
            $query .=" and a.status = $search[status]";
        }
        $this->dbsso->setQuery($query);
        $num = $this->dbsso->loadResult();
        return $num;
    }

    public function getBusinessInfo($businessId){
        $query = "select * from business where businessId='$businessId'";
        $this->dbsso->setQuery($query);
        $rows = $this->dbsso->loadAssoc();
        return $rows;
    }
    
    /**
     * 查看用户是否存在
     * @param string $userName
     * @return id
     */
    public function checkUsername($userName){
        if(!$userName){
            return false;
        }
        $query = "select businessId from business where userName='$userName' limit 1;";
        $this->dbsso->setQuery($query);
        $id = $this->dbsso->loadResult();
        return $id;
    }
    
    public function uuid_short(){
       $sql = "select uuid_short();";
       $this->dbsso->setQuery($sql);
       $uuid_short = $this->dbsso->loadResult();
       return $uuid_short;
    }

    public function add($data) {
        $time=time();
        $businessId = $this->uuid_short();
        if (isset($data['password']) && $data['password']) {
            $password=strrev(sha1($data['password']));
            $password=md5($password);
        } else {
            return false;
        }
        $sql = "insert business set 
                                    businessId='$businessId',
                                    userName='$data[userName]',
                                    password='$password',
                                    contact='$data[contact]',
                                    mobile='$data[mobile]',
                                    tel='$data[tel]',
                                    title='$data[title]',
                                    provinceId='$data[provinceId]',
                                    cityId='$data[cityId]',
                                    districtId='$data[districtId]',
                                    address='$data[address]',
                                    industryId='$data[industryId]',
                                    status='-1',
                                    createTime='$time'";
        $result = $this->dbsso->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }

    public function edit($data) {
        $time=time();
        if (isset($data['password']) && trim($data['password'])) {
            $password=strrev(sha1($data['password']));
            $password=md5($password);
        } else {
            $password='';
        }
        if (isset($data['status'])) {
            $status = $data['status'];
        } else {
            $status = 1;
        }
        if($password){
            $sql = "update business set userName='$data[userName]',
                                    password='$password',
                                    contact='$data[contact]',
                                    mobile='$data[mobile]',
                                    tel='$data[tel]',
                                    title='$data[title]',
                                    provinceId='$data[provinceId]',
                                    cityId='$data[cityId]',
                                    districtId='$data[districtId]',
                                    address='$data[address]',
                                    industryId='$data[industryId]',
                                    status='$status',
                                    updateTime='$time'";
            $sql .=" where businessId=$data[businessId]";
        }else{
            $sql = "update business set userName='$data[userName]',
                                    contact='$data[contact]',
                                    mobile='$data[mobile]',
                                    tel='$data[tel]',
                                    title='$data[title]',
                                    provinceId='$data[provinceId]',
                                    cityId='$data[cityId]',
                                    districtId='$data[districtId]',
                                    address='$data[address]',
                                    industryId='$data[industryId]',
                                    status='$status',
                                    updateTime='$time'";
            $sql .=" where businessId=$data[businessId]";
        }
        
        $result = $this->dbsso->query($sql);
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
			 		"value":"title",
			  		"label":"商家名称",
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
			 		"value":"industryId",
			  		"label":"所属行业",
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
			 		"value":"contact",
			  		"label":"联系人",
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
	 					},
						{
	 						"name":"number",
	 						"message":"%s%必须为数字"
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
		  					"value":"/^[A-Za-z0-9_\\\\-]{3,30}$/",
			  				"message":"%s%应为3到30位的字母、数字、字符和下划线"
	  					},
 						{
		  					"name":"remote",
		  					"value":"checkField",
		  					"message":"此账号已经存在"		
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
		  		}
                                
                                ]}';

        return $rules;
    }

}
