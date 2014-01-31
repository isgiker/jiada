<?php

/**
 * @name IndexController
 * @author Vic Shiwei
 * @desc 首页控制器
 */
class IndexController extends Core_Controller_Admin {
    
    protected $model;

    public function init() {
        parent::init();
        $this->model = new Chaoshi_IndexModel();
    }
    
    /**
     * 
     */
    public function indexAction(){
        $this->_layout = true;
        $data=array();
        $this->getView()->assign('data', $data);
    }
}
