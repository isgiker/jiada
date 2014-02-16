<?php

class Core_Controller_Basic extends Yaf_Controller_Abstract {
    protected $_config = null;
    public $_layout = false;
    protected $_layoutVars = array();
    public $_ModuleName = false;
    public $_ControllerName = false;
    public $_ActionName = false;
    
    public function init() {
        $this->_config = Yaf_Registry::get("_CONFIG");
        $this->_ModuleName=$this->getRequest()->getModuleName();
        $this->_ControllerName=$this->getRequest()->getControllerName();
        $this->_ActionName=$this->getRequest()->getActionName();
        $this->getView()->assign('_view', $this->getView());
        $this->getView()->assign('_ModuleName', $this->_ModuleName);
        $this->getView()->assign('_ControllerName', $this->_ControllerName);
        $this->getView()->assign('_ActionName', $this->_ActionName);
        
        $this->getView()->assign('_config_domain', $this->_config->domain);
    }
    
    public function __call($name, $arguments) {
        if(method_exists($this->getRequest()->getControllerName(), $name)){
            return $this->$name($arguments);
        }        
    }

    /**
     * 加载Layout模板
     * $_page=array(
     * 'title' => '家大.小区服务-商家后台管理系统',
     * 'meta_title' => '',
       'meta_description' => '',
       'meta_keywords' => '',            
       'static_css_files' => [
                ['css/admin/bootstrap.css'],
                ['css/admin/bootstrap-theme.css']
            ],
       'static_js_files' => [
                ['js/libs/jquery.min.js'],
                ['js/libs/bootstrap/bootstrap.min.js']
            ]
     * )
     */
    public function render($action, array $tplVars = NULL) {
        if ($this->_layout == true) {
//            $this->_layoutVars['_page']=$this->getView()->_page;
            $this->_layoutVars['_ActionContent'] = parent::render($action, $tplVars);
            //指定layout位置
            $this->setViewPath(APPLICATION_PATH . DS . 'views');
            $layoutFile = '../layout' . DS . 'layout';
            return parent::render($layoutFile, $this->_layoutVars);
            //当前modules下的layout
//            return parent::render('../layout/layout', $this->_layoutVars);
        } else {
            return parent::render($action, $tplVars);
        }
    }

    /**
     * 是否为Ajax请求
     * 这个方法取决于请求报头：HTTP_X_REQUESTED_WITH，一些Javascript库在做Ajax请求时候不设置这个报文头
     */
    public function isAjax() {
        return $this->getRequest()->isXmlHttpRequest();
    }

    /**
     * 监测是否post
     */
    public function isPost() {
       return $this->getRequest()->isPost();
    }

    /**
     * 监测是否get
     */
    public function isGet() {
        return $this->getRequest()->isGet();
    }
    
    /**
     * 返回
     */
    public function jsHistoryBack(){
        echo '<script>javascript:history.back(1);</script>';
    }
    
    /**
     * js跳转
     * @param type $url
     * @param type $msg
     * @param type $flag
     */
    public function jsLocation($msg, $url, $flag=true) {
        echo "<script>";
        if ($msg)
            echo "alert('{$msg}');";
        if ($url)
            echo "window.location.href='{$url}'";
        echo "</script>";
        if ($flag)
            exit;
    }

    /**
     * 输出请求成功的json数据
     * data：返回的数据对象
     * url:请求成功后需要跳转的地址
     */
    public function ok($data = '', $url = '', $msg = null) {
        $strResult = json_encode(array(
            'result' => 'ok',
            'code' => 200,
            'data' => $data,
            'msg' => $msg,
            'url' => $url
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
     * 输出请求失败的json数据
     * code：错误码
     * msg:错误信息
     */
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
     * 用于接口
     * @param type $msg
     * @param string $format array|json|xml
     * @return $format
     */
    public function errorMessage($msg = 'fail', $format='json') {
        $returnResult =array(
                'status' => false,
                'message' => $msg,
                'code' => -200
            );
        if($format=='json'){
            return json_encode($returnResult);
        }else{
            return $returnResult;
        }
    }

    /**
     * 返回结果函数
     * @param array $data
     * @param string $format array|json|xml
     * @return $format
     */
    public function returnData($data = null, $format='json') {
        $returnResult = array(
            'status' => true,
            'message' => 'success',
            'code' => 200
        );
        if ($data)
            $returnResult['data'] = $data;
        if($format=='json'){
            return json_encode($returnResult);
        }else{
            return $returnResult;
        }
        
    }
    
    /**
     * 通用,主要用于函数之间的返回信息传递；
     * @param bool $status 必要参数
     * @param string $msg 可选参数
     * @param array $data 可选参数
     * @param string $format array|json|xml
     * @return $format
     */
    public function returnResult($status, $msg=null, $format='array'){
        if(!is_bool($status)){
            die('返回结果状态参数错误！');
        }
        $status?$code=200:$code=-200;
        $data=array();
        if($status===false && !$msg){
            $msg='fail';
        }elseif($status===true && !$msg){
            $msg='success';
        }
        if($msg && is_array($msg)){
            $data=$msg;
            $status?$msg='success':$msg='fail';            
        }
        
        $returnResult = array(
            'status' => $status,
            'message' => $msg,
            'data' => $data,
            'code' => $code
        );
        
        if($format=='json'){
            return json_encode($returnResult);
        }else{
            return $returnResult;
        }
    }
    
    /**
     * 返回get、Query、post和cookie的所有数据
     */
    public function _getRequest(){
        return $this->getRequest()->getRequest();
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
    public function getParam($key, $value=null, $type='none') {
        $var = @$this->getRequest()->getParam($key, $value);
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
     * @param type $type int|float|word|string|none|array
     * @return array|string
     */
    public function getPost($key=null, $value=null, $type='none') {
        if(!$key){
            $arr = $this->getRequest()->getPost();
            $var = Core_Filter::_addslashes($arr);
            return $var;
        }
        $var = $this->getRequest()->getPost($key, $value);
        if (!$var) {
            return $value;
        }
       
        $var = Core_Filter::_cleanVar($var, 0, $type);
        $var = Core_Filter::_addslashes($var);
            
        return $var;
    }
    
    public function getFiles(){
        return $this->getRequest()->getFiles();
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

        if (isset($input["$name"]) && $input["$name"] != null) {
            // Get the variable from the input hash and clean it
            $var = Core_Filter::_cleanVar($input[$name], $mask, $type);
            $var = Core_Filter::_addslashes($var);
            
        } else if ($default) {
            $var = $input[$name] = $default;
            
        }

        return @$var;
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

    /**
     * @param type $name
     * @param type $val
     * @param type $time
     * @param type $path
     * @param type $domain
     * @param type $secure https加密
     * @param type $httponly 禁止js读取cookie
     */
    public function setCookies($name, $val = '', $time = 0, $path = '/', $domain = '', $secure = FALSE, $httponly = FALSE) {
        if (trim($name)) {
            return setcookie($name, $val, $time, $path, $domain, $secure, $httponly);
        }else{
            return false;
        }
    }
    
    /**
     *  显示分页
     */
    public function showPagination($total) {
        //设置每页显示条数;
        $limit = $this->model->limit;

        //显示全部;
        if (!$limit) {
            $limit = $total;
        }

        //创建分页;
        if ($total == '' || !is_numeric($total)) {
             $total = 0;
        }
        $page = new Pagination_Default(array('total' => $total, 'perpage' => $limit));
        $style = 1;
        return '<div class="pagination" style="margin:0px;">'.$page->show($style).'</div>';
    }

}
