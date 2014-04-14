<?php

header("Content-Type: text/html; charset=utf-8");
error_reporting(E_ERROR | E_WARNING);
date_default_timezone_set("Asia/chongqing");
$sitepath = dirname($_SERVER['DOCUMENT_ROOT']); //站点根目录;
require_once($sitepath . '/library/File/Ftp.php');
require_once($sitepath . '/library/File/Image.php');


//上传图片框中的描述表单名称，
$title = htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
$path = htmlspecialchars($_POST['dir'], ENT_QUOTES);
$globalConfig = include( "config.php" );
$imgSavePathConfig = $globalConfig['imageSavePath'];

//获取存储目录
if (isset($_GET['fetch'])) {

    header('Content-Type: text/javascript');
    echo 'updateSavePath(' . json_encode($imgSavePathConfig) . ');';
    return;
}


//上传配置
$config = array(
    "savePath" => $imgSavePathConfig,
    "maxSize" => 1000, //单位KB
    "allowFiles" => array("gif", "png", "jpg", "jpeg", "bmp", "webp"),
    "fileNameFormat" => $_POST['fileNameFormat']
);

if (empty($path)) {

    $path = $config['savePath'][0];
}

//上传目录验证
if (!in_array($path, $config['savePath'])) {
    //非法上传目录
    echo '{"state":"\u975e\u6cd5\u4e0a\u4f20\u76ee\u5f55"}';
    return;
}

$config[ 'savePath' ] = $path . '/';

//文件上传状态,当成功时返回SUCCESS，其余值将直接返回对应字符窜并显示在图片预览框，同时可以在前端页面通过回调函数获取对应字符窜
$state = "SUCCESS";

//file_put_contents($path.'tt.txt', $_COOKIE['businessid']);
//格式验证
$fInfo = pathinfo($_FILES["upfile"]["name"]);
$current_type = $fInfo['extension'];
if (!in_array($current_type, $config['allowFiles'])) {
    $state = "不允许的文件类型！";
}

//大小验证
$file_size = 1024 * $config['maxSize'];
if ($_FILES["upfile"]["size"] > $file_size) {
    $state = "文件大小超出 MAX_FILE_SIZE 限制！";
}


//保存图片
if ($state == "SUCCESS") {

    /*
     * 将文件通过FTP从本地上传到图片服务器
     */
    $_config_images = parse_ini_file($sitepath . '/conf/images.ini', true);
    $imagesServerGroups = $_config_images['common']['setting.images.serverGroup'];

    $config = array(
        'hostname' => $_config_images['imga']['ftp.master.host'],
        'username' => $_config_images['imga']['ftp.master.username'],
        'password' => $_config_images['imga']['ftp.master.password'],
        'port' => $_config_images['imga']['ftp.master.port']
    );

    $fi = new File_Image();
    $servGroup = $fi->getImageServerGroup($imagesServerGroups);
    $imgParameter=array('imgType'=>$current_type,'imgServer'=>$servGroup);
    $path = $fi->getImagePath($imgParameter);
    $ftp = new File_Ftp();
    $ftp->connect($config);

    $ftp->createFolder($path['filePath']);
    $localpath = $_FILES['upfile']['tmp_name'];
    $remotepath = $path['filePath'] . '/' . $path['fileName'];
    $uploadResult = $ftp->upload($localpath, $remotepath, $mode = 'auto', $permissions = 777);
    if (!$uploadResult) {
        $state = "图片上传失败！！";
    }
    $completeImgUrl = 'http://' . $_config_images[$servGroup]['ftp.slave1.domain'] . $remotepath;
}


echo "{'url':'" . $completeImgUrl . "','title':'" . $title . "','original':'" . $_FILES['upfile']['name'] . "','state':'" . $state . "'}";

