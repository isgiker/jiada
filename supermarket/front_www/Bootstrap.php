<?php

/**
 * @name Bootstrap
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract {

    private $_config;
    private $_settingConfig;

    public function _initConfig() {
        //把配置保存起来
        $this->_config = $arrConfig = Yaf_Application::app()->getConfig('development');
        Yaf_Registry::set('_CONFIG', $arrConfig);

        /**
         * loading and setting config option;
         */
        $this->_settingConfig = new Yaf_Config_Ini(CONFIG_PATH . DS . 'setting.ini', 'development');
        Yaf_Registry::set('_SETTINGCONFIG', $this->_settingConfig);

        //Set Debug
        error_reporting($this->_settingConfig->setting->errorLevel);
        if ($this->_settingConfig->setting->debug) {
            ini_set('display_errors', $this->_settingConfig->setting->debug);
        }

        //图片服务器配置文件
        $ImagesConfig = new Yaf_Config_Ini(CONFIG_PATH . DS . 'images.ini');
        Yaf_Registry::set('_ImagesConfig', $ImagesConfig);
    }
    
    public function _initRegisterClass(Yaf_Dispatcher $dispatcher) {
        Yaf_Loader::getInstance()->registerLocalNamespace(array("Factory"));
    }
    
    /**
     * 关闭自定义错误，因为自定义错误无法用‘@’符合抑制     
    public function _initErrors() {
        Core_Error::attachHandler($this->_settingConfig);        
    }
    */
    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        //用户插件
//        $objUserPlugin = new UserPlugin();
//        $dispatcher->registerPlugin($objUserPlugin);

    }

    public function _initRoute(Yaf_Dispatcher $dispatcher) {
        //在这里注册自己的路由协议,默认使用简单路由
    }

    public function _initView(Yaf_Dispatcher $dispatcher) {
        //在这里注册自己的view控制器，例如smarty,firekylin
        $objView = new Core_View_Basic(null);
        $dispatcher->setView($objView);
    }

}
