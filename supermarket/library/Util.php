<?php

/**
 * @abstract通用类
 * 
 */
class Util {
    /*
     * 创建唯一标识符
     */

    static public function getUuid() {
        $uuid = uniqid(getmypid()) . mt_rand(1, 10000000000);
        return $uuid;
    }
    
    /** 
     * @abstract获取订单编号
     */
    static public function getOrdersn() {
        $millisecond = sprintf("%.4f", microtime(true)) * 10000;
        return $millisecond . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * 获取用户IP
     */
    static public function getIP() {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        RETURN $ip;
    }
    
    /*
     * 使用CURL替代file_get_contents方案
     * $timeout超时时间默认30秒
     * $url HTTP地址
     */

    static public function curl_file_get_contents($url, $timeout = 30) {

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

}

?>
