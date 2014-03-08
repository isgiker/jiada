<?php

/**
 * @description	通用类
 * @author 石维(shiwei)
 * @version 1.0
 * @2011-01-14
 */

class Util {
    /* URL 重定向 */

    static public function redirect($url = null) {
        if ($url) {
            header('location:' . $url);
        }
    }

    /**
     * Show javascript message and redirect
     *
     * @param string $str
     * @param string $url
     * @param integer $flag
     */
    static public function location($url = '', $str = '', $flag = 1) {
        echo "<script>";
        if ($str)
            echo "alert('{$str}');";
        if ($url)
            echo "window.location.href='{$url}'";
        echo "</script>";
        if ($flag)
            exit;
    }

    static public function msg($str, $url = '') {
        echo "<script>";
        if ($str)
            echo "alert('$str');";
        if ($url)
            echo "window.location.href=\"$url\"";
        echo "</script>";
    }

    /* 多维数组中，判断键值是否存在 */

    static public function multi_array_key_exists($needle, $haystack) {
        if (!$haystack)
            return false;
        foreach ($haystack as $key => $value) {
            if ($needle === $key) {
                return $key;
            }
            if (is_array($value)) {
                if (self::multi_array_key_exists($needle, $value)) {
                    return $key . ":" . self::multi_array_key_exists($needle, $value);
                }
            }
        }
        return false;
    }

    /*
     * 使用CURL替代file_get_contents方案
     * $timeout超时时间默认30秒
     * $url HTTP地址
     */

    public function curl_file_get_contents($url, $timeout = 30) {

        if (!$url)
            return false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file_contents = curl_exec($ch);
        curl_close($ch);

        return $file_contents;
    }

    public function mkdir_r($dir, $mode = 0755) {
        return is_dir($dir) || ( self::mkdir_r(dirname($dir), $mode) && @mkdir($dir, $mode) );
    }
    
    /*
     * 签名验证
     */
    static function checkSign(){
        if(!$_REQUEST['sign']) die('签名验证失败！');
        $aes = new Aes();
        $sign = json_decode($aes->uncypherAES128(base64_decode($_REQUEST['sign'])));
        foreach($sign as $key => $value){
            if($_REQUEST[$key] != $value){
                die('签名验证失败！');
            }
        }
        return true;
    }
    
    /*
     * 获得一个点周围n千米的正方形的四个点
     */
    static function returnSquarePoint($lng,$lat,$distance= 1){
	    define(EARTH_RADIUS,6371);
	    $dlng= 2 * asin(sin($distance/ (2 * EARTH_RADIUS)) /cos(deg2rad($lat)));
	    $dlng= rad2deg($dlng);
	    $dlat=$distance/EARTH_RADIUS;
	    $dlat= rad2deg($dlat);
	  
	    return array(
	                'left-top'=>array('lat'=>$lat+$dlat,'lng'=>$lng-$dlng),
	                'right-top'=>array('lat'=>$lat+$dlat,'lng'=>$lng+$dlng),
	                'left-bottom'=>array('lat'=>$lat-$dlat,'lng'=>$lng-$dlng),
	                'right-bottom'=>array('lat'=>$lat-$dlat,'lng'=>$lng+$dlng)
	                );
    }

}