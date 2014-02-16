<?php

class Core_Controller_Business extends Core_Controller_Basic {
    //当前登录商家账号id，可能是商家账号id也可能是商家的某个店铺的管理员id
    protected $currentBusinessId;
    //当前店铺的id，取cookie或url参数中的shopId
    protected $currentShopId;
    //当前登录账号所属行业
    protected $currentIndustry;
    
    //商家所有店铺
    protected $businessShops;


    public function init() {
        $this->currentBusinessId = @$_COOKIE['businessId'];
        //如果是店铺的管理员登录那么cookie会记录shopId
        if(isset($_COOKIE['shopId']) && $_COOKIE['shopId']){
            $this->currentShopId = $_COOKIE['shopId'];
        }else{
            $this->currentShopId = $this->getParam('shopId',0);
        }
        //当前登录用户为：商家账号id或店铺管理员账号id
        $this->currentUid = @$_COOKIE['uid'];
        
        $this->currentIndustry = @$_COOKIE['industry_modules'];
        
        $this->getView()->assign("currentShopId", $this->currentShopId);
        parent::init();
        
        $this->shopModel = new Chaoshi_ShopModel();
        $this->getBizShops();
        $this->getCurShopInfo();
        
        //权限判断
        if(!$this->checkPermission()){
            $this->showError('您无权执行此操作!');
        }
        
        //判断是否有人恶意篡改url的shopId参数
        if(!$this->checkShopId()){
            $this->showError('请不要恶意修改数据!');
        }
    }
    
    /**
     * 获取商家所有店铺（商家店铺管理layout header显示店铺列表）
     */
    public function getBizShops(){
//        $shops = $this->shopModel->getShops($this->currentBusinessId, $this->currentShopId);
        $shops = $this->shopModel->getShops($this->currentBusinessId);
        $bizShops=array();
        if($shops){
            foreach($shops as $shop){
                $bizShops[$shop['shopId']]=$shop;
            }
        }
        $shops=$bizShops;
        unset($bizShops);
        $this->businessShops=$shops;
        $this->getView()->assign("businessShops", $shops);
    }
    /**
     * 获取当前店铺信息（商家店铺管理layout header显示当前店铺）
     */
    public function getCurShopInfo(){
        $currentShopInfo = $this->shopModel->getShopInfo($this->currentShopId);
        $this->getView()->assign("currentShopInfo", $currentShopInfo);
        
    }
    
    
    /**
     * 判断帐户是否有执行此操作的权限。
     * @return boolen true允许访问|false无权访问
     */
    public function checkPermission(){
        //首先获取登录用户的权限范围，如果是商家主账号则无需判断权限，该账号拥有所有权限，可以访问任何资源。
        if(isset($_COOKIE['acl']) && $_COOKIE['acl']){
            $this->cookieAcl=@base64_decode($_COOKIE['acl']);
        }else{
            $this->cookieAcl='';
        }
        //如果acl等于空并且该账号没有所属店铺，则该账号是商家主账号；否则就是店铺账号
        if(isset($_COOKIE['shopId']) && $_COOKIE['shopId']){
            $this->cookieShopId=@$_COOKIE['shopId'];
        }else{
            $this->cookieShopId='';
        }
        if(!$this->cookieAcl && !$this->cookieShopId){
            return true;
        } else {
            //其次获取该行业的所有权限资源
            //获取权限资源文件
            $currentIndustry = strtolower($this->currentIndustry);
            $resourcConfig = new Yaf_Config_Ini(CONFIG_PATH . DS . 'resourc' . DS . 'business_' . $currentIndustry . '.ini');
            if (!isset($resourcConfig[$this->currentIndustry]) || !$resourcConfig[$this->currentIndustry]) {
                die('该行业没有配置权限资源文件，禁止访问！');
            }
            //注册资源，其它地方还要调用该权限资源。
//            $industryResourc='resourc_' . $currentIndustry;
//            $this->$industryResourc = $resourcConfig;
//            Yaf_Registry::set('resourc_' . $currentIndustry, $resourcConfig[$this->currentIndustry]);//无效

            //如果当前资源(url:module.controller.action)在全部资源里，那么需要检验当前资源是否在用户的acl允许范围里。
            if (isset($resourcConfig[$this->_ModuleName][$this->_ControllerName][$this->_ActionName])) {
                if (stripos($this->cookieAcl, $this->_ModuleName . '.' . $this->_ControllerName . '.' . $this->_ActionName)!==false) {                    
                    return true;
                }else{
                    return false;
                }
            } else {
                //如果当前资源(url:module.controller.action)不在全部资源里则不需要检验，即所有用户都能访问。
                return true;
            }
        }
        return false;
    }
    
    /**
     * 防篡改shopId;此函数需要在checkPermission函数执行后运行
     */
    public function checkShopId() {
        $shopId = $this->getParam('shopId',0);
        //如果是商家主账号登录,那么只允许修改商家自身的店铺id
        if(!$this->cookieAcl && !$this->cookieShopId){
            //商家查看店铺的时候
            if($shopId){
                if(isset($this->businessShops[$shopId]) && $this->businessShops[$shopId]){
                    return true;
                }else{
                    return false;
                }
                
            }else{
                //商家没有查看店铺
                return true;
            }
        } else {
            if($shopId==$this->currentShopId){
                return true;
            }
        }
        return false;
    }
    
    /**
     * 错误页信息
     * @param type $msg
     * @param string $action
     */
    public function showError($msg, $action = null) {
        $this->_layout = true;
        $this->setViewPath(APPLICATION_PATH . DS . 'views');
        $action = 'error' . DS . 'index.phtml';
        $layoutFile = 'layout' . DS . 'layout.phtml';
        $tplVars=['error' => $msg];
        if ($this->_layout == true) {
            $tplVars['_ActionContent'] = $this->getView()->render($action, $tplVars);
            echo $this->getView()->render($layoutFile, $tplVars);
        } else {
            echo $this->getView()->render($action, $tplVars);
        }
        exit;
    }
}
