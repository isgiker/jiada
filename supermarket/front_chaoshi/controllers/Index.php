<?php

/**
 * @name IndexController
 * @desc 超市首页
 */
class IndexController extends Core_Controller_Chaoshi {

    private $imagesConfig;
    
    private $fileImg;
    
    private $phprpcClient; 
    
    public $mustLogin=false;

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

        $this->phprpcClient = new PHPRPC_Client('http://'.$this->_config->domain->api.'/Chaoshi/index/index');

    }

    public function indexAction() {
        $this->_layout = true;
        
        //全部分类
        $allCategary=$this->getAllCategary(0);
        $this->getView()->assign('allCategary', $allCategary);

        //楼层
        $floor = array();

        $f1 = $this->floor1();
        $floor[1] = array('floorName' => '牛奶乳品', 'floorData' => $f1);

        
        $this->getView()->assign('floor', $floor);

        //图片
        $this->getView()->assign('imagesConfig', $this->imagesConfig);
        $this->getView()->assign('fileImg_obj', $this->fileImg);
        
        //This page add css、js files .
        $_page=array(
            'static_css_files' => [
                ['path'=>'/css/front-end/chaoshi/v1/chaoshi_header.css','attr'=>''],
                ['path'=>'/css/front-end/chaoshi/v1/chaoshi_index.css','attr'=>''],
                ['path'=>'/plugin/slidebox/css/jquery.slideBox.css','attr'=>'']
            ],
            'static_js_files' => [                
                ['path'=>'/plugin/slidebox/js/jquery.slideBox.min.js','attr'=>['charset'=>'utf8']],
                ['path'=>'/js/front-end/chaoshi/v1/chaoshi_index.js','attr'=>['charset'=>'utf8']],
            ]
        );
        $this->getView()->assign("_page", $_page);
    }
    
    private function getAllCategary($cateId=0) {
        //全部商品分类
        $result = @json_decode($this->phprpcClient->getAllCategary($cateId), true);
        if (isset($result['data']) && $result['data']) {
            $data=$result['data'];
        } else {
            $data = null;
        }

        return $data;
    }

    /* F1-奶制品（begin）
     * ========================================================================= */

    private function floor1() {

        //商品类型
        $goodsTypeResult = @json_decode($this->phprpcClient->getGoodsType('10002', 12), true);
        if (isset($goodsTypeResult['data']) && $goodsTypeResult['data']) {
            $goodsType = $goodsTypeResult['data'];
        } else {
            $goodsType = null;
        }
        //品牌
        $catesBrandResult = @json_decode($this->phprpcClient->getCatesBrand('10002', 12), true);
        if (isset($catesBrandResult['data']) && $catesBrandResult['data']) {
            $catesBrand = $catesBrandResult['data'];
        } else {
            $catesBrand = null;
        }

        //纯牛奶
        $catesGoodsResult = @json_decode($this->phprpcClient->getCatesGoods($this->shopId, '10045', 10), true);
        if (isset($catesGoodsResult['data']) && $catesGoodsResult['data']) {
            $goods_chun = $catesGoodsResult['data'];
        } else {
            $goods_chun = null;
        }

        //儿童奶
        $catesGoodsResult = @json_decode($this->phprpcClient->getCatesGoods($this->shopId, '10222', 10), true);
        if (isset($catesGoodsResult['data']) && $catesGoodsResult['data']) {
            $goods_child = $catesGoodsResult['data'];
        } else {
            $goods_child = null;
        }



        $floor = array('catesGoods' => array(
                '0' => array('name' => '纯牛奶', 'data' => $goods_chun),
                '1' => array('name' => '儿童奶', 'data' => $goods_child)
            ),
            'goodsType' => $goodsType,
            'catesBrand' => $catesBrand,
        );

        return $floor;
    }
    
    

}
