<?php

class Core_Basic_Controllers extends Yaf_Controller_Abstract {

    public $_layout = false;
    protected $_layoutVars = array();

    /**
     * 加载Layout模板
     */
    public function render($action, array $tplVars = NULL) {
        if ($this->_layout == true) {
            $this->_layoutVars['_ActionContent'] = parent::render($action, $tplVars);
            return parent::render('../layout/layout', $this->_layoutVars);
        } else {
            return parent::render($action, $tplVars);
        }
    }

    /**
     * 监测是否异步请求
     */
    public function isAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && "XMLHttpRequest" === $_SERVER['HTTP_X_REQUESTED_WITH']) {

            return true;
        } else {

            return false;
        }
    }

    /**
     * 监测是否post
     */
    public function isPost() {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            return true;
        } else {

            return false;
        }
    }

    /**
     * 监测是否get
     */
    public function isGet() {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {

            return true;
        } else {

            return false;
        }
    }

    public function err($code = "", $msg = "") {
        $strResult = json_encode(array(
            'result' => 'err',
            'code' => $code,
            'msg' => $msg
        ));
        $strCb = $this->getRequest()->getQuery('cb');
        if (!empty($strCb)) {
            $strResult = $strCb . '(' . $strResult . ');';
        }
        header('Content-type: application/x-javascript;charset=UTF-8');
        echo $strResult;
        exit;
    }
    
    public function getParam ($key, $value){
        $var = $this->getRequest()->getParam($key, $value);
        if(!$var){
            return $value;
        }
        return $var;
    }
    
    function getVar($name, $default = null, $hash = 'default', $type = 'none', $mask = 0) {
        global $Route;
        // Ensure hash and type are uppercase
        $hash = strtoupper($hash);
        if ($hash === 'METHOD') {
            $hash = strtoupper($_SERVER['REQUEST_METHOD']);
        }
        $type = strtoupper($type);

        // Get the input hash
        switch ($hash) {
            case 'GET' :
                $input_1 = &$_GET;
                $input_2 = $this->getRequest()->getParams();
                $input = array_merge($input_1, $input_2);
                break;
            case 'POST' :
                $input = &$_POST;
                break;
            case 'FILES' :
                $input = &$_FILES;
                break;
            case 'COOKIE' :
                $input = &$_COOKIE;
                break;
            case 'ENV' :
                $input = &$_ENV;
                break;
            case 'SERVER' :
                $input = &$_SERVER;
                break;
            default:
                $get = array_merge($_GET, $this->getRequest()->getParams());
                $input = array_merge($get, $_REQUEST);
                $hash = 'REQUEST';
                break;
        }

        if (isset($input[$name]) && $input[$name] != null) {
            
            // Get the variable from the input hash and clean it
            $var = Core_Filter::_cleanVar($input[$name], $mask, $type);print_r(Core_Filter::_cleanVar('asdf', $mask, 'int'));exit;
            $var = Core_Filter::_addslashes($var);

        } else if ($default) {
            
            $var = $input[$name] = $default;            
        }

        return $var;
    }
    
    function getInt($name, $default = 0, $hash = 'default') {
        return $this->getVar($name, $default, $hash, 'int');
    }
    
    function getFloat($name, $default = 0.0, $hash = 'default') {
        return $this->getVar($name, $default, $hash, 'float');
    }
    
    function getString($name, $default = '', $hash = 'default', $mask = 0) {
        // Cast to string, in case JREQUEST_ALLOWRAW was specified for mask
        return (string) $this->getVar($name, $default, $hash, 'string', $mask);
    }
    
    

    public  function _setcookie($name, $val = NULL, $time = NULL, $path = NULL, $domain = NULL) {
        if ($name) {
            global $_setting;
            if ($time == null)
                $time = time() + 3600 * 24;
            if ($path == null)
                $path = $_setting['cookie_path'];
            if ($domain == null)
                $domain = $_setting['cookie_domain'];
            setcookie($name, $val, $time, $path, $domain, 0);
        }
    }

}
