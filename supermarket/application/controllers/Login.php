<?php

/**
 * @name IndexController
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Core_Basic_Controllers {

    public function init() {
        
    }
    
    public function indexAction() {
        $this->_layout=true;
        $this->_layoutVars['meta_title'] = 'Yaf-J Framework!';
        return TRUE;
    }
    
    public function topAction() {
        //echo $this->_tplcontent();
    }
    
    public function leftAction() {
        //echo $this->_tplcontent();
    }

    public function rightAction() {
        //echo $this->_tplcontent();
    }
    
    public function rightTopAction() {
        //echo $this->_tplcontent();
    }

}
