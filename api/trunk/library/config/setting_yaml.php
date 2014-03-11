<?php
/**
* 读取4中配置的表信息,现支持.php.ini.xml.yaml
*/

//////////////////////////////////读取YAML格式文件///////////////////////////////////////////////
/**
使用YAML必须使用到SPYC这个库,相关链接在http://spyc.sourceforge.net/
YAML配置例子:
db:
   name: test
   host: localhost


*/
class Setting_yaml Extends Setting {
function load ($file) {
       if (file_exists($file) == false) { return false; }

       include ('spyc.php');
       $this->_settings = Spyc::YAMLLoad($file);
}

}