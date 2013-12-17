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
    
    /**
     * 从客户端返回变量，这个方法将从请求参数中寻找参数name，如果没有找到的话，将从POST, GET, Cookie, Server中寻找
     * @param type $key the variable name
     * @param type $value 如果提供了此参数，当变量在未被找到的情况下，它将被返回
     * @param type $type int|float|word|string|none
     * @return string
     */
    public function get($key, $value, $type='none') {
        $var = $this->getRequest()->get($key, $value);
        if (!$var) {
            return $value;
        }
       
        $var = Core_Filter::_cleanVar($var, 0, $type);
        $var = Core_Filter::_addslashes($var);
            
        return $var;
    }
    
    /**
     * Fetch a query parameter,Retrieve GET variable
     * @param type $key the variable name
     * @param type $value if this parameter is provide, this will be returned if the variable can not be found
     * @param type $type int|float|word|string|none
     * @return string
     */
    public function getQuery($key, $value, $type='none') {
        $var = $this->getRequest()->getQuery($key, $value);
        if (!$var) {
            return $value;
        }
       
        $var = Core_Filter::_cleanVar($var, 0, $type);
        $var = Core_Filter::_addslashes($var);
            
        return $var;
    }

    /**
     * 获取当前请求中的路由参数, 路由参数不是指$_GET或者$_POST, 而是在路由过程中, 路由协议根据Request Uri分析出的请求参数.
     * @param type $key
     * @param type $value
     * @return string
     * @example 路由如下请求URL: http://www.domain.com/module/controller/action/name1/value1/name2/value2/ 路由结束后将会得到俩个路由参数, name1和name2, 值分别是value1, value2.
     */
    public function getParam($key, $value, $type='none') {
        $var = $this->getRequest()->getParam($key, $value);
        if (!$var) {
            return $value;
        }
        
        $var = Core_Filter::_cleanVar($var, 0, $type);
        $var = Core_Filter::_addslashes($var);
            
        return $var;
    }
    
    /**
     * Retrieve POST variable
     * @param type $key the variable name
     * @param type $value if this parameter is provide, this will be returned if the varialbe can not be found
     * @param type $type int|float|word|string|none
     * @return string
     */
    public function getPost($key, $value, $type='none') {
        $var = $this->getRequest()->getPost($key, $value);
        if (!$var) {
            return $value;
        }
       
        $var = Core_Filter::_cleanVar($var, 0, $type);
        $var = Core_Filter::_addslashes($var);
            
        return $var;
    }

    function getVar($name, $default = null, $hash = 'default', $type = 'none', $mask = 0) {
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
            $var = Core_Filter::_cleanVar($input[$name], $mask, $type);
            $var = Core_Filter::_addslashes($var);
            
        } else if ($default) {
            $var = $input[$name] = $default;
            
        }

        return $var;
    }

    /**
     * Fetches and returns a given filtered variable. The integer
     * filter will allow only digits to be returned. This is currently
     * only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param	string	$name		Variable name
     * @param	string	$default	Default value if the variable does not exist
     * @param	string	$hash		Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @return	integer	Requested variable
     */
    function getInt($name, $default = 0, $hash = 'default') {
        return $this->getVar($name, $default, $hash, 'int');
    }

    /**
     * Fetches and returns a given filtered variable.  The float
     * filter only allows digits and periods.  This is currently
     * only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param	string	$name		Variable name
     * @param	string	$default	Default value if the variable does not exist
     * @param	string	$hash		Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @return	float	Requested variable
     * @since	1.5
     */
    function getFloat($name, $default = 0.0, $hash = 'default') {
        return $this->getVar($name, $default, $hash, 'float');
    }
    
    /**
     * Fetches and returns a given filtered variable. The bool
     * filter will only return true/false bool values. This is
     * currently only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param	string	$name		Variable name
     * @param	string	$default	Default value if the variable does not exist
     * @param	string	$hash		Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @return	bool		Requested variable
     */
    function getBool($name, $default = false, $hash = 'default') {
        return $this->getVar($name, $default, $hash, 'bool');
    }

    /**
     * Fetches and returns a given filtered variable. The word
     * filter only allows the characters [A-Za-z_]. This is currently
     * only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param	string	$name		Variable name
     * @param	string	$default	Default value if the variable does not exist
     * @param	string	$hash		Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @return	string	Requested variable
     */
    function getWord($name, $default = '', $hash = 'default') {
        return $this->getVar($name, $default, $hash, 'word');
    }

    /**
     * Fetches and returns a given filtered variable. The cmd
     * filter only allows the characters [A-Za-z0-9.-_]. This is
     * currently only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param	string	$name		Variable name
     * @param	string	$default	Default value if the variable does not exist
     * @param	string	$hash		Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @return	string	Requested variable
     */
    function getCmd($name, $default = '', $hash = 'default') {
        return $this->getVar($name, $default, $hash, 'cmd');
    }

    /**
     * Fetches and returns a given filtered variable. The string
     * filter deletes 'bad' HTML code, if not overridden by the mask.
     * This is currently only a proxy function for getVar().
     *
     * See getVar() for more in-depth documentation on the parameters.
     *
     * @static
     * @param	string	$name		Variable name
     * @param	string	$default	Default value if the variable does not exist
     * @param	string	$hash		Where the var should come from (POST, GET, FILES, COOKIE, METHOD)
     * @param	int		$mask		Filter mask for the variable
     * @return	string	Requested variable
     */
    function getString($name, $default = '', $hash = 'default', $mask = 0) {
        // Cast to string, in case JREQUEST_ALLOWRAW was specified for mask
        return (string) $this->getVar($name, $default, $hash, 'string', $mask);
    }

    public function _setcookie($name, $val = NULL, $time = NULL, $path = NULL, $domain = NULL) {
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
