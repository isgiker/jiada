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
        $this->db = Factory::getDBO('local_jiada_sso');
    }
    
    /**
     * 根据用户名密码获取用户信息
     * @param array $data
     */
    public function getUserInfo($data){
        $query = "select a.adminId,a.realName,a.agroupId,a.status,b.groupName,b.acl from admin a, admin_group b where a.password = ? and a.userName = ? and a.agroupId=b.agroupId";
        $sth = $this->db->prepare($query);
        if($sth != FALSE){
            if($sth->execute(array($data['password'], $data['username']))){
                $rows = $sth->fetch();
                return $rows;
            }
        }
        return false;
    }
    
    /*
     * 构建客户端唯一ID,并进行加密;
     * @param int $userId 管理员id
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
     * 构建原始票据结构:用户id|acl权限|浏览器代理信息|用户ip地址|用户socket端口号;
     * @param int $userId 管理员id
     * @param string $industryPinyin 行业拼音
     */
    public function buildTicket($ticketParam) {
        if (!$ticketParam['uid'] || !$ticketParam['acl'])
            return false;

        $IP = Util::getIP();
        //由于端口号隔一段时间就会变动所以不能使用
//        $ticket = $userId.'|'.$_SERVER['HTTP_USER_AGENT'].'|'.$IP.'|' . $_SERVER['REMOTE_PORT'];
        $ticket = $ticketParam['uid'].'|'.$ticketParam['acl'].'|'.$_SERVER['HTTP_USER_AGENT'].'|'.$IP;
        return $ticket;
    }

    /**
     * form 验证规则
     */
    public function getRules() {
        $rules = '{"validation":[
                                {
			 		"value":"username",
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
		  		}
                                ]}';

        return $rules;
    }
}
