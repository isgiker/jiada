<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Core_Request extends Yaf_Request_Http {

    function clean($source, $type = 'string') {
        // Handle the type constraint
        switch (strtoupper($type)) {
            case 'INT' :
            case 'INTEGER' :
                // Only use the first integer value
                preg_match('/-?[0-9]+/', (string) $source, $matches);
                $result = @ (int) $matches[0];
                break;

            case 'FLOAT' :
            case 'DOUBLE' :
                // Only use the first floating point value
                preg_match('/-?[0-9]+(\.[0-9]+)?/', (string) $source, $matches);
                $result = @ (float) $matches[0];
                break;

            case 'BOOL' :
            case 'BOOLEAN' :
                $result = (bool) $source;
                break;

            case 'WORD' :
                $result = (string) preg_replace('/[^A-Z_]/i', '', $source);
                break;

            case 'ALNUM' :
                $result = (string) preg_replace('/[^A-Z0-9]/i', '', $source);
                break;

            case 'CMD' :
                $result = (string) preg_replace('/[^A-Z0-9_\.-]/i', '', $source);
                $result = ltrim($result, '.');
                break;

            case 'BASE64' :
                $result = (string) preg_replace('/[^A-Z0-9\/+=]/i', '', $source);
                break;

            case 'STRING' :
                $result = (string) $source;
                break;

            case 'ARRAY' :
                $result = (array) $source;
                break;

            case 'PATH' :
                $pattern = '/^[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';
                preg_match($pattern, (string) $source, $matches);
                $result = @ (string) $matches[0];
                break;

            case 'USERNAME' :
                $result = (string) preg_replace('/[\x00-\x1F\x7F<>"\'%&]/', '', $source);
                break;



            default :
                $result = (string) $source;
                break;
        }
        return $result;
    }

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