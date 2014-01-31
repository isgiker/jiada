<?php

/**
 * @name IndexController
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Core_Controller_Admin {

    public function init() {
        parent::init();
    }
    
    public function indexAction() {
        $this->_layout = true;
    }

}
