<?php

/**
 * @abstract图片处理类
 * @author Vic Shi <isgiker@gmail.com>
 */
class File_Image {
    /*
     * 图片文件名;
     */

    public function getImageName($imgType, $imgServer) {
        if (!$imgType || !$imgServer) {
            die('Image type or Image server Parameters can not be null！');
        }
        $imageTypeArr = array('jpeg', 'jpg', 'png', 'gif', 'tiff', 'tif', 'bmp', 'xpm');
        $imgType = strtolower($imgType);
        if (!in_array($imgType, $imageTypeArr)) {
            die($imgType . ' files can not be upload！');
        }

        $randStr = rand(1000, 10000000);
        $fileName = md5(time() . $randStr) . '_' . $imgServer . '.' . $imgType;
        return $fileName;
    }

    /**
     * 生成FTP服务器上的图片路径（不需要图片尺寸）
     * @param array $imgParameter=array('imgType'=>'png','imgServer'=>'imga');
     * @return array
     */
    public function getImagePath($imgParameter) {
        if (!$imgParameter['imgType'] || !$imgParameter['imgServer']) {
            die('imgType and imgServer parameter can not be null！');
        }

        $year = date("Y");
        $month = date("m");
        $day = date("d");
        $fileName = $this->getImageName($imgParameter['imgType'], $imgParameter['imgServer']);
        $hashDir1 = substr($fileName, 0, 2);
        $hashDir2 = substr($fileName, 2, 2);

        $imagePath = '/' . $year . '/' . $month . '/' . $day . '/' . $hashDir1 . '/' . $hashDir2;
        $path = array(
            'filePath' => $imagePath,
            'fileName' => $fileName
        );

        return $path;
    }

    public function getImageServerGroup($serverGroups) {
        if (!trim($serverGroups)) {
            die('Images server group can not be null!');
        }

        $gropsArr = explode(',', $serverGroups);
        $groupTotal = count($gropsArr);
        //分布式图片服务器以后完善;
        if ($groupTotal >= 2) {
            return $gropsArr[rand(0, $groupTotal - 1)];
        } else {
            return $gropsArr[0];
        }
        return false;
    }

    /**
     * @abstract 生成图片地址,图片尺寸不是必要参数
     * @param type $imgParameter=array('imgSize'=>'60X60','imgUrl'=>'/800X600/2013/11/15/16/c7/16c7f5bc0450a8e797031b1e727d5925_imga.png');
     * @param type $imagesConfig=Yaf_Registry::get("imagesConfig");
     * @example $imagesConfig = Yaf_Registry::get("imagesConfig");$fi = new File_Image();fi->generateImgUrl($imgParameter,$imagesConfig);
     * @return string
     */
    public function generateImgUrl($imgParameter, $imagesConfig) {
        if (!trim($imgParameter['imgUrl']) || !$imagesConfig) {
            return false;
//            die('Images Url and imagesConfig can not be null!');
        }
        if(isset($imgParameter['imgSize']) && $imgParameter['imgSize']){
            //不同图片尺寸的Url地址;
            $imgSizePattern = '/(\d+)X(\d+)/i';
            $imgUrl = preg_replace($imgSizePattern, $imgParameter['imgSize'], $imgParameter['imgUrl']);

        }else{
            $imgUrl = $imgParameter['imgUrl'];
        }
        
        //获取图片服务器组名;
        $tmpName = substr($imgUrl, strrpos($imgUrl, '_') + 1);
        $serverGroupName = pathinfo($tmpName)['filename'];

        if(isset($imagesConfig->$serverGroupName->ftp->slave1->domain)){
            $imgDomain = $imagesConfig->$serverGroupName->ftp->slave1->domain;
        }else{
            $imgDomain = '';
        }
        
        $completeImgUrl = '//' . $imgDomain . $imgUrl;
        return $completeImgUrl;
    }

    
    /**
     * 生成缩略图
     * @param type $im 图片对象，应用函数之前，你需要用imagecreatefromjpeg()读取图片对象，如果PHP环境支持PNG，GIF，也可使用imagecreatefromgif()，imagecreatefrompng()；
     * @param type $maxwidth 定义生成图片的最大宽度（单位：像素）
     * @param type $maxheight 生成图片的最大高度（单位：像素）
     * @param type $name 生成的图片名
     * @param type $filetype 最终生成的图片类型（.jpg/.png/.gif）
     */
    public function resizeImage($im, $maxwidth, $maxheight, $name, $filetype) {
        $infos = getimagesize($im);

        $pic_width = $infos[0];
        $pic_height = $infos[1];
        $filetype=$infos[2];
        if (($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight)) {
            if ($maxwidth && $pic_width > $maxwidth) {
                $widthratio = $maxwidth / $pic_width;
                $resizewidth_tag = true;
            }

            if ($maxheight && $pic_height > $maxheight) {
                $heightratio = $maxheight / $pic_height;
                $resizeheight_tag = true;
            }

            if ($resizewidth_tag && $resizeheight_tag) {
                if ($widthratio < $heightratio)
                    $ratio = $widthratio;
                else
                    $ratio = $heightratio;
            }

            if ($resizewidth_tag && !$resizeheight_tag)
                $ratio = $widthratio;
            if ($resizeheight_tag && !$resizewidth_tag)
                $ratio = $heightratio;

            $newwidth = $pic_width * $ratio;
            $newheight = $pic_height * $ratio;

            if (function_exists("imagecopyresampled")) {
                $newim = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
            } else {
                $newim = imagecreate($newwidth, $newheight);
                imagecopyresized($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
            }

            $name = $name . $filetype;
            imagejpeg($newim, $name);
            imagedestroy($newim);
        } else {
            $name = $name . $filetype;
            imagejpeg($im, $name);
        }
    }

}
