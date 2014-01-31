<?php
$host = $_SERVER['HTTP_HOST'];
$hostArr = ['admin', 'business','www'];
$modules = ['admin' => 'Index,Default,Chaoshi,Demo', 'business' => 'Index,Default,Chaoshi','www' => 'Index'];
$curHost = explode('.', $host);
if (in_array($curHost[0], $hostArr)) {
    $curHost = $curHost[0];
} else {
    $curHost = 'www';
}
//application 改名为二级域名目录
define('APPLICATION_PATH', BASE_PATH.DS.$curHost);
define('MODULES', $modules[$curHost]);

define('CORE_PATH', BASE_PATH.DS.'core');
define('CONFIG_PATH', BASE_PATH.DS.'conf');
define('PUBLIC_PATH', BASE_PATH.DS.'public');

//多站点共享library
//define('LIBRARY_PATH', BASE_PATH.DS.'application'.DS.'library');
define('LIBRARY_PATH', BASE_PATH.DS.'library');

