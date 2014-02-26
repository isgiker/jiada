<?php

/**
 * @name LoginController
 * @desc 登录控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class LoginController extends Core_Controller_Business {

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
        //检查商家用户是否存在,如果存在返回用户信息;
        $userInfo = $this->model->getBusinessInfo($data);
        if(!$userInfo){
            //检查店铺用户是否存在,如果存在返回用户信息;
            $userInfo = $this->model->getShopAdminInfo($data);
        }
        if (!$userInfo) {
            $error = '用户名或密码错误！';
            return $error;
        } elseif (isset($userInfo['status']) && $userInfo['status'] == -1) {
            $error = '帐号已禁用！';
            return $error;
        }
        
        //获取商家所属行业的拼音
        $industryInfo = $this->model->getIndustryInfo($userInfo['industryId']);
        if(!$industryInfo){
            $error = '所属行业不存在！';
            return $error;
        }elseif (!$industryInfo['pinyin']) {
            $error = '所属行业数据有误！';
            return $error;
        }
        //行业
        $industryPinyin=ucfirst($industryInfo['pinyin']);
        
        //记录商家id,店铺id,用户id,管理员权限;商家账号没有所属店铺，只有店铺管理员账号有所属店铺id。
        if(isset($userInfo['shopId']) && $userInfo['shopId']){
            $shopId=$userInfo['shopId'];
        }else{
            $shopId='';
        }
        
        //adminId是店铺里的管理员id；而businessId是一定存在的。cookie里uid其实没有必要记录，只是为了将来可能有需求用到，扩展用。
        if(isset($userInfo['adminId']) && $userInfo['adminId']){
            $uid=$userInfo['adminId'];
            //获取用户权限
            $acl=base64_encode($userInfo['acl']);
        }else{
            $uid=$userInfo['businessId'];
            //商家账号没有权限
            $acl='';
        }
        
        $cR1=$this->setCookies('businessId', $userInfo['businessId'],0,"/$industryPinyin");
        $cR2=$this->setCookies('shopId', $shopId,0,"/$industryPinyin");
        $cR3=$this->setCookies('uid', $uid,0,"/$industryPinyin");
        $cR4=$this->setCookies('acl', $acl,0,"/$industryPinyin");
        
        //方便用户再次登录时不用输入用户名
        $cR5=$this->setCookies('uname', $data['username'],0,"/$industryPinyin");
        
        //认证票据
        $ticketParam=array(
            'businessId'=>$userInfo['businessId'],
            'shopId'=>$shopId,
            'uid'=>$uid,
            'acl'=>$acl,
            'industry_modules'=>$industryPinyin
        );
        $ticket = $this->model->setTicket($ticketParam);
        $cR6=$this->setCookies('_TICKET', $ticket,0,"/$industryPinyin");
        
        //这块可以考虑使用通用des/3des加密
        $userInfo = base64_encode(serialize($userInfo));
        $cR7=$this->setCookies('_USERINFO', $userInfo,0,"/$industryPinyin");
        //对账号信息进行签名
        $uis=$this->model->getSign($userInfo);
        $cR8=$this->setCookies('_UIS', $uis,0,"/$industryPinyin");
        
        //根据这个cookie和cookie作用域正确显示各行业的左侧导航和账号权限范围；
        $cR9=$this->setCookies('industry_modules', $industryPinyin,0,"/$industryPinyin");

        if($cR1 && $cR2 && $cR3 && $cR4 && $cR5 && $cR6 && $cR7 && $cR8 && $cR9){
            if($shopId){
                //商家店铺首页
                $this->redirect("/Chaoshi/Shop/index/shopId/$shopId");
            }else{
                //商家首页
                $this->redirect("/$industryPinyin/Index/index");
            }
            
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
        $delCookie3 = $this->setCookies('businessId', '', -86400);
        $delCookie4 = $this->setCookies('shopId', '', -86400);
        $delCookie5 = $this->setCookies('uid', '', -86400);
        $delCookie6 = $this->setCookies('acl', '', -86400);
        $delCookie7 = $this->setCookies('industry_modules', '', -86400);
        $delCookie8 = $this->setCookies('_UIS', '', -86400);
        if (!$delCookie1 || !$delCookie2 || !$delCookie3 || !$delCookie4 || !$delCookie5 || !$delCookie6 || !$delCookie7) {
            $this->jsLocation('cookie写入失败！', $url);
        }
        $this->redirect('/Login');
    }

}
