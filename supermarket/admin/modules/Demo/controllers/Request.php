<?php

/**
 * @name IndexController
 * @author Vic
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class RequestController extends Core_Controller_Admin {

    /**
     * 封装request测试
     * @return boolean
     * @example http://chaoshi.jiada.local/demo/request/index/?get='as234.345 |get/'as234.345
     */
    public function indexAction() {
        //1. fetch query
//        $get = $this->getParam("get", "default value");

        $get = $this->getQuery("get",0,'none');
        print_r($get);exit;
        return false;
    }

}
