<?php

/**
 * @name OrderController
 * @author Vic Shiwei
 * @desc 我的订单控制器
 */
class OrderController extends Core_Controller_My {
    
    private $imagesConfig;
    
    private $fileImg;
    
    private $phprpcClient; 
    
    public $mustLogin=true;
    
    private $uid;

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

        $this->phprpcClient = new PHPRPC_Client('http://'.$this->_config->domain->api.'/My/Order/index');
        
        //用户信息
        $this->uid = $_COOKIE['uid'];

    }


    /**
     * 
     */
    public function indexAction(){
        $this->_layout = true;
        $param = array(
            'userId' => $this->uid
        );
        
        $orderData = $this->getMyOrderList($param);

        $this->getView()->assign('data', $orderData);
        $this->getView()->assign('imagesConfig', $this->imagesConfig);
        $this->getView()->assign('fileImg_obj', $this->fileImg);
        
        
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
    
    private function getMyOrderList($param) { 
        if(!isset($param['userId']) || !$param['userId']){
            return false;
        }
        //全部商品分类
        $result = $this->phprpcClient->getMyOrderList($param);        
        $result = @json_decode($result, true);
        if (isset($result['data']) && $result['data']) {
            $data=$result['data'];
        } else {
            $data = null;
        }

        return $data;
    }
    
}
