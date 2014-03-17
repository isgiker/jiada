<?php

/**
 * @name DetailController
 * @author Vic Shiwei
 * @desc 超市商品详情页面API
 */
class DetailController extends Core_Controller_Api{
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
        $this->model = new Chaoshi_DetailModel();
    }
    
    public function indexAction() {
        //禁止缓存
//        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->_layout = false;
        $phprpcServer = new PHPRPC_Server();
        $phprpcServer->add(array('getGoodsInfo'),  $this);
        
        $phprpcServer->start();
    }
    
    /**
     * 根据商品价格id获取商品信息
     * @param bigint|string $priceId 商品价格id。
     * @return array|json
     */
    public function getGoodsInfo($priceId) {
        $priceId=trim($priceId);
        if(!$priceId){
            return $this->errorMessage();
        }
        
        $data=$this->model->getGoodsInfo($priceId);
        if(!$data){
            return $this->errorMessage();
        }
        return $this->returnData($data);
    }
    
}
