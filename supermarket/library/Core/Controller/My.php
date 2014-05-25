<?php

class Core_Controller_My extends Core_Controller_Basic {
    
    const cryptKey= '~!@#*w.(KLH)^F/,W6[jIi]-%kXz+K_w3%+=';

    //显示该店铺（仓库）的数据;
    public $shopId;
    
    //需要登录的页面设置true
    public $mustLogin=false;
    
    public function init() {
        parent::init();
        if($this->mustLogin===true){
            if (!$this->isLogin()) {
                //Index模块里的控制器不受登录限制
                if($this->_ModuleName=='Index' && $this->_ControllerName=='Setcookie'){

                }else{
                    $currentUrl='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    $currentUrl=urlencode($currentUrl);
                    header("Location:http://".$this->_config->domain->www."/Login?ref=$currentUrl");
                    exit;
                }
            }
        }
        
    }
    
    /**
     * 判断是否登录
     * @return boolean
     */
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
    
    /**
     * 错误页信息
     * @param type $msg
     * @param string $action
     */
    public function showError($msg, $action = null) {
        $this->_layout = true;
        $this->setViewPath(APPLICATION_PATH . DS . 'views');
        $action = 'error' . DS . 'index.phtml';
        $layoutFile = 'layout' . DS . 'layout.phtml';
        $tplVars=['error' => $msg];
        if ($this->_layout == true) {
            $tplVars['_ActionContent'] = $this->getView()->render($action, $tplVars);
            echo $this->getView()->render($layoutFile, $tplVars);
        } else {
            echo $this->getView()->render($action, $tplVars);
        }
        exit;
    }
    
    /**
     * 跨域cookie设置
     * 如果没有指定cookie域默认把一个cookie写入多个站点(经测试不行)
     * @param type $name
     * @param type $val
     * @param type $time
     */
//    public function setCookies($name, $val = '', $time = 0, $path = '/', $domain = '', $secure = FALSE, $httponly = FALSE) {
//        header('P3P: CP="CAO DSP COR CUR ADM DEV TAI PSA PSD IVAi IVDi CONi TELo OTPi OUR DELi SAMi OTRi UNRi PUBi IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE GOV"');
//        $front_domains = array('www' => $this->_config->domain->www, 'chaoshi' => $this->_config->domain->chaoshi);
//
//        if (trim($name)) {
//            $r = 0;
//            if ($domain) {
//                return setcookie($name, $val, $time, $path, $domain, $secure, $httponly);
//            } else {
//                foreach ($front_domains as $key => $host) {
//                    if (setcookie($name, $val, $time, $path, $host, $secure, $httponly)) {
//                        $r++;
//                    }
//                }
//            }
//
//            if ($r == count($front_domains)) {
//                return true;
//            } else {
//                return false;
//            }
//        } else {
//            return false;
//        }
//    }
}
