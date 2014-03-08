<?php
/**
 * @abstract 全局文件
 * @author 石维(shiwei)
 * @version 1.0
 * @2011-01-14
 */
header("Content-type: text/html; charset=utf-8"); 
define('BASE_PATH', dirname(__FILE__) );
define('ROOT', dirname(__FILE__));
define( 'DS', DIRECTORY_SEPARATOR );

require_once ( BASE_PATH .DS.'core'.DS.'defines.php' );
require_once ( BASE_PATH .DS.'core'.DS.'framework.php' );