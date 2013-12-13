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

    public function getImagePath($imageSize, $imgType, $imgServer) {
        if (!$imageSize) {
            die('Images size parameter can not be null！');
        }

        $year = date("Y");
        $month = date("m");
        $day = date("d");
        $fileName = $this->getImageName($imgType, $imgServer);
        $hashDir1 = substr($fileName, 0, 2);
        $hashDir2 = substr($fileName, 2, 2);


        $imagePath = '/' . $imageSize . '/' . $year . '/' . $month . '/' . $day . '/' . $hashDir1 . '/' . $hashDir2;
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
     * @abstract 生成图片地址；sizeDesc:large/medium/small 图片大小描述是为了兼容旧版本必须设置的一个值
     * @param type $imgParameter=array('imgSize'=>'60X60','sizeDesc'=>'small','imgUrl'=>'/800X600/2013/11/15/16/c7/16c7f5bc0450a8e797031b1e727d5925_imga.png');
     * @param type $imagesConfig=Yaf_Registry::get("imagesConfig");
     * @example $imagesConfig = Yaf_Registry::get("imagesConfig");$fi = new File_Image();fi->generateImgUrl($imgParameter,$imagesConfig);
     * @return string
     */
    public function generateImgUrl($imgParameter, $imagesConfig) {
        if (!trim($imgParameter['imgSize']) or !trim($imgParameter['imgUrl']) or !trim($imgParameter['sizeDesc'])) {
            die('Images Size、sizeDesc and Url can not be null!');
        }

        //因为要兼容第一版的图片路径，所以判断图片地址是新版的还是旧版的。
        $imgPathPattern = '/(\d{4})\/(\d{2})\/(\d{2})\/(\w{2})\/(\w{2})\//i';
        $isNew = preg_match($imgPathPattern, $imgParameter['imgUrl']);

        if ($isNew) {
            //不同图片尺寸的Url地址;
            $imgSizePattern = '/(\d+)X(\d+)/i';
            $newImgUrl = preg_replace($imgSizePattern, $imgParameter['imgSize'], $imgParameter['imgUrl']);

            //获取图片服务器组名;
            $tmpName = substr($newImgUrl, strrpos($newImgUrl, '_') + 1);
            $serverGroupName = pathinfo($tmpName)['filename'];


            $imgDomain = $imagesConfig->$serverGroupName->ftp->slave1->domain;
            $completeImgUrl = 'http://' . $imgDomain . $newImgUrl;
            return $completeImgUrl;
        } else {
            $oldImgUrlArr = explode('|', $imgParameter['imgUrl']);
            if (count($oldImgUrlArr) == 1) {
                $completeImgUrl = 'http://' . $imgDomain . $imgParameter['imgUrl'];
                return $completeImgUrl;
            }
            $oldSizeArr = array('large' => 1, 'medium' => 2, 'small' => 3);
            $imgSize = $oldSizeArr[trim($imgParameter['sizeDesc'])];
            if ($imgSize) {
                $imgDomain = $imagesConfig->common->setting->oldimg->domain;
                $oldImgUrl = $oldImgUrlArr[$imgSize - 1];
                $completeImgUrl = 'http://' . $imgDomain . $oldImgUrl;
                return $completeImgUrl;
            } else {
                die('The Image path is v1.0!');
            }
        }
    }

    /**
     * @abstract 根据图片尺寸生成图片路径；
     * @param type $imgParameter=array('imgSize'=>array('60X60','200X200'),'imgUrl'=>'/800X600/2013/11/15/16/c7/16c7f5bc0450a8e797031b1e727d5925_imga.png');
     * @example $imagesConfig = Yaf_Registry::get("imagesConfig");$fi = new File_Image();fi->generateImgUrl($imgParameter);
     * @return string or array
     */
    public function getImgSizeUrl($imgParameter) {
        if (!$imgParameter['imgSize'] or !trim($imgParameter['imgUrl'])) {
            die('Images Size and Url can not be null!');
        }

        //不同图片尺寸的Url地址;
        $imgSizePattern = '/(\d+)X(\d+)/i';
        if(is_array($imgParameter['imgSize'])){
            $newImgUrl = array();
            foreach($imgParameter['imgSize'] as $size){
                $newImgUrl[$size] = preg_replace($imgSizePattern, $imgParameter['imgSize'], $imgParameter['imgUrl']);
            }
            return $newImgUrl;            
        }else{
            $newImgUrl = preg_replace($imgSizePattern, $imgParameter['imgSize'], $imgParameter['imgUrl']);
            return $newImgUrl;
        }
        
    }

}
