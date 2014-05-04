<?php

/**
 * @name ListController
 * @author Vic Shiwei
 * @desc 超市商品详情页面API
 */
class ListController extends Core_Controller_Api{
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
        $this->model = new Chaoshi_ListModel();
    }
    
    public function indexAction() {
        //禁止缓存
//        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->_layout = false;
        $phprpcServer = new PHPRPC_Server();
        $phprpcServer->add(array('getCategaryList','getCategaryChild','getProductList'),  $this);
        
        $phprpcServer->start();
    }
    
    /**
     * 递归获取商品分类id的子类列表
     * @param int $cateId 商品分类id。
     * @return array|json
     */
    public function getCategaryList($cateId) {
        $cateId=trim($cateId);
        if(!$cateId){
            return $this->errorMessage('请求参数错误！');
        }
        
        //获取商品相关信息
        $catList=$this->model->getCategaryList($cateId);
        if(!$catList){
            return $this->errorMessage('无数据！');
        }
        
        return $this->returnData($catList);
    }
    
    /**
     * 获取当前商品分类的子类（只一级不递归）
     * @param int $cateId 商品分类id。
     * @return array|json
     */
    public function getCategaryChild($cateId) {
        $cateId=trim($cateId);
        if(!$cateId){
            return $this->errorMessage('请求参数错误！');
        }
        
        //获取商品相关信息
        $catList=$this->model->getCategaryChild($cateId);
        if(!$catList){
            return $this->errorMessage('无数据！');
        }
        
        return $this->returnData($catList);
    }
    
    
    /**
     * 获取当前商品分类及相关检索条件匹配后的所有商品
     * @param array $param
     * @param int cateId $param['cateId'] 分类id
     * @return array|json
     */
    public function getProductList($param) {
        if(!$param || !is_array($param) || !$param['shopId'] || !$param['cateId']){
            return $this->errorMessage('请求参数错误！');
        }
        
        //获取商品列表
        $pList=$this->model->getProductList($param);
        if(!$pList){
            return $this->errorMessage('无数据！');
        }
        
        return $this->returnData($pList);
    }
}
