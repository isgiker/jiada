<?php
/**
 * @name LoginModel
 * @desc 登录
 * @author Vic Shiwei
 */
class LoginModel extends BasicModel{

    const cryptKey= '~!@#*w.(KLH)^F/,W6[jIi]-%kXz+K_w3%+=';
    
    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
        $this->ssodb = Factory::getDBO('local_jiada_sso');
    }
    
    /**
     * 根据用户名密码获取用户信息，为了安全用户密码和联系信息不能记录在cookie
     * @param array $data
     */
    public function getUserInfo($data){
        $query = "select a.userId,a.areaId,a.userName,a.status,a.avatar from user a where a.password = ? and a.email = ?";
        $sth = $this->ssodb->prepare($query);
        if($sth != FALSE){
            if($sth->execute(array($data['password'], $data['username']))){
                $rows = $sth->fetch();
                return $rows;
            }
        }
        return false;
    }
    
    public function writeLoginLog($data){
        if(!$data['userId'] || !$data['loginTime'] || !$data['keyValue'] ){
            return false;
        }
        $sql = "replace into user_login_log set 
                                    `userId`='$data[userId]',
                                    `loginTime`='$data[loginTime]',
                                    `keyValue`='$data[keyValue]'
                ";
        $result = $this->ssodb->query($sql);
        if ($result == false) {
            $error = $this->ssodb->ErrorMsg();
            return FALSE;
        }
        return true;
    }
    
    /**
     * 获取地区信息
     * @param int $parentId 分类的父级节点id
     * @return array
     */
    public function getAreaInfo($areaId){
        if(!$areaId) return false;
        $query = "select a.areaId,a.areaName,a.parentPath,a.lng,a.lat from area a where a.areaId=$areaId";
        $this->db->setQuery($query);
        $rows = $this->db->loadAssoc();       
        return $rows;
    }
    
    /*
     * 构建客户端唯一ID,并进行加密;
     * @param int $userId 用户id
     */
    public function setTicket($ticketParam) {
        $ticket = $this->buildTicket($ticketParam);
        if(!$ticket){
            return false;
        }
        $cryptKey = strrev(md5(self::cryptKey));
        $ticket = strrev(sha1($ticket)).$cryptKey;
        $ticket = sha1($ticket);
        return $ticket;
    }

    /**
     * 对字符串进行前面,和密码加密的方式一样
     * @param type $string
     */
    public function getSign($string){
        $cryptKey = strrev(md5(self::cryptKey));
        $string = strrev(sha1($string)).$cryptKey;
        $strSign = sha1($string);
        return $strSign;
    }

    /**
     * 构建原始票据结构:用户id|登录时间|浏览器代理信息|用户ip地址;
     * @param int $userId 用户id
     */
    public function buildTicket($ticketParam) {
        if (!$ticketParam['uid'] || !$ticketParam['lt'])
            return false;

        $IP = Util::getIP();
        $ticket = $ticketParam['uid'].'|'.$ticketParam['lt'].'|'.$_SERVER['HTTP_USER_AGENT'].'|'.$IP;
        return $ticket;
    }

    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[
                                {
			 		"value":"username",
			  		"label":"账号",
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
