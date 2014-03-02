<?php

/**
 * @name IndexController
 * @author Vic Shiwei
 * @desc 首页控制器
 */
class IndexController extends Core_Controller_Www {
    
    protected $model;

    public function init() {
        parent::init();
        $this->indexModel = new Chaoshi_IndexModel();
    }
    
    /**
     * 
     */
    public function indexAction(){
        $this->_layout = false;
    }
}
