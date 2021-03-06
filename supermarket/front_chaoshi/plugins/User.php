<?php

/**
 * @name UserPlugin
 * @desc 检验登录状态
 * @see http://www.php.net/manual/en/class.yaf-plugin-abstract.php
 * @author Vic Shiwei
 */
class UserPlugin extends Yaf_Plugin_Abstract {
    
    const cryptKey= '~!@#*w.(KLH)^F/,W6[jIi]-%kXz+K_w3%+=';

    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        if (!isset($_COOKIE['user-key']) || !$_COOKIE['user-key']) {
            //生成user-key cookie
            $time = uniqid(getmypid(), true);
            $IP = Util::getIP();
            //时间|浏览器代理信息|用户ip地址
            $userKey = sha1($time . $_SERVER['HTTP_USER_AGENT'] . $IP);
            
            $expire=time()+(3600*24*100);
            setcookie('user-key', $userKey, $expire, '/', null, null, true);
        }
    }

    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        
    }

    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        
    }

    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
//        $_config=Yaf_Registry::get('_CONFIG');
//        
//        if (!$this->isLogin()) {
//            //Index模块里的控制器不受登录限制
//            if($request->module=='Index' && $request->controller=='Setcookie'){
//     
//            }else{
//                $currentUrl='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
//                header("Location:http://".$_config->domain->www."/Login?ref=$currentUrl");
//                exit;
//            }
//        }
    }

    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        
    }

    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {        

    }

    public function isLogin() {
        if (isset($_COOKIE['uid']) && isset($_COOKIE['lt']) && isset($_COOKIE['_TICKET']) && isset($_COOKIE['_USERINFO'])  && isset($_COOKIE['_UIS']) 
                && $_COOKIE['uid'] && $_COOKIE['lt'] && $_COOKIE['_TICKET'] && $_COOKIE['_USERINFO'] && $_COOKIE['_UIS']) {
            
            //票据结构:用户id|浏览器代理信息|用户ip地址|行业拼音|用户socket端口号;
            $ticketParam=array(
                'uid'=>@$_COOKIE['uid'],
                'lt'=>@$_COOKIE['lt']
            );
            $cookieTicket = $_COOKIE['_TICKET'];
            $cookieUserInfoSign = $_COOKIE['_UIS'];

            //服务端生成ticket
            $ticket = $this->setTicket($ticketParam);
            $uis = $this->getSign($_COOKIE['_USERINFO']);

            //服务端和客户端ticket比较
            if ($ticket === $cookieTicket && $uis==$cookieUserInfoSign) {
                return true;
            } else {
                return false;
            }
        }

        return false;
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
    
}
