<?php

class Core_Controller_Api extends Core_Controller_Basic {

    public function init() {
        parent::init();
        
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
