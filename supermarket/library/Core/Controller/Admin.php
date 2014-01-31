<?php

class Core_Controller_Admin extends Core_Controller_Basic {

    public $_layout = false;
    protected $_layoutVars = array();
    
    public function init() {
        parent::init();
        $this->getIndustrys();
    }
    
    /**
     * 后台管理界面根据行业切换不同的导航
     */
    public function getIndustrys(){
        $industryModel = new Default_IndustryModel();
        $industrys = $industryModel->getIndustrys();
        $this->getView()->assign("industrys", $industrys);
    }
    
}
