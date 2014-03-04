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
        header('P3P: CP="CAO DSP COR CUR ADM DEV TAI PSA PSD IVAi IVDi CONi TELo OTPi OUR DELi SAMi OTRi UNRi PUBi IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE GOV"');

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

        //登录时间
        $loginTime=microtime(true);
        $cR3=$this->setCookies('lt', $loginTime);
           
        //认证票据
        $ticketParam=array(
            'uid'=>$userInfo['userId'],
            'lt'=>$loginTime
        );
        $ticket = $this->model->setTicket($ticketParam);
        $cR4=$this->setCookies('_TICKET', $ticket); 
        
        //这块可以考虑使用通用des/3des加密
        $userInfo_encrypt = base64_encode(serialize($userInfo));
        $cR5=$this->setCookies('_USERINFO', $userInfo_encrypt);
        
        //对账号信息进行签名
        $uis=$this->model->getSign($userInfo_encrypt);
        $cR6=$this->setCookies('_UIS', $uis);
        
        //记录用户登录日志，日志包含登录cookie
        $logParam=array(
            'userId'=>$userInfo['userId'],
            'loginTime'=>$loginTime,
            'keyValue'=>'uname:'.$data['username'].','.'uid:'.$userInfo['userId'].','.'lt:'.$loginTime.','.'_USERINFO:'.$userInfo_encrypt.','.'_UIS:'.$uis.','.'_TICKET:'.$ticket
        );
        //跨域时根据用户id和登录时间获取cookie
        $writeR=$this->model->writeLoginLog($logParam);
        if($writeR){
            $this->corssDomain(array(
                'userId' => $userInfo['userId'],
                'loginTime' => $loginTime));
        }

        if($cR1 && $cR2 && $cR3 && $cR4 && $cR5 && $cR6){
            //目前暂跳转到超市首页，日后会跳转到小区主页
            $this->redirect('//'.$this->_config->domain->www.'/Chaoshi/Index/index');
        }else{
            $error = 'Cookie写入失败！';
            return $error;
        }
    }
    
    /*
     * 跨域设置cookie
     * 每一个APP下面都有一个setCookie.php页面
     */
    private function corssDomain($data) {
        $front_domains = array('www' => $this->_config->domain->www, 'chaoshi' => $this->_config->domain->chaoshi);
        $q=$data['userId'].'|'.$data['loginTime'];
        $html = '';
        foreach ($front_domains as $key => $host) {
            $html .='<script type="text/javascript" src="http://' . $host . '/Index/setCookie?q='.$q.'"></script>' . "\n";
        }
        echo $html;
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
