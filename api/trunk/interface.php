<?php
/**
 * @description 新版乘友SSO API
 * @author shiwei 20121225
 */
require_once('global.php');

//获取参数;
$param = Request::_addslashes(array_merge($_REQUEST,$_FILES));

if (!$param['obj']) {
    die('param error');
}
$objs = explode('.', $param['obj']);
$notesTotal = count($objs);
$class = $objs[$notesTotal - 2];
$method = $objs[$notesTotal - 1];
unset($objs[$notesTotal - 1]);
$classPath = join(DS, $objs);
$classPath = '_api/' . $classPath . '.php';
//执行..
require_once($classPath);
eval('$result=' . $class . '::' . $method . '($param);');
die($result);