<?php

class Core_Controller_Www extends Core_Controller_Basic {

    public function init() {
        parent::init();
        
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
