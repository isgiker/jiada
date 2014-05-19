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
        $this->_layout = true;
        //商品分类id:catId1,catId2,catId3;一共三级
        $catesId=$this->getQuery('cat');
        $catesId=explode(',', $catesId);
        
        //分页
        $pageNum=$this->getQuery('p',1);
        
        //排序sort
        $sort=$this->getQuery('sort');
        if($sort){
            $sort=explode('_', $sort);
        }else{
            $sort=array('sales','desc');
        }
        
        //检索项search condition
        $searchCondition=$this->getQuery('sc');
        if($searchCondition){
            $searchCondition=explode(',', $searchCondition);
        }

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
        
        
        //根据分类id获取商品属性，理论上第二级分类一定是存在的。
        if(isset($catesId[1]) && $catesId[1]){
            $searchTerms=$this->getProductAttr($catesId[1]);
        }else{
            $searchTerms=array();
        }
        
        //根据分类id获取商品，理论上第三级分类一定是存在的。
        if(isset($catesId[2]) && $catesId[2]){
            $limit=20;
            $param=array(
                'shopId'=>$this->shopId,
                'cateId'=>$catesId[2],
                'searchCondition'=>$searchCondition,
                'sort'=>$sort,
                'limit'=>$limit,
                'pageNum'=>$pageNum
            );
            $pList=$this->getProductList($param);
            
            //显示分页
            $total = (int) $this->getProductListTotal($param);
            
            $totalpage = ceil($total / $limit);
            $prePage=  $this->prePage($pageNum);
            $nextPage=  $this->nextPage($pageNum, $totalpage);
            $pagination = $this->showPagination($total, $limit);
        }else{
            $pList=array();
            $total = 0;            
            $totalpage = 0;
            $prePage=0;
            $nextPage=0;
            $pagination = '';
        }
        
        //商品分类
        $this->getView()->assign('catList', $catList);
        $this->getView()->assign('catesId', $catesId);
        
        //全部分类
        $allCategary=$this->getAllCategary(0);
        $this->getView()->assign('allCategary', $allCategary);
        
        //商品列表、检索、分页
        $this->getView()->assign('sort', $sort);
        $this->getView()->assign('searchTerms', $searchTerms);
        $this->getView()->assign('searchCondition', $searchCondition);
        $this->getView()->assign('pList', $pList);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('totalpage', $totalpage);
        $this->getView()->assign('pageNum', $pageNum);
        $this->getView()->assign('prePage', $prePage);
        $this->getView()->assign('nextPage', $nextPage);
        $this->getView()->assign('pagination', $pagination);
        
        //图片
        $this->getView()->assign('imagesConfig', $this->imagesConfig);
        $this->getView()->assign('fileImg_obj', $this->fileImg);
        
        //This page add css、js files .
        $_page=array(
            'static_css_files' => [
                ['path'=>'/css/front-end/chaoshi/v1/chaoshi_header.css','attr'=>''],
                ['path'=>'/css/front-end/chaoshi/v1/chaoshi_list.css','attr'=>'']
            ],
            'static_js_files' => [
                ['path'=>'/js/front-end/chaoshi/v1/chaoshi_list.js','attr'=>['charset'=>'utf8']],
            ]
        );
        $this->getView()->assign("_page", $_page);
    }
    
    private function getAllCategary($cateId=0) {
        //全部商品分类
        $phprpcClient = new PHPRPC_Client('http://'.$this->_config->domain->api.'/Chaoshi/index/index');
        $result = @json_decode($phprpcClient->getAllCategary($cateId), true);
        if (isset($result['data']) && $result['data']) {
            $data=$result['data'];
        } else {
            $data = null;
        }

        return $data;
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
    
    /**
     * 商品列表
     * @param array $param 
     * @param string|bigint $shopId 店铺id
     * @param int $param cateId 终极分类id
     * @return null|array
     */
    private function getProductList($param) {
        $result = @json_decode($this->phprpcClient->getProductList($param), true);
        if (isset($result['data']) && $result['data']) {
            $data=$result['data'];
        } else {
            $data = null;
        }

        return $data;
    }
    
    private function getProductListTotal($param){
        $result = @json_decode($this->phprpcClient->getProductListTotal($param), true);
        if (isset($result['data']) && $result['data']) {
            $data=$result['data'];
        } else {
            $data = 0;
        }

        return $data;
    }


    /**
     * 商品属性（检索条件）
     * @param int $cateId 二级分类id
     * @return null|array
     */
    private function getProductAttr($cateId){
        $result = @json_decode($this->phprpcClient->getProductAttr($cateId), true);
        if (isset($result['data']) && $result['data']) {
            $data=$result['data'];
        } else {
            $data = null;
        }

        return $data;
    }
    
}
