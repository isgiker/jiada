<?php
class Request {

    public function getRequest($var, $val) {
        if ($var) {
            if (!$_REQUEST[$var] && !is_null($val)) {
                return "$val";
            }
            return $_REQUEST[$var];
        }
        return false;
    }

    function getVar($name, $default = null, $hash = 'default') {
        // Ensure hash and type are uppercase
        $hash = strtoupper($hash);
        if ($hash === 'METHOD') {
            $hash = strtoupper($_SERVER['REQUEST_METHOD']);
        }

        // Get the input hash
        switch ($hash) {
            case 'GET' :
                $input = &$_GET;
                break;
            case 'POST' :
                $input = &$_POST;
                break;
            case 'FILES' :
                $input = &$_FILES;
                break;
            case 'COOKIE' :
                $input = &$_COOKIE;
                break;
            case 'ENV' :
                $input = &$_ENV;
                break;
            case 'SERVER' :
                $input = &$_SERVER;
                break;
            default:
                $input = $_REQUEST;
                $hash = 'REQUEST';
                break;
        }

        if (isset($input[$name]) && $input[$name] != null) {
            $var = Request::_addslashes($input);
        } else if ($default) {
            $var = $input[$name] = $default;
        }

        return $var;
    }

    static public function _addslashes($param) {
        //if magic_quotes_gpc=Off
        if (!get_magic_quotes_gpc()) {
            //if $param is an array
            if (is_array($param)) {
                foreach ($param as $key => $value) {
                    $param[$key] = addslashes($value);
                }
            } else {
                //if $param is not an array
                $param = addslashes($param);
            }
        } else {
            //if magic_quotes_gpc=On do nothing
        }
        return $param;
    }

}