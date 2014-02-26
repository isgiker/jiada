<?php
$host = $_SERVER['HTTP_HOST'];
/**
 * $hostArr=['域名'=>'application目录']
 */
$hostArr = ['admin'=>'admin', 'business'=>'business','www'=>'front_www','chaoshi'=>'front_chaoshi'];
/**
 * $modules=['application目录'=>'站点模块,模块,模块']
 */
$modules = ['admin' => 'Index,Default,Chaoshi,Demo', 'business' => 'Index,Default,Chaoshi','front_www' => 'Index','front_chaoshi' => 'Index'];
$curHost = explode('.', $host);
if (isset($hostArr[$curHost[0]]) && $hostArr[$curHost[0]]) {
    $applicationName=$hostArr[$curHost[0]];
} else {
    $applicationName='front_www';
}
//application 改名为二级域名目录
define('APPLICATION_PATH', BASE_PATH.DS.$applicationName);
define('MODULES', $modules[$applicationName]);

define('CORE_PATH', BASE_PATH.DS.'core');
define('CONFIG_PATH', BASE_PATH.DS.'conf');
define('PUBLIC_PATH', BASE_PATH.DS.'public');

//多站点共享library
//define('LIBRARY_PATH', BASE_PATH.DS.'application'.DS.'library');
define('LIBRARY_PATH', BASE_PATH.DS.'library');

