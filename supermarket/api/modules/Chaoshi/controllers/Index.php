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
        $phprpcServer->add(array('multiply','aRr'),  $this);
        $phprpcServer->add('add', IndexController);
        $phprpcServer->add('getGoodsType',  $this);
        
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

    static public function add($number) {
        return $number + 22;
    }

    public function multiply($number) {
        return $number * 2;
    }
    
    public function aRr(){
        $data=array('a'=>array('a1','a2'),'b'=>array('b1'),'d'=>array('c1'));
        return json_encode($data);
    }
}
