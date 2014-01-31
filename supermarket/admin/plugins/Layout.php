<?php

class LayoutPlugin extends Yaf_Plugin_Abstract {

    private $_layoutDir;
    private $_layoutFile;
    private $_layoutVars = array();
    private $request;
    private $response;
    

    public function __construct() {
        $this->_layoutFile = 'layout.phtml';
        $this->_layoutDir = APPLICATION_PATH . DS . 'views'.DS.'layout'.DS;
    }

    public function __set($name, $value) {
        $this->_layoutVars[$name] = $value;
    }

    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        
    }

    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        
    }

    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        $this->request = $request;
        $this->response = $response;

    }

    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

    }

    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

    }

    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

    }

    /**
     * @param [string] $layoutfile [file name:layout.phtml]
     */    
    public function loadLayout($layoutfile = null){
        echo 7;
        if (!$layoutfile) {
            $layoutfile = $this->_layoutFile;
        }
        if($this->request->module == 'Index'){
            $defaultLayoutPath = $this->_layoutDir;
        }else{
            $defaultLayoutPath = APPLICATION_PATH.DS.'modules'.DS.$this->request->module.DS.'views'.DS.'layout'.DS;
        }
        $this->response->setBody('');
        $body = $this->response->getBody();
        
        /* clear existing response */
        $this->response->clearBody();

        /* wrap it in the layout */
        $layout = new Yaf_View_Simple($defaultLayoutPath);
        $layout->content = $body;
        $layout->assign('layout', $this->_layoutVars);

        /* set the response to use the wrapped version of the content */
        $this->response->setBody($layout->render($layoutfile));
        echo 8;
    }
    
    public function loadActionContent(){
        $defaultActionTplPath = APPLICATION_PATH.DS.'modules'.DS.$this->request->module.DS.'views'.DS.$this->request->action.DS;
        include_once $defaultActionTplPath;
    }

}