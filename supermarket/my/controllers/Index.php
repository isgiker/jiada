<?php

/**
 * @name IndexController
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Core_Controller_My {

    public function init() {
        parent::init();
    }
    
    public function indexAction() {
        $this->_layout = true;
        
        
        
        //This page add css、js files .
        $_page=array(
            'static_css_files' => [
                ['path'=>'/css/front-end/chaoshi/v1/chaoshi_header.css','attr'=>''],
                ['path'=>'/css/front-end/my/v1/my.css','attr'=>'']
            ],
            'static_js_files' => [
                ['path'=>'/js/front-end/my/v1/my.js','attr'=>['charset'=>'utf8']],
            ]
        );
        $this->getView()->assign("_page", $_page);
    }
    //
    public function demoAction(){
        $model=new demoModel();
        $model->aaa();
    }

}
