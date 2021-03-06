<?php

/**
 * @name UserPlugin
 * @desc 检验登录状态
 * @see http://www.php.net/manual/en/class.yaf-plugin-abstract.php
 * @author Vic Shiwei
 */
class UserPlugin extends Yaf_Plugin_Abstract {

    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        
    }

    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        
    }

    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        
    }

    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        if (!$this->isLogin()) {
            //Index模块里的控制器不受登录限制
            if($request->module=='Index'){
     
            }else{
                header("Location:/Login");
                exit;
            }
        }
    }

    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        
    }

    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {        

    }

    public function isLogin() {
        if (
                isset($_COOKIE['businessId']) && isset($_COOKIE['_TICKET']) && isset($_COOKIE['industry_modules']) && isset($_COOKIE['_USERINFO'])  && isset($_COOKIE['_UIS']) 
                && trim($_COOKIE['businessId']) && trim($_COOKIE['_TICKET']) && trim($_COOKIE['industry_modules']) && trim($_COOKIE['_USERINFO']) && trim($_COOKIE['_UIS'])
           ) {
            //票据结构:商家id|店铺id|用户id|acl权限|浏览器代理信息|用户ip地址|行业拼音|用户socket端口号;
            $ticketParam=array(
                'businessId'=>@$_COOKIE['businessId'],
                'shopId'=>@$_COOKIE['shopId'],
                'uid'=>@$_COOKIE['uid'],
                'acl'=>@$_COOKIE['acl'],
                'industry_modules'=>@$_COOKIE['industry_modules']
            );
            $cookieTicket = $_COOKIE['_TICKET'];
            $cookieUserInfoSign = $_COOKIE['_UIS'];
                        
            //服务端生成ticket
            $loginModel = new LoginModel();
            $ticket = $loginModel->setTicket($ticketParam);
            $uis = $loginModel->getSign($_COOKIE['_USERINFO']);
            
            //服务端和客户端ticket比较
            if ($ticket === $cookieTicket && $uis==$cookieUserInfoSign) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
    
}
