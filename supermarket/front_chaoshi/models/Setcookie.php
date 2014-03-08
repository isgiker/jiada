<?php
/**
 * @name SetcookieModel
 * @desc cookie跨域登录
 * @author Vic Shiwei
 */
class SetcookieModel extends BasicModel{

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
    public function getLoginCookie($data){
        $query = "select a.userId,a.loginTime,a.keyValue from user_login_cookie a where a.userId = '$data[0]' and a.loginTime = '$data[1]'";
        $this->ssodb->setQuery($query);
        $rows = $this->ssodb->loadAssoc();
        return $rows;
    }
    
}
