<?php

/**
 * @name IndexController
 * @author Vic
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class RequestController extends Core_Basic_Controllers {

    public function indexAction() {
        //1. fetch query
//        $get = $this->getParam("get", "default value");

        $get = $this->getInt("get");
        
        return false;
    }

}
