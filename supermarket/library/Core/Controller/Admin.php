<?php

class Core_Controller_Admin extends Core_Controller_Basic {

    public $_layout = false;
    protected $_layoutVars = array();
    //当前登录账号所属行业模块
    protected $currentIndustryModule;

    public function init() {
        //根据行业不同，切换不同的管理模块
        if (isset($_COOKIE['industry_modules']) && $_COOKIE['industry_modules']) {
            $this->currentIndustryModule = @$_COOKIE['industry_modules'];
        } else {
            $this->currentIndustryModule = 'Default';
        }
        $this->getView()->assign("currentIndustryModule", $this->currentIndustryModule);
        
        parent::init();
        
        $this->getIndustrys();

        //权限判断
        if (!$this->checkPermission()) {
            $this->showError('您无权执行此操作!');
        }
    }

    /**
     * 后台管理界面根据行业切换不同的导航
     */
    public function getIndustrys() {
        $industryModel = new Default_IndustryModel();
        $industrys = $industryModel->getIndustrys();
        $this->getView()->assign("industrys", $industrys);
    }

    /**
     * 判断帐户是否有执行此操作的权限。
     * @return boolen true允许访问|false无权访问
     */
    public function checkPermission() {
        //首先获取登录用户的权限范围，如果是商家主账号则无需判断权限，该账号拥有所有权限，可以访问任何资源。
        if (isset($_COOKIE['acl']) && $_COOKIE['acl']) {
            $this->cookieAcl = @base64_decode($_COOKIE['acl']);
        } else {
            $this->cookieAcl = '';
        }
        //其次获取该行业的所有权限资源
        //获取权限资源文件
        $resourcConfig = new Yaf_Config_Ini(CONFIG_PATH . DS . 'resourc' . DS . 'admin.ini');
        if (!$resourcConfig) {
            die('没有配置权限资源文件，禁止访问！');
        }

        //如果当前资源(url:module.controller.action)在全部资源里，那么需要检验当前资源是否在用户的acl允许范围里。
        if (isset($resourcConfig[$this->_ModuleName][$this->_ControllerName][$this->_ActionName])) {
            if (stripos($this->cookieAcl, $this->_ModuleName . '.' . $this->_ControllerName . '.' . $this->_ActionName) !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            //如果当前资源(url:module.controller.action)不在全部资源里则不需要检验，即所有用户都能访问。
            return true;
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
