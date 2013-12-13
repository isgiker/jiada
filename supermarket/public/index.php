<?php
/**
 * @abstract 此框架基于Yaf开发, PHP 5.3.0以下版本不兼容
 * @author Vic Shiwei <isgiker@gmail.com>
 */
header("Content-type: text/html; charset=utf-8"); 
define('BASE_PATH', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);
define('TJ_EVN', 'development');
//require_once BASE_PATH.DS.'core'.DS.'framework.php';

require_once BASE_PATH.DS.'core'.DS.'define.php';

//Bootstrap
$application = new Yaf_Application( CONFIG_PATH .DS. "application.ini");
//$application->getDispatcher()->throwException(true);
$application->bootstrap()->run();
