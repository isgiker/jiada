<?php

/**
 * @name ListController
 * @desc 商品列表页面
 */
class ListController extends Core_Controller_Chaoshi {
    private $imagesConfig;
    
    private $fileImg;
    
    private $phprpcClient;
    
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
        
        $this->phprpcClient = new PHPRPC_Client('http://'.$this->_config->domain->api.'/Chaoshi/List/index');
        
    }
    
    /**
     * 根据一级分类获取子类数据，如果分类id不足3级系统默认补全3级如：cat=10026
     * @example http://chaoshi.jiada.local/List?cat=10026,10002,10045
     */
    public function indexAction() {
        $this->_layout = false;
        //商品分类id:catId1,catId2,catId3;一共三级
        $catesId=$this->getQuery('cat');
        $catesId=explode(',', $catesId);
        
        if(isset($catesId[0]) && $catesId[0]){
            $catList=$this->getCategaryList($catesId[0]);
            
            //如果cat参数只有一级分类id,那么二级分类id和三级分类id的值默认为子类下的第一个分类的id.
            if($catList && is_array($catList)){
                if(!isset($catesId[1]) || !$catesId[1]){
                    $catesId[1]=$catList[0]['cateId'];
                    
                    if(!isset($catesId[2]) || !$catesId[2]){
                        $catesId[2]=$catList[0]['child'][0]['cateId'];
                    }
                }else{
                    //如果二级分类存在，三级分类为空，那么三级分类id默认为当前二级分类id的第一个子类id
                    if(!isset($catesId[2]) || !$catesId[2]){
                        $curCatChild=$this->getCategaryChild($catesId[1]);
                        $catesId[2]=$curCatChild[0]['cateId'];
                    }
                }
                
            }
        }else{
            $catList=null;
        }
        
        //根据分类id获取商品，理论上第三级分类一定是存在的。
        if(isset($catesId[2]) && $catesId[2]){
            $param=array(
                'shopId'=>$this->shopId,
                'cateId'=>$catesId[2]
            );
            $pList=$this->getProductList($param);
        }else{
            $pList=array();
        }
        
        $this->getView()->assign('catList', $catList);
        $this->getView()->assign('catesId', $catesId);
        $this->getView()->assign('pList', $pList);
        
        $this->getView()->assign('imagesConfig', $this->imagesConfig);
        $this->getView()->assign('fileImg_obj', $this->fileImg);
    }
    
    private function getCategaryList($cateId) {
        //商品分类
        $result = @json_decode($this->phprpcClient->getCategaryList($cateId), true);
        if (isset($result['data']) && $result['data']) {
            $data=$result['data'];
        } else {
            $data = null;
        }

        return $data;
    }
    
    private function getCategaryChild($cateId){
        //商品分类
        $result = @json_decode($this->phprpcClient->getCategaryChild($cateId), true);
        if (isset($result['data']) && $result['data']) {
            $data=$result['data'];
        } else {
            $data = null;
        }

        return $data;
    }
    
    private function getProductList($cateId) {
        //商品分类
        $result = @json_decode($this->phprpcClient->getProductList($cateId), true);
        if (isset($result['data']) && $result['data']) {
            $data=$result['data'];
        } else {
            $data = null;
        }

        return $data;
    }
    
}
