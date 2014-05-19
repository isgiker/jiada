<?php

/**
 * @name DetailController
 * @desc 商品详情页面
 */
class DetailController extends Core_Controller_Www {
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
        
        $this->phprpcClient = new PHPRPC_Client('http://'.$this->_config->domain->api.'/Chaoshi/Detail/index');
        
    }
    
    public function indexAction() {
        $this->_layout = true;
        //商品价格id
        $priceId=$this->getQuery('p', 0);

        $data = $this->getGoodsInfo($priceId);
        $this->getView()->assign('goodsInfo', $data['goodsInfo']);
        $this->getView()->assign('goodsDetail', $data['goodsDetail']);
        
        if(isset($data['goodsInfo']['cateId'])){
            $cateNodes = $this->getCateNodes($data['goodsInfo']['cateId']);
            $this->getView()->assign('cateNodes', $cateNodes);
        }
        
        $this->getView()->assign('imagesConfig', $this->imagesConfig);
        $this->getView()->assign('fileImg_obj', $this->fileImg);
        $this->getView()->assign('priceId', $priceId);
//        $this->display('index2.phtml');
        //This page add css、js files .
        $_page=array(
            'static_css_files' => [
                ['path'=>'/css/front-end/chaoshi/v1/chaoshi_header.css','attr'=>''],
                ['path'=>'/css/front-end/chaoshi/v1/chaoshi_detail.css','attr'=>''],
                ['path'=>'/plugin/jqzoom_ev-2.3/css/jquery.jqzoom.css','attr'=>'']
            ],
            'static_js_files' => [
                ['path'=>'/js/front-end/chaoshi/v1/chaoshi.js','attr'=>['charset'=>'utf8']],
                ['path'=>'/js/front-end/chaoshi/v1/chaoshi_detail.js','attr'=>['charset'=>'utf8']],
                ['path'=>'/plugin/jqzoom_ev-2.3/js/jquery.jqzoom-core.js','attr'=>['charset'=>'utf8']]
            ]
        );
        $this->getView()->assign("_page", $_page);
    }
    
    /* 商品详情（begin）
     * ========================================================================= */

    private function getGoodsInfo($priceId) {
        //商品类型
        $result = @json_decode($this->phprpcClient->getGoodsInfo($priceId), true);
        if (isset($result['data']) && $result['data']) {
            $goodsInfo=$result['data']['goodsInfo'];
            $goodsDetail=$result['data']['goodsDetail'];
            $data=array('goodsInfo'=>$goodsInfo,'goodsDetail'=>$goodsDetail);
        } else {
            $data = null;
        }

        return $data;
    }
    
    private function getCateNodes($cateId) {
        //商品类型
        $resultData = @json_decode($this->phprpcClient->getCateNodes($cateId), true);
        if (isset($resultData['data']) && $resultData['data']) {
            $cateNodes = $resultData['data'];
        } else {
            $cateNodes = null;
        }

        return $cateNodes;
    }

}
