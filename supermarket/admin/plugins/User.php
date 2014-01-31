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
        
    }

    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        
    }

    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        if (!$this->isLogin()) {            
            if($request->module=='Index' && $request->controller == 'Login'){
     
            }else{
                header("Location:/Login");
                exit;
            }
        }
    }

    public function isLogin() {
        if (isset($_COOKIE['uid']) && isset($_COOKIE['_TICKET']) && $_COOKIE['uid'] && $_COOKIE['_TICKET']) {
            $userId = $_COOKIE['uid'];
            $cookieTicket = $_COOKIE['_TICKET'];

            //服务端生成ticket
            $loginModel = new LoginModel();
            $ticket = $loginModel->setTicket($userId);

            //服务端和客户端ticket比较
            if ($ticket === $cookieTicket) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

}
