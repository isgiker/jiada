<?php
require('../global.php');
$modulePath = BASE_PATH .DS.'_api';

$files = getFiles($modulePath);

$var = array(
    'data'=>$files
);

$result = Template::_include('tree/list', $var);

die($result);


/**
 * 获取当前目录及子目录下的所有文件
 * @param string $dir 路径名
 * @return array 所有文件的路径数组
 */
function getFiles($dir) {
    global $modulePath;
    $files = array();

    if (!is_dir($dir)) {
        return $files;
    }

    $handle = opendir($dir);
    if ($handle) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && $file !='.svn') {
                $filePath = $dir . DS . $file;
                $fileName = $file;
                
                if(is_dir($filePath)){
                    $files[$fileName] = getFiles($filePath);
                }else{
//                    list($fileName, $suffix) = explode('.', $file);
                    $filePath = str_replace($modulePath.DS, '', $filePath);                    
                    $files[] = $filePath;
                }
                
//                if (is_file($filePath)) {
//                    $files[] = $fileName;
//                } else {
//                    $files = array_merge($files, getFiles($filePath));
//                }
            }
        }   //  end while
        closedir($handle);
    }
    return $files;
}