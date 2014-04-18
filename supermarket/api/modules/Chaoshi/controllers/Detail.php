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
        $phprpcServer->add(array('getGoodsInfo','getCateNodes'),  $this);
        
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
            return $this->errorMessage('请求参数错误！');
        }
        
        //获取商品相关信息
        $goodsInfo=$this->model->getGoodsInfo($priceId);
        if(!$goodsInfo){
            return $this->errorMessage('无数据！');
        }
        $goodsDetail=$this->model->getGoodsDetail($goodsInfo['goodsId']);
        
        $data=array('goodsInfo'=>$goodsInfo,'goodsDetail'=>$goodsDetail);
        
        return $this->returnData($data);
    }
    
    /**
     * 根据分类id获取分类节点信息
     * @param int $cateId 分类id。
     * @return array|json
     */
    public function getCateNodes($cateId) {
        //分类信息
        $cateInfo=$this->model->getGoodsCateInfo($cateId);
        if($cateInfo && isset($cateInfo['parentPath'])){
            //获取分类节点路径信息
            $cateNodes=$this->model->getCateNodes($cateInfo['parentPath']);
            if(!$cateNodes){
                return $this->errorMessage();
            }
            $parentPath=  explode(',', $cateInfo['parentPath']);
            $newNode=array();
            foreach($cateNodes as $cateItem){
                $newNode[$cateItem['cateId']]=$cateItem;
            }
            $data=array('nodePath'=>$parentPath,'nodeItems'=>$newNode);
            return $this->returnData($data);
        }else{
            return $this->errorMessage();
        }
    }
    
}
