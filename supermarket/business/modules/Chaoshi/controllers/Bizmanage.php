<?php

/**
 * @name IndexController
 * @author Vic Shiwei
 * @desc 商家信息控制器
 */
class BizmanageController extends Core_Controller_Business {
    
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
