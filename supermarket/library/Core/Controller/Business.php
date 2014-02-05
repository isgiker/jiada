<?php

class Core_Controller_Business extends Core_Controller_Basic {
    //当前登录商家账号id，可能是商家账号id也可能是商家的某个店铺的管理员id
    protected $currentBusinessId;
    //当前店铺的id，取url参数中的shopId
    protected $currentShopId;
    
    public function init() {
        $this->currentBusinessId = @$_COOKIE['uid'];
        $this->currentShopId = $this->getParam('shopId',0);
        $this->_layoutVars['shopId']=$this->currentShopId;
        $this->getView()->assign("currentShopId", $this->currentShopId);
        parent::init();
        
        $this->shopModel = new Chaoshi_ShopModel();
        $this->getBizShops();
        $this->getCurShopInfo();
    }
    
    /**
     * 获取商家店铺（商家店铺管理layout header显示店铺列表）
     */
    public function getBizShops(){
        $shops = $this->shopModel->getShops($this->currentBusinessId, $this->currentShopId);
        $this->getView()->assign("shops", $shops);
    }
    /**
     * 获取当前店铺信息（商家店铺管理layout header显示当前店铺）
     */
    public function getCurShopInfo(){
        $currentShopInfo = $this->shopModel->getShopInfo($this->currentShopId);
        $this->getView()->assign("currentShopInfo", $currentShopInfo);
        
    }
}
