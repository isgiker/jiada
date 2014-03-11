<?php

/**
 * 读取4中配置的表信息,现支持.php.ini.xml.yaml
 */
//////////////////////读取INI文件,主要用到parser_ini_file函数,该函数返回一个数组,如第二个参数为true时则返回多维数组/////////////////////////////////////////
/**
 * ini例子:
 * [db]
  name = test
  host = localhost
  调用例子:
  $settings = new Settings_INI;
  $settings->load('config.ini');
  echo 'INI: ' . $settings->get('db.host') . '';
 */
class Parse_ini Extends Parse {

    function load($file=null) {
        if (!$file || file_exists($file) == false) {
            return false;
        }
        $this->_settings = parse_ini_file($file, true);
    }

}
