<?php

/**
 * @description	工厂类
 * @author	shiwei
 * @date	2011-01-14
 */
class Factory {

    protected $_config = null;

    /*
     * 获取配置文件；
     */
    function getConfig($filename, $ftype = 'ini', $child = NULL) {
        $className = 'Parse_' . strtolower($ftype);
        $filepath = CONFIG_PATH . DS . $filename . '.' . strtolower($ftype);
        $settings = new $className();
        $settings->load($filepath);
        $this->_config = $settings->get($child);
        foreach ($this->_config as $key => $item) {
            if (is_array($item)) {
                $newarray = array();
                foreach ($item as $k => $v) {
                    if (strstr($k, '.')) {
                        $key_arr = explode('.', $k);

                        for ($i = 0; $i < count($key_arr); $i++) {

                            if ($i == 0) {
                                if (!Util::multi_array_key_exists($key_arr[$i], $newarray)) {
                                    $newarray [$key_arr[$i]] = array();
                                }
                                $tmparray = &$newarray [$key_arr[$i]];
                            } else {
                                if (($i + 1) == count($key_arr)) {
                                    $tmparray[$key_arr[$i]] = $v;
                                } else {
                                    if (!Util::multi_array_key_exists($key_arr[$i], $newarray)) {
                                        $tmparray[$key_arr[$i]] = array();
                                    }
                                }

                                $tmparray = &$tmparray [$key_arr[$i]];
                            }
                        }
                        unset($this->_config[$key][$k]);
                    }
                }
                $this->_config[$key] = array_merge_recursive($this->_config[$key], $newarray);
            }
        }
        return $this->_config;
    }

    /**
     * Instantiated database;
     */
    public static function getDBO($dbNode = 'development') {
//        $config = $this->getConfig(CONFIG_PATH . DS . "databases.ini", 'ini', $dbNode);
        $config = new Yaf_Config_Ini(CONFIG_PATH . DS . "databases.ini", $dbNode);

        $dbOption = array(
            'driver' => $config->db->driver,
            'host' => $config->db->host,
            'port' => $config->db->port,
            'user' => $config->db->user,
            'pass' => $config->db->pass,
            'dbname' => $config->db->dbname,
            'dbprefix' => $config->db->dbprefix,
            'charset' => $config->db->charset,
            'debug' => $config->db->debug,
            'persist' => $config->db->persist,
            'persist_key' => $config->db->persist_key
        );

        $db = Db_Adapter::getInstance($dbOption);
        if ($error = $db->ErrorMsg()) {
            die("$error");
        }

        return $db;
    }

    function getPagination($total = 0, $perpage = 20, $style = 1) {
        $page = new Pagination(array('total' => $total, 'perpage' => $perpage));

        //开启AJAX：
        //$ajaxpage=new page(array('total'=>1000,'perpage'=>20,'ajax'=>'ajax_page','page_name'=>'test'));
        //echo 'mode:1<br>'.$ajaxpage->show();

        if (!$total)
            return false;
        return $page->show($style);
    }

}