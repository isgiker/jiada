<?php

/**
 * 读取4中配置的表信息,现支持.php.ini.xml.yaml
 */
class Parse {

    var $_settings = array();

    /**
     * 获取某些设置的值
     *
     * @param unknown_type $var
     * @return unknown
     */
    function get($var = NULL) {
        if (!$var)
            return $this->_settings;
        $var = explode('.', $var);

        $result = $this->_settings;
        foreach ($var as $key) {
            if (!isset($result[$key])) {
                return false;
            }

            $result = $result[$key];
        }

        return $result;


        // trigger_error ('Not yet implemented', E_USER_ERROR);//引发一个错误
    }

    function load() {
        trigger_error('Not yet implemented', E_USER_ERROR);
    }

}