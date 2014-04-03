<?php

/**
 * @name OrderController
 * @desc 订单信息确认（该页面必须要登录才能访问）
 */
class OrderController extends Core_Controller_Www {
    private $imagesConfig;
    
    private $fileImg;
    
    private $phprpcClient;
    
    private $uid;
    
    private $userKey;


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
        //接口地址
        $this->phprpcClient = new PHPRPC_Client();
        
        //用户信息
        $this->uid=$_COOKIE['uid'];
        $this->userKey=$_COOKIE['user-key'];
        
    }
    
    public function indexAction() {
        $parameter=array(
            'userId'=>  $this->uid,
            'userKey'=>  $this->userKey
        );
        
        //请求接口,获取用户购物车内的商品数据
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Cart/index');
        $recountResult = $this->phprpcClient->recount($parameter);
        $recountResult = json_decode($recountResult,true);
        
        //获取默认收货地址
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Order/index');
        $addressData = $this->phprpcClient->getDefaultAddress($this->uid);
        $addressData = json_decode($addressData,true);

        $this->getView()->assign('defaultAddress', $addressData['data']['defaultAddress']);
        $this->getView()->assign('areas', $addressData['data']['areas']);
        $this->getView()->assign('data', $recountResult['data']);
    }
    
    /**
     * 根据用户id获取用户收货地址（必须要登录后才能请求该接口）
     */
    public function userAddressAction(){
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Order/index');
        $result = $this->phprpcClient->getUserAddress($this->uid);
        $result = json_decode($result,true);
        $data=$result['data'];
        $this->getView()->assign('data', $data);
    }
    
    /**
     * 选择收货地址（必须要登录后才能请求该接口）
     */
    public function selectedAddressAction(){
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $addressId=$this->getPost('addressId');
        $parameter=array(
            'userId'=>  $this->uid,
            'addressId'=>  $addressId
        );
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Order/index');
        $result = $this->phprpcClient->setDefaultAddress($parameter);
        exit($result);
    }
    
    /**
     * 保存收货地址
     */
    public function addAddressAction(){
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $post=$this->getPost();
        $post['userId']=$this->uid;
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Order/index');
        $result = $this->phprpcClient->addAddress($post);
        exit($result);
    }

}
