<?php

/**
 * @name LoginController
 * @desc 登录控制器
 * @author Vic shiwei
 */
class LoginController extends Core_Controller_Www {

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
        if (!$userInfo['userId']) {
            $error = '用户名或密码错误！';
            return $error;
        } elseif ($userInfo['status'] == -1) {
            $error = '帐号已注销！';
            return $error;
        } elseif ($userInfo['status'] == -2) {
            $error = '帐号被锁定！';
            return $error;
        } elseif ($userInfo['status'] == -9) {
            $error = '帐号已删除！';
            return $error;
        }
        
        //关联用户区域信息
        $areaInfo=$this->model->getAreaInfo($userInfo['areaId']);

        //方便用户再次登录时不用输入用户名
        $cR1=$this->setCookies('uname', $data['username']);
        $cR2=$this->setCookies('uid', $userInfo['userId']);


        //认证票据
        $ticketParam=array(
            'uid'=>$userInfo['userId']
        );
        $ticket = $this->model->setTicket($ticketParam);
        $cR3=$this->setCookies('_TICKET', $ticket);    
        
        //这块可以考虑使用通用des/3des加密
        $userInfo = base64_encode(serialize($userInfo));
        $cR4=$this->setCookies('_USERINFO', $userInfo);
        
        //对账号信息进行签名
        $uis=$this->model->getSign($userInfo);
        $cR5=$this->setCookies('_UIS', $uis);
        
        if($cR1 && $cR2 && $cR3 && $cR4 && $cR5){
            //目前暂跳转到超市首页，日后会跳转到小区主页
            $this->redirect('//'.$this->_config->domain->www.'/Chaoshi/Index/index');
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
        $delCookie4 = $this->setCookies('_UIS', '', -86400);

        $this->redirect('/Login');
    }

}
