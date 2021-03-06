<?php
/*
 * 生成缩略图.类
 */
class File_Thumbnail {

    //图片类型 
    var $type;
    private $allowtype = array('image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/gif'); //充许上传文件的类型,类型合集
    //实际宽度 
    var $width;
    //实际高度 
    var $height;
    //改变后的宽度 
    var $resize_width;
    //改变后的高度 
    var $resize_height;
    //是否裁图 
    var $cut;
    //源图象 
    var $srcimg;
    //目标图象地址 
    var $dstimg;
    //临时创建的图象 
    var $im;

    function exe($img, $wid, $hei, $c, $dstpath) {
        $this->srcimg = $img;
        $this->resize_width = $wid;
        $this->resize_height = $hei;
        $this->cut = $c;
        //图片的类型 
//        $this->type = strtolower(substr(strrchr($this->srcimg, "."), 1));
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $this->type = finfo_file($finfo, $img);
        finfo_close($finfo);

        //初始化图象 
        $this->initi_img();
        //目标图象地址 
        $this->dst_img($dstpath);
        //-- 
        $this->width = imagesx($this->im);
        $this->height = imagesy($this->im);
        //生成图象 
        $this->newimg();
        ImageDestroy($this->im);
    }

    function newimg() {
        //改变后的图象的比例 
        $resize_ratio = ($this->resize_width) / ($this->resize_height);
        //实际图象的比例 
        $ratio = ($this->width) / ($this->height);
        if (($this->cut) == "1") {
        //裁图 
            if ($ratio >= $resize_ratio) {
            //高度优先 
                $newimg = imagecreatetruecolor($this->resize_width, $this->resize_height);
                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $this->resize_width, $this->resize_height, (($this->height) * $resize_ratio), $this->height);
                ImageJpeg($newimg, $this->dstimg);
            }
            if ($ratio < $resize_ratio) {
            //宽度优先 
                $newimg = imagecreatetruecolor($this->resize_width, $this->resize_height);
                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $this->resize_width, $this->resize_height, $this->width, (($this->width) / $resize_ratio));
                ImageJpeg($newimg, $this->dstimg);
            }
        } else {
        //不裁图 
            if ($ratio >= $resize_ratio) {
                $newimg = imagecreatetruecolor($this->resize_width, ($this->resize_width) / $ratio);
                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, $this->resize_width, ($this->resize_width) / $ratio, $this->width, $this->height);
                ImageJpeg($newimg, $this->dstimg);
            }
            if ($ratio < $resize_ratio) {
                $newimg = imagecreatetruecolor(($this->resize_height) * $ratio, $this->resize_height);
                imagecopyresampled($newimg, $this->im, 0, 0, 0, 0, ($this->resize_height) * $ratio, $this->resize_height, $this->width, $this->height);
                ImageJpeg($newimg, $this->dstimg);
            }
        }
    }

    //初始化图象 
    function initi_img() {
        if ($this->type == 'image/jpeg' || $this->type == 'image/pjpeg') {
            $this->im = imagecreatefromjpeg($this->srcimg);
        }
        if ($this->type == 'image/gif') {
            $this->im = imagecreatefromgif($this->srcimg);
        }
        if ($this->type == 'image/png') {
            $this->im = imagecreatefrompng($this->srcimg);
        }
    }

    //图象目标地址 
    function dst_img($dstpath) {
        $full_length = strlen($this->srcimg);
        $type_length = strlen($this->type);
        $name_length = $full_length - $type_length;

        $name = substr($this->srcimg, 0, $name_length - 1);
        $this->dstimg = $dstpath;

    //echo $this->dstimg; 
    }

}

//$Thumbnail = new File_Thumbnail("图片源文件地址", "131", "212", "0", "缩略图地址");
