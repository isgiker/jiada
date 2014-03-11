<?php

/**
 * @abstract 程序架构入口
 * @author Vic shiwei(石维)
 * @2011-01-14
 * 系统使用__autoload自动加载类文件,所以类名称必须与文件名相同；
 * 所有类文件在使用前必须提前到config.regclass.php文件注册;
 * 全局变量都以下划线“_”开头
 */

/*加载注册类库*/
include_once(CORE_PATH . DS . 'config.regclass.php');
$config_reg_class = new JRegClass();

/*自动加载注册文件*/
function __autoload($className) {
    global $config_reg_class;
    //获取类文件路径;
    $classPath = $config_reg_class->$className();
    //加载文件;
    include_once($classPath);
}

//创建工厂对象
$factory = new Factory();

/**加载站点配置文件
 * =============================================================================
 */

/* 获取站点配置文件 */
$_setting_config = $factory->getConfig('setting', 'ini');

/* 获取数据库配置文件 */
$_db_config = $factory->getConfig('databases', 'ini');
//$factory->getDBO('jiada');

/**站点设置
 * =============================================================================
 */

/* DEBUG 设置 */
if (isset($_setting_config['common']['setting']['errorLevel']) && $_setting_config['common']['setting']['errorLevel']) {
    $error_reporting = $_setting_config['common']['setting']['errorLevel'];
} else {
    $error_reporting = 0;
}

error_reporting($error_reporting);

if (isset($_setting_config['common']['setting']['debug']) && $_setting_config['common']['setting']['debug']==1) {
    ini_set('display_errors', 'On');
} else if (isset($_setting_config['common']['setting']['debug']) && $_setting_config['common']['setting']['debug']==2) {
    ini_set('display_errors', 'Off');
    Error::attachHandler();
} else {
    ini_set('display_errors', 'Off');
}

/* 设置时区 */
date_default_timezone_set($_setting_config['common']['setting']['offset']);

/* 以(i)分为单位 */
$lifeTime = $_setting_config['common']['setting']['lifetime'] * 60;

/* 以(s)秒为单位 */
if (!$lifeTime)
    $lifeTime = 24 * 1 * 3600;
ini_set('session.gc_maxlifetime', $lifeTime);

/* 使用cookie 记录session */
ini_set('session.cookie_lifetime', $lifeTime);
setcookie(session_name(), session_id(), time() + $lifeTime, '/');



//设置超时;用于解决file_get_contents 连接超时的问题
$opts = array(
    'http' => array(
        'method' => "GET",
        'timeout' => 10,
    )
);
$context = stream_context_create($opts);