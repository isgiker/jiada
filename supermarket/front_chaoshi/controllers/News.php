<?php

/**
 * @name NewsController
 * @desc 商品列表页面
 */
class NewsController extends Core_Controller_Chaoshi {
    private $imagesConfig;
    
    private $fileImg;
    
    private $phprpcClient;
    
    public function init() {
        parent::init();
        Yaf_Loader::import('phprpc/client/phprpc_client.php');
        Yaf_Loader::import('phprpc/common/bigint.php');
        Yaf_Loader::import('phprpc/common/compat.php');
        Yaf_Loader::import('phprpc/common/phprpc_date.php');
        Yaf_Loader::import('phprpc/common/xxtea.php');
        
        //加载配置文件
        $this->imagesConfig = Yaf_Registry::get("_ImagesConfig");
        
        $this->fileImg = new File_Image();
        
        $this->phprpcClient = new PHPRPC_Client('http://'.$this->_config->domain->api.'/Chaoshi/List/index');
        
    }
    
    /**

     */
    public function indexAction() {
        $this->_layout = true;
        
        //图片
        $this->getView()->assign('imagesConfig', $this->imagesConfig);
        $this->getView()->assign('fileImg_obj', $this->fileImg);
        
        //This page add css、js files .
        $_page=array(
            'static_css_files' => [
                ['path'=>'/css/front-end/chaoshi/v1/chaoshi_header.css','attr'=>''],
                ['path'=>'/css/front-end/chaoshi/v1/chaoshi_news.css','attr'=>'']
            ],
            'static_js_files' => [
                ['path'=>'/js/front-end/chaoshi/v1/chaoshi_news.js','attr'=>['charset'=>'utf8']],
            ]
        );
        $this->getView()->assign("_page", $_page);
    }
    
}
