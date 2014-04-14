<?php

/**
 * @name SuccessController
 * @author Vic Shiwei
 * @desc 下单成功页面
 */
class SuccessController extends Core_Controller_Api{
    public $_config;
    protected $model;
    

    public function init() {
        parent::init();
        $this->_config = Yaf_Registry::get('_CONFIG');
        Yaf_Loader::import('phprpc/server/phprpc_server.php');
        Yaf_Loader::import('phprpc/server/dhparams.php');
        Yaf_Loader::import('phprpc/common/bigint.php');
        Yaf_Loader::import('phprpc/common/compat.php');
        Yaf_Loader::import('phprpc/common/phprpc_date.php');
        Yaf_Loader::import('phprpc/common/xxtea.php');
        $this->model = new Chaoshi_SuccessModel();
    }
    
    public function indexAction() {
        //禁止缓存
//        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->_layout = false;
        $phprpcServer = new PHPRPC_Server();
        $phprpcServer->add(array('getOrderInfo'),  $this);
        
        $phprpcServer->start();
    }
    
    /**
     * 获取订单信息
     * @param bigint|string $orderNo 订单编号。
     * @return array|json
     */
    public function getOrderInfo($param) {
        //验证参数,未登录情况下用户id是空的；
        if (!isset($param['orderNo']) || !$param['orderNo'] || !isset($param['userId']) || !$param['userId']) {
            return $this->errorMessage('请求参数错误！');
        }
        
        //获取商品相关信息
        $orderInfo=$this->model->getOrderInfo($param);
        if(!$orderInfo){
            return $this->errorMessage();
        }
        
        return $this->returnData($orderInfo);
    }
    
    
}
