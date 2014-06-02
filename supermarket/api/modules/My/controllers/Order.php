<?php

/**
 * @name OrderController
 * @author Vic Shiwei
 * @desc 个人中心 - 我的订单API
 */
class OrderController extends Core_Controller_Api{
    public $_config;
    protected $model;
     protected $redisModel;

    public function init() {
        parent::init();
        $this->_config = Yaf_Registry::get('_CONFIG');
        Yaf_Loader::import('phprpc/server/phprpc_server.php');
        Yaf_Loader::import('phprpc/server/dhparams.php');
        Yaf_Loader::import('phprpc/common/bigint.php');
        Yaf_Loader::import('phprpc/common/compat.php');
        Yaf_Loader::import('phprpc/common/phprpc_date.php');
        Yaf_Loader::import('phprpc/common/xxtea.php');
        $this->model = new My_IndexModel();
        $this->redisModel = new RedisModel();
    }
    
    public function indexAction() {
        //禁止缓存
//        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->_layout = false;        
        $phprpcServer = new PHPRPC_Server();
        $phprpcServer->add(array('getMyOrderList'),  $this);
        
        $phprpcServer->start();
    }
    
    public function debugAction(){
        $r=$this->getMyOrderList('95433943100162057');
        
    }
    
    /**
     * 获取用户订单列表
     * @param int $param['userId'] 用户id
     * @param int $limit 条数
     * @return array|json
     */
    public function getMyOrderList($param, $limit=5) {
        $userId=trim($param['userId']);
        if(!$userId){
            return $this->errorMessage('请求参数错误！');
        }
        
        $orderList=$this->model->getMyOrderList($userId, $limit);
        
        $orderNo_str='';
        $new_orderList=array();
        if($orderList){
            foreach($orderList as $order){
                if(!$orderNo_str){
                    $orderNo_str=$order['orderNo'];
                }else{
                    $orderNo_str.=','.$order['orderNo'];
                }
                //根据userId和shopId获取该订单的商品列表
                $order['product_list']=$this->model->getOrderProductList($order['orderNo'],$order['shopId'],$param['userId']);
                
                $new_orderList[$order['orderNo']][]=$order;
            }
        }else{
            return $this->errorMessage('无数据！');
        }
        
        $orderNoList=$this->model->getMyOrderNoList($orderNo_str, $limit);
        
        $data=array('orderNoList'=>$orderNoList,'orderList'=>$new_orderList);
        if(!$data){
            return $this->errorMessage('无数据！');
        }
        return $this->returnData($data);
    }
    
    /**
     * 根据分类(商品类型)/多个分类(商品类型)获取旗下的商品
     * @param string $shopId,多个店铺id用逗号分隔。
     * @param string $catesId 分类(商品类型)id,分类(商品类型)id;多个分类id用逗号分隔。
     * @param int $limit 条数
     * @return array|json
     */
    public function getCatesGoods($shopId, $catesId, $limit=10) {
        $catesId=trim($catesId);
        if(!$shopId || !$catesId){
            return $this->errorMessage('请求参数错误！');
        }
        
        $data=$this->model->getCatesGoods($shopId, $catesId, $limit);
        if(!$data){
            return $this->errorMessage('无数据！');
        }
        return $this->returnData($data);
    }
    
    /**
     * 获取分类下的品牌
     * @param int $cateId 分类id
     * @param int $limit 条数
     * @return array|json
     */
    public function getCatesBrand($cateId, $limit=12) {
        $cateId=trim($cateId);
        if(!$cateId){
            return $this->errorMessage();
        }
        
        $data=$this->model->getCatesBrand($cateId, $limit);
        if(!$data){
            return $this->errorMessage();
        }
        return $this->returnData($data);
    }
    
    /**
     * 递归获取商品分类id的子类列表
     * @param int $cateId 商品分类id。
     * @return array|json
     */
    public function getAllCategary($cateId=0) {
        //获取商品相关信息
        
        //如果缓存过期或无数据则调用Mysql数据
        $cacheAllCates = $this->redisModel->getAllCategary();
        if (!$cacheAllCates) {
            $catList = $this->model->getAllCategary($cateId);
            if (!$catList) {
                return $this->errorMessage('无数据！');
            }else{
                //写入缓存
                $data=$this->returnData($catList);
                $this->redisModel->setAllCategary($data);
            }
            
            
        }else{
            return $cacheAllCates;
        }
        
        
    }
}
