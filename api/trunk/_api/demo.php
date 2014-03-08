<?php

/**
 * @description 新版乘友SSO API
 * @author shiwei 20121225
 */
class demo {

    public static $cryptKey = '*w.KLH^F,W6jIi%kXz+K_w3%';

    public function run($param = null) {
        //参数验证
        if (!$param['case']) {
            $msg = '[' . date('Y-m-d H:i:s') . '] ' . 'param case does not exist' . "\n";
            return self::errorMessage($msg);
        }

        return self::$param['case']($param);
    }

    /**
     * -> 注册用户(用邮箱注册,邮箱需要激活)
     * @param email 邮箱
     * @param password 密码
     * @param username 用户名
     * demo.reg {"email":"1468386898@qq.com","password":"111111","username":"shiwei"}
     */
    static public function reg($parameter) {
       
        return self::returnData(array('uid' => 1111));
    }

    /*
     * 检查用户是否存在;
     */

    static public function checkUser($parameter) {
        return 222;
    }
    
    static public function errorMessage($msg = 'fail', $errorCode = null) {
        $errorCode = $errorCode ? $errorCode : -200;
        $returnResult = array(
            'result' => array(
                'status' => false,
                'message' => $msg,
                'code' => $errorCode
            )
        );
        return json_encode($returnResult);
    }

    static public function returnData($data = null) {
        $returnResult = array(
            'result' => array(
                'status' => true,
                'message' => 'success',
                'code' => 200
            )
        );
        if ($data)
            $returnResult['data'] = $data;
        return json_encode($returnResult);
    }


}

/* ==================================开始执行...================================ */
//签名验证：
//Util::checkSign();

$className = basename($_SERVER['PHP_SELF'], '.php');
if ($className == 'interface') {
    //连接采集数据库
    //$db = getDBO($_config_db['sso2.0']);
} else {
    /* 加载全局文件 */
    require_once('../global.php');

//连接采集数据库
    //$db = getDBO($_config_db['sso2.0']);

//获取参数;
    $param = Request::_addslashes($_REQUEST);
    eval('$result=' . $className . '::run($param);');
    die($result);
}
