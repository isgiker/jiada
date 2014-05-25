<?php

/**
 * @name IndexController
 * @author Vic Shiwei
 * @desc 首页控制器
 */
class IndexController extends Core_Controller_My {
    
    protected $model;

    public function init() {
        parent::init();
        $this->shopModel = new Chaoshi_ShopModel();
    }
    
    /**
     * 
     */
    public function indexAction(){
        $this->_layout = true;
        $businessId = @$_COOKIE['businessId'];
        $this->getView()->assign('data', $this->businessShops);
    }
}
