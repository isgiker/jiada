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
    static public function getIp() {
        $onlineip = '';
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        return $onlineip;
    }

}

?>
