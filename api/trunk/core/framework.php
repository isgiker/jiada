<?php

/**
 * @abstract 程序架构入口
 * @author 石维(shiwei)
 * @version 1.0
 * @2011-01-14
 */

/* 注册类库 */
include_once(JPATH_CORE . DS . 'config.regclass.php');

$config_reg_class = new JRegClass();

/* 自动加载注册文件 */

function __autoload($className) {

    global $config_reg_class;

    //获取类文件路径;
    $classPath = $config_reg_class->$className();

    //加载文件;
    include_once($classPath);
}

require_once (JPATH_CORE . DS . 'util.php');

//数据库类文件;
require_once (JPATH_LIBRARIES . DS . 'mysqldb.php');



/* 配置文件 */

//数据库配置文件
$_config_db = parse_ini_file(JPATH_CONFIGS . DS . "config.db.ini", true);

$_setting = parse_ini_file(JPATH_CONFIGS . DS . "setting.ini", true);


/* 设置时区 */
date_default_timezone_set('Asia/Shanghai');

/* 以(i)分为单位 */
$lifeTime = $_setting['lifetime'] * 60;

/* 以(s)秒为单位 */
if (!$lifeTime)
    $lifeTime = 24 * 1 * 3600;
ini_set('session.gc_maxlifetime', $lifeTime);

/* 使用cookie 记录session */
ini_set('session.cookie_lifetime', $lifeTime);
setcookie(session_name(), session_id(), time() + $lifeTime, '/');

/* DEBUG 设置 */
if ($_setting['error_reporting']) {
    $error_reporting = $_setting['error_reporting'];
} else {
    $error_reporting = 0;
}

if ($_setting['debug'] == 1) {
    ini_set('display_errors', 'On');
} else {
    ini_set('display_errors', 'Off');
}
error_reporting($error_reporting);

//设置超时;用于解决file_get_contents 连接超时的问题
$opts = array(
    'http' => array(
        'method' => "GET",
        'timeout' => 10,
    )
);
$context = stream_context_create($opts);



/*
 * 使用CURL替代file_get_contents方案
 * $timeout超时时间默认30秒
 * $url HTTP地址
 */

function curl_file_get_contents($url, $timeout = 10) {

    if (!$url)
        return false;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $file_contents = curl_exec($ch);
    curl_close($ch);

    return $file_contents;
}

/*
 * 全局函数
 */

function getDBO($option) {
    $db = new Mysqldb();
    $db->connect($option['host'], $option['username'], $option['password'], $option['dbname']);

    return $db;
}
