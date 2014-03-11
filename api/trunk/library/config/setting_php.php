<?php
/**
* 读取4中配置的表信息,现支持.php.ini.xml.yaml
*/
/**
* 针对PHP的配置,如有配置文件
* $file=
<?php
$db = array();

// Enter your database name here:
$db['name'] = 'test';

// Enter the hostname of your MySQL server:
$db['host'] = 'localhost';

?>


具体调用:
include ('settings.php'); //原始环境假设每个类为单独的一个类名.php文件

// Load settings (PHP)
$settings = new Settings_PHP;
$settings->load('config.php');

echo 'PHP: ' . $settings->get('db.host') . '';

*
*/
class Setting_php Extends Setting {
function load ($file) {
         if (file_exists($file) == false) { return false; }

         // Include file
         include ($file);
unset($file);   //销毁指定变量
$vars = get_defined_vars(); //返回所有已定义变量的列表,数组,变量包括服务器等相关变量,
//通过foreach吧$file引入的变量给添加到$_settings这个成员数组中去.
foreach ($vars as $key => $val) {
         if ($key == 'this') continue;

         $this->_settings[$key] = $val;
}

}
 

}