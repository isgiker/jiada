<?php
/**
 * @name RegModel
 * @desc 注册
 * @author Vic Shiwei
 */
class RegModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
        $this->ssodb = Factory::getDBO('local_jiada_sso');
    }
    
    /**
     * 检测邮箱是否唯一
     * @param boole or int
     */
    public function checkEmail($email){
        if(!trim($email)){
            return false;
        }
        $query = "select count(a.userId) from user a where a.email = '$email' limit 0,1";
        $this->ssodb->setQuery($query);
        $num = $this->ssodb->loadResult();
        return $num;
    }
    
    /**
     * 检测用户名是否唯一
     * @param boole or int
     */
    public function checkUserName($userName){
        if(!trim($userName)){
            return false;
        }
        $query = "select count(a.userId) from user a where a.userName = '$userName' limit 0,1";
        $this->ssodb->setQuery($query);
        $num = $this->ssodb->loadResult();
        return $num;
    }
    
    public function uuid_short(){
       $sql = "select uuid_short();";
       $this->ssodb->setQuery($sql);
       $uuid_short = $this->ssodb->loadResult();
       return $uuid_short;
    }
    
    //用户注册
    public function reg($data) {
        $time=time();
        $userId = $this->uuid_short();
        if (isset($data['passwd']) && $data['passwd']) {
            $password=strrev(sha1($data['passwd']));
            $password=md5($password);
        } else {
            return false;
        }
        $sql = "insert user set 
                                    `userId`='$userId',
                                    `areaId`='$data[community]',
                                    `userName`='$data[nickname]',
                                    `password`='$password',
                                    `email`='$data[email]',
                                    `mobile`='',
                                    `emailValidate`='0',
                                    `mobileValidate`='0',
                                    `status`='1',
                                    `registerTime`='$time'";
        $result = $this->ssodb->query($sql);
        if ($result == false) {
            $error = $db->ErrorMsg();
            die("$error");
        }
        return true;
    }
    
    //添加小区
    public function addcommunity($data) {
        if(!$data['pid']){
            return false;
        }
        
        $parentPath = $this->getParentPath($data['pid']);
        
        $sql = "insert area set areaName='$data[location]',pinyin='',parentId='$data[pid]',parentPath='$parentPath',domain='',sort='0',areaType='Community',public='1'";
        $result = $this->db->query($sql);
        
        if ($result == false) {
            return false;
        }
        $areaId=$this->db->insertid();
        
        return $this->getAreaInfo($areaId);
    }
    
    private function getParentPath($parentId){
        $query = "select parentId from area where areaId = $parentId";
        $this->db->setQuery($query);
        $newParentId = $this->db->loadResult();

        static $parentPath = array();
        if($newParentId!=0){
            $this->getParentPath($newParentId);
            $parentPath[] = $newParentId;
        }

        if(!empty($parentPath)){
            $parentPathStr = implode(',', $parentPath);
            $result = $parentPathStr.','.$parentId;
        }else{
            $result = $parentId;
        }
        return $result;
    }
    
    private function getAreaInfo($areaId){
        $query = "select areaId,areaName,pinyin from area where areaId=$areaId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();
        return $rows;
    }

    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[
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
                                        "value":"community",
                                                "label":"居住小区",
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
					"value":"email",
					"label":"邮箱地址",
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
	 						"name":"email",
	 						"message":"%s%格式不正确"
	 					}
		  			]
				},
                                {
			 		"value":"nickname",
			  		"label":"昵称",
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
 						"value":"[3,16]",
 						"message":"%s%长度为3到16位"
 						}
		  			]
		  		},
                                {
			 		"value":"passwd",
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
					"value":"repasswd",
					"label":"确认密码",
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
	 						"name":"equalTo",
	 						"value":"#passwd",
	 						"message":"两次输入密码不一致"
	 					}
		  			]
				}
                                ]}';

        return $rules;
    }
}
