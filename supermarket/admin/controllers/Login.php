<?php

/**
 * @name LoginController
 * @desc 登录控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class LoginController extends Core_Controller_Admin {

    protected $model;

    public function init() {
        parent::init();
        $this->model = new LoginModel();
    }

    public function indexAction() {
        $this->_layout = false;
        $rules = $this->model->getRules();
        $post = $this->getPost();
        if ($this->isPost()) {
            $v = new validation(); //数据校验
            $v->validate($rules, $post);
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
                $this->getView()->assign("post", $post);
                if ($this->isAjax()) {
                    $this->err('', $v->error_message); //输出异步错误信息
                }
            } else {
                $errMsg = $this->login($post);
                $this->getView()->assign("errorMsg", $errMsg);
            }
        }
        if(isset($_COOKIE['uname']))
            $post['username']=$_COOKIE['uname'];

        $this->getView()->assign('post', $post);
    }
    
    private function login($data){
        if (isset($data['password']) && $data['password']) {
            $password=strrev(sha1($data['password']));
            $data['password']=md5($password);
        }else{
            $error = '密码错误，不能为空！';
            return $error;
        }
        //检查用户(管理员表)是否存在,如果存在返回用户信息;
        $userInfo = $this->model->getUserInfo($data);
        if (!$userInfo) {
            $error = '用户名或密码错误！';
            return $error;
        } elseif (isset($userInfo['status']) && $userInfo['status'] == -1) {
            $error = '帐号已禁用！';
            return $error;
        }

        $ticket = $this->model->setTicket($userInfo['adminId']);
        $cR1=$this->setCookies('uid', $userInfo['adminId']);
        //方便用户再次登录时不用输入用户名
        $cR2=$this->setCookies('uname', $data['username']);
        $cR3=$this->setCookies('_TICKET', $ticket);
        
        
        //这块可以考虑使用通用des/3des加密
        $userInfo = base64_encode(serialize($userInfo));
        $cR4=$this->setCookies('_USERINFO', $userInfo);
        if($cR1 && $cR2 && $cR3 && $cR4){
            $this->redirect('/Index/index');
        }else{
            $error = 'Cookie写入失败！';
            return $error;
        }
    }
    
    
    
    public function logoutAction(){
        // Remove the cookie
        unset($_COOKIE['uid']);
        unset($_COOKIE['_TICKET']);
        unset($_COOKIE['_USERINFO']);
        // Nullify the cookie and make it expire
        $delCookie1 = $this->setCookies('_TICKET', '', -86400);
        $delCookie2 = $this->setCookies('_USERINFO', '', -86400);
        $delCookie3 = $this->setCookies('uid', '', -86400);
        if (!$delCookie1 || !$delCookie2 || !$delCookie3) {
            $this->jsLocation('cookie写入失败！', $url);
        }
        $this->redirect('/Login');
    }

}
