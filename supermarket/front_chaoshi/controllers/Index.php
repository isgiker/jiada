<?php

/**
 * @name IndexController
 * @desc 超市首页
 */
class IndexController extends Core_Controller_Chaoshi {

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

        $this->phprpcClient = new PHPRPC_Client('http://api.jiada.local/Chaoshi/index/index');

    }

    public function indexAction() {
        $this->_layout = false;

        //楼层
        $floor = array();

        $f1 = $this->floor1();
        $floor[1] = array('floorName' => '奶制品', 'floorData' => $f1);

        $this->getView()->assign('floor', $floor);

        $this->getView()->assign('imagesConfig', $this->imagesConfig);
        $this->getView()->assign('fileImg_obj', $this->fileImg);
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

        //特惠商品
        $catesGoodsResult = @json_decode($this->phprpcClient->getCatesGoods($this->shopId, '10016', 10), true);
        if (isset($catesGoodsResult['data']) && $catesGoodsResult['data']) {
            $goods_tehui = $catesGoodsResult['data'];
        } else {
            $goods_tehui = null;
        }

        //常温奶
        $catesGoodsResult = @json_decode($this->phprpcClient->getCatesGoods('95396877549174785', '10045', 10), true);
        if (isset($catesGoodsResult['data']) && $catesGoodsResult['data']) {
            $goods_suannai = $catesGoodsResult['data'];
        } else {
            $goods_suannai = null;
        }



        $floor = array('catesGoods' => array(
                '1' => array('name' => '特惠专区', 'data' => $goods_tehui),
                '2' => array('name' => '常温奶', 'data' => $goods_suannai)
            ),
            'goodsType' => $goodsType,
            'catesBrand' => $catesBrand,
        );

        return $floor;
    }

}
