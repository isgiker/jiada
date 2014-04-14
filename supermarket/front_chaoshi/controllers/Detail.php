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
        
        $this->phprpcClient = new PHPRPC_Client('http://api.jiada.local/Chaoshi/Detail/index');
        
    }
    
    public function indexAction() {
        $this->_layout = false;
        //商品价格id
        $priceId=$this->getQuery('p', 0);
        
        $goodsInfo = $this->getGoodsInfo($priceId);
        $this->getView()->assign('goodsInfo', $goodsInfo);
        
        $cateNodes = $this->getCateNodes($goodsInfo['cateId']);
        $this->getView()->assign('cateNodes', $cateNodes);
        
        $this->getView()->assign('imagesConfig', $this->imagesConfig);
        $this->getView()->assign('fileImg_obj', $this->fileImg);
    }
    
    /* 商品详情（begin）
     * ========================================================================= */

    private function getGoodsInfo($priceId) {
        //商品类型
        $goodsInfoResult = @json_decode($this->phprpcClient->getGoodsInfo($priceId), true);
        if (isset($goodsInfoResult['data']) && $goodsInfoResult['data']) {
            $goodsInfo = $goodsInfoResult['data'];
        } else {
            $goodsInfo = null;
        }

        return $goodsInfo;
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
