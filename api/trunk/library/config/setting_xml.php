<?php
/**
* 读取4中配置的表信息,现支持.php.ini.xml.yaml
*/

//////////////////////读取XML文件,需要用到XML_PARSER//////////////////////////////////////////////////////////
/**
* XML例子:
    <?xml version="1.0" encoding="UTF-8"?>
<settings>
         <db>
                   <name>test</name>
                   <host>localhost</host>
         </db>
</settings>
调用例子:
// Load settings (XML)
$settings = New Settings_XML;
$settings->load('config.xml');
echo 'XML: ' . $settings->get('db.host') . '';

*
*/
class Setting_xml Extends Setting {
function load ($file) {
       if (file_exists($file) == false) { return false; }

       /**xmllib.php为PHP XML Library, version 1.2b,相关连接:http://keithdevens.com/software/phpxml
       xmllib.php主要特点是把一个数组转换成一个xml或吧xml转换成一个数组
       XML_unserialize:把一个xml给转换 成一个数组
       XML_serialize:把一个数组转换成一个xml
       自PHP5起,simpleXML就很不错,但还是不支持将xml转换成数组的功能,所以xmlLIB还是很不错的. 
       */
       include ('xmllib.php');  
       $xml = file_get_contents($file);
       $data = XML_unserialize($xml);
       $this->_settings = $data['settings'];
}

}