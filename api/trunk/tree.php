<?php
/**
 * @description 新版乘友SSO API
 * @author shiwei 20121225
 */
require_once('global.php');

$var = array(
    'data'=>$data
);

Template::render('tree/tree', $var);