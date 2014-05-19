<?php

/**
 * @name IndexController
 * @author Vic Shiwei
 * @desc 超市首页API
 */
class IndexController extends Core_Controller_Api{
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
        $this->model = new Chaoshi_IndexModel();
    }
    
    public function indexAction() {
        //禁止缓存
//        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->_layout = false;        
        $phprpcServer = new PHPRPC_Server();
        $phprpcServer->add(array('getGoodsType','getCatesGoods','getCatesBrand','getAllCategary'),  $this);
        
        $phprpcServer->start();
    }
    
    /**
     * 获取分类下的商品类型
     * @param int $cateId 分类id
     * @param int $limit 条数
     * @return array|json
     */
    public function getGoodsType($cateId, $limit=12) {
        $cateId=trim($cateId);
        if(!$cateId){
            return $this->errorMessage();
        }
        
        $data=$this->model->getGoodsType($cateId, $limit);
        if(!$data){
            return $this->errorMessage();
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
        $catList=$this->model->getAllCategary($cateId);
        if(!$catList){
            return $this->errorMessage('无数据！');
        }
        
        return $this->returnData($catList);
    }
}
