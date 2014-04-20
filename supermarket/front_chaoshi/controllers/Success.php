<?php

/**
 * @name SuccessController
 * @desc 商品详情页面
 */
class SuccessController extends Core_Controller_Www {

    private $phprpcClient;
    
    private $uid;
    
    public function init() {
        parent::init();
        Yaf_Loader::import('phprpc/client/phprpc_client.php');
        Yaf_Loader::import('phprpc/common/bigint.php');
        Yaf_Loader::import('phprpc/common/compat.php');
        Yaf_Loader::import('phprpc/common/phprpc_date.php');
        Yaf_Loader::import('phprpc/common/xxtea.php');
        
        $this->phprpcClient = new PHPRPC_Client('http://'.$this->_config->domain->api.'/Chaoshi/Success/index');
        
        //用户信息
        $this->uid=$_COOKIE['uid'];
        
    }
    
    public function indexAction() {
        $this->_layout = false;
        //订单编号
        $orderNo=$this->getQuery('orderNo', 0);
        $param=array(
            'userId'=>  $this->uid,
            'orderNo'=>  $orderNo
        );
        if($orderNo){
            $orderInfo = $this->getOrderInfo($param);
        }else{
            $orderInfo = array();
        }
        
        $this->getView()->assign('orderInfo', $orderInfo);
        
    }
    /* 商品详情（begin）
     * ========================================================================= */

    private function getOrderInfo($param) {
        //商品类型
        $orderInfoResult = @json_decode($this->phprpcClient->getOrderInfo($param), true);
        if (isset($orderInfoResult['data']) && $orderInfoResult['data']) {
            $orderInfo = $orderInfoResult['data'];
        } else {
            $orderInfo = null;
        }

        return $orderInfo;
    }
    
}
