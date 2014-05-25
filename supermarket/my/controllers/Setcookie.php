<?php

/**
 * @name IndexController
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class SetcookieController extends Core_Controller_Www {
    
    protected $model;

    public function init() {
        parent::init();
        $this->model = new SetcookieModel();
    }
    
    public function indexAction() {
        header('P3P: CP="CAO DSP COR CUR ADM DEV TAI PSA PSD IVAi IVDi CONi TELo OTPi OUR DELi SAMi OTRi UNRi PUBi IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE GOV"');

        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->_layout = false;
        //获取参数
        $q=$this->getQuery('q');
        $q_arr=explode('|', $q);
        if(!trim($q_arr[0]) || !trim($q_arr[1])){
            return false;
        }
        $userId=$q_arr[0];
        $loginTime=$q_arr[1];
        
        //获取cookie
        $cookies=$this->model->getLoginCookie($q_arr);
        if ($cookies['keyValue']) {
            $c_arr = explode(',', $cookies['keyValue']);
            $c_arr = array_filter($c_arr);
            if ($c_arr) {
                foreach ($c_arr as $v) {
                    $item = explode('=>', $v);
                    if(isset($item[0]) && isset($item[1]) && $item[0] && $item[1]){
                        $this->setCookies($item[0], $item[1]);
                    }
                }
            }
        }
        
        return false;
    }


}
