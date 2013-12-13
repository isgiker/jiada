<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Core_Basic_Request extends Yaf_Request_Http {

    /**
     * mysql_escape_string会比addslashes更安全
     * mysql_escape_string会分析哪些在字符串需要进行处理，
     * 而addslashes则单纯对单引号(‘)，是双引号(“)，反斜杠(\)和NUL字元加入反斜杠
     */
    public static function _quotes($content) {
        //if magic_quotes_gpc=Off
        if (!get_magic_quotes_gpc()) {
            //if $content is an array
            if (is_array($content)) {
                foreach ($content as $key => $value) {
                    $content[$key] = mysql_escape_string($value);
                }
            } else {
                //if $content is not an array
                $content = mysql_escape_string($content);
            }
        } else {
            //if magic_quotes_gpc=On do nothing
        }
        return $content;
    }

    public static function _addslashes($content) {
        //if magic_quotes_gpc=Off
        if (!get_magic_quotes_gpc()) {
            //if $content is an array
            if (is_array($content)) {
                foreach ($content as $key => $value) {
                    $content[$key] = addslashes($value);
                }
            } else {
                //if $content is not an array
                $content = addslashes($content);
            }
        } else {
            //if magic_quotes_gpc=On do nothing
        }
        return $content;
    }

    public static function _setcookie($name, $val = NULL, $time = NULL, $path = NULL, $domain = NULL) {
        if ($name) {
            global $_setting;
            if ($time == null)
                $time = time() + 3600 * 24;
            if ($path == null)
                $path = $_setting['cookie_path'];
            if ($domain == null)
                $domain = $_setting['cookie_domain'];
            setcookie($name, $val, $time, $path, $domain, 0);
        }
    }

}