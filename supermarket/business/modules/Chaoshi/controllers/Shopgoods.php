<?php

/**
 * @name GoodsController
 * @desc 我(商店)发布的商品
 * @author Vic
 */
class ShopgoodsController extends Core_Controller_Business {

    protected $model;
    protected $cateModel;

    public function init() {
        parent::init();
        $this->model = new Chaoshi_ShopgoodsModel();
        //加载商品分类模型
        $this->cateModel = new Chaoshi_GoodscateModel();
        $this->brandModel = new Chaoshi_GoodsbrandModel();
    }

    /**
     * 只显示本店铺的数据
     */
    public function indexAction() {
        $this->_layout = true;
        $post = $this->getPost();
        if ($post && $post['jsubmit']) {
            //分类
            if (isset($post['cateId']) && trim($post['cateId'])) {
                //获取分类信息
                $gcateInfo = $this->cateModel->getGcateInfo($post['cateId']);
                
                //parentPath的分类id顺序一定不能错：1级节点,2级,3级,...，全站统一就行。
                $parentPath=array();
                
                //记录页面select提交的历史值；
                $post_select_cateId = array();
                
                if($gcateInfo['parentPath']!=0){
                    $parentPath = explode(',', $gcateInfo['parentPath']);
                    $post_select_cateId=$parentPath;
                }
                //一级分类的parentId为0，手动写入头部;
                array_unshift($parentPath, '0');
                
                //将当前分类id加入post_selecct数组
                $post_select_cateId[]=$gcateInfo['cateId'];
                $this->getView()->assign('post_select_cateId', $post_select_cateId);

                //按照节点路径循环获取当前分类的父级列表
                foreach($parentPath as $k=>$parentId){
                    $node=$k+1;
                    $nodeGcate = $this->nodeGcateAction($parentId);
                    $this->getView()->assign('nodeCate'.$node, $nodeGcate);
                }
                
            //品牌
            $catebrands = $this->gcatebrandAction($post['cateId']);
            $this->getView()->assign('catebrands', $catebrands);
            
            }elseif (isset($post['cateId']) && !trim($post['cateId'])) {
                //获取一级商品分类
                $nodeCate1 = $this->nodeGcateAction(0);
                $this->getView()->assign('nodeCate1', $nodeCate1);
            }
            
            
            
            switch ($post['jsubmit']) {
                case 'search':
                    $data = $this->model->getGoodsList($this->currentShopId,$post);
                    $total = (int) $this->model->getGoodsTotal($this->currentShopId,$post);

                    break;
            }
            $this->getView()->assign('post', $post);
        } else {
            $data = $this->model->getGoodsList($this->currentShopId);
            $total = (int) $this->model->getGoodsTotal($this->currentShopId);

            //获取一级商品分类
            $nodeCate1 = $this->nodeGcateAction(0);
            $this->getView()->assign('nodeCate1', $nodeCate1);
        }


        //显示分页
        $pagination = $this->showPagination($total);

        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
    }

    public function editAction() {
        $this->_layout = true;
        $priceId = $this->getParam('priceId', 0);
        //获取商品信息
        $goodsPriceInfo = $this->model->getGoodsPriceInfo($priceId, $this->currentShopId);
        if (!$goodsPriceInfo) {
            $this->redirect("/$this->_ModuleName/$this->_ControllerName/index/shopId/$this->currentShopId");
        }

        $post = $this->getPost();
        $rules = $this->model->getRules();
        if ($this->isPost()) {
            $post['priceId'] = $priceId;
            $post['shopId'] = $this->currentShopId;
            
            //数据校验
            $v = new validation();
            $v->validate($rules, $post);

            if (!empty($v->error_message)) {
                //输出同步错误信息
                $this->getView()->assign("error", $v->error_message);
                $goodsPriceInfo = $post;
                
                if ($this->isAjax()) {
                    //输出异步错误信息
                    $this->err('', $v->error_message);
                }
            } else {
                //保存数据
                $this->saveAction($post, 'edit');
            }
        }

        //模板变量

        $this->getView()->assign('goodsPriceInfo', $goodsPriceInfo);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
        //page 添加css文件..
        $_page=array(
            'static_css_files' => [
                ['path'=>'/plugin/datetimepicker/css/jquery.datetimepicker.css','attr'=>'']
            ],
            'static_js_files' => [
            ]
        );
        $this->getView()->assign("_page", $_page);
    }
    
    /**
     * 添加商品库存，商品进货流水记录
     * @param int $goodsId 商品id
     * @param int $shopId 仓库/店铺id
     */
    public function stockAction() {
        $this->_layout = true;
        $goodsId = $this->getParam('goodsId', 0);
        //获取商品信息
        $goodsInfo = $this->model->getGoodsPriceInfo($goodsId);
        if (!$goodsInfo) {
            $this->redirect("/$this->_ModuleName/Shopgoods/index/shopId/$this->currentShopId");
        }

        $post = $this->getPost();
        $rules = $this->model->getStockRules();
        if ($this->isPost()) {
            $post['goodsId'] = $goodsId;
            //店铺id即仓库id;
            $post['shopId'] = $this->currentShopId;
            
            //数据校验
            $v = new validation();
            $v->validate($rules, $post);

            if (!empty($v->error_message)) {
                //输出同步错误信息
                $this->getView()->assign("error", $v->error_message);
                $this->getView()->assign('post', $post);
                if ($this->isAjax()) {
                    //输出异步错误信息
                    $this->err('', $v->error_message);
                }
            } else {
                //保存数据
                $this->saveAction($post, 'stock');
            }
        }

        //模板变量
        $this->getView()->assign('goodsInfo', $goodsInfo);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
        //page 添加css文件..
        $_page=array(
            'static_css_files' => [
                ['path'=>'/plugin/datetimepicker/css/jquery.datetimepicker.css','attr'=>'']
            ],
            'static_js_files' => [
            ]
        );
        $this->getView()->assign("_page", $_page);
    }
    
    /**
     * 显示商品进货历史记录
     * @param int $goodsId 商品id
     * @param int $shopId 仓库id
     */
    public function stocklistAction(){
        $this->_layout = true;
        $goodsId = $this->getParam('goodsId', 0);
        $shopId = $this->getParam('shopId', 0);
        if (!$goodsId || !$shopId) {
            $this->redirect("/$this->_ModuleName/$this->_ControllerName/index/shopId/$this->currentShopId");
        }
        $data = $this->model->getStocklist($goodsId, $shopId);
        $total = (int) $this->model->getStockTotal($goodsId, $shopId);
        
        //显示分页
        $pagination = $this->showPagination($total);

        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
    }
    
    /**
     * 获取分类的子分类,支持ajax http和函数形式调用;必须要有shopId;
     * 根据parentPath参数判断该分类是第几级分类，目的是要找出哪级分类是商品类型，在目前第3级分类是商品类型。
     * @param int $parentId 分类的父级节点id
     */
    public function nodeGcateAction($parentId = '0') {
//        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        //根据分类id获取所有子类
        if ($this->isAjax()) {
            $cateId = $this->getParam('cateId');
            if ($cateId) {
                $parentId = $cateId;
            }else{
                $parentId = '';
                return FALSE;
            }
        }
        
        //店铺的分类id
        $shopGcate = $this->model->getShopGcates($this->currentShopId);

        //获取数据        
        $nodeGcate = $this->cateModel->getNodeGcate($parentId, $shopGcate);
        if ($this->isAjax()) {
            $data = json_encode($nodeGcate);
            echo $data;
            exit;
        } else {
            return $nodeGcate;
        }
    }
    
    /**
     * 根据商品类型获取所有品牌
     * @param int $cateId 分类id
     * @return boolean
     */
    public function gcatebrandAction($cateId) {
        //根据商品分类（商品类型）获取所有品牌
        if (!$cateId) {
            if(!$cateId = $this->getParam('cateId')){
                return false;
            }
        }
        
        //店铺的分类id
        $shopGbrands = $this->model->getShopGbrands($this->currentShopId);
        
        //获取数据
        $cateBrand = $this->brandModel->getCateBrand($cateId, $shopGbrands);
        if ($this->isAjax()) {
            $data = json_encode($cateBrand);
            echo $data;
            exit;
        } else {
            return $cateBrand;
        }
        
    }    

    /**
     * 保存数据
     * @param array $data
     * @param string $action
     */
    public function saveAction($data,$action){
        if(!$data || !$action){
            if($this->isAjax()){
                $this->err(null, '参数错误');
            }else{
                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
            }
            
        }
        //保存数据 begin;返回shopId;
        $saveR = $this->model->$action($data);
        
        $_eventUrl = "/$this->_ModuleName/$this->_ControllerName/index/shopId/$this->currentShopId";

        if($saveR){
            //保存成功跳转到列表页            
            if($this->isAjax()){
                $this->ok(null, $_eventUrl, '保存成功！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存成功！','_eventUrl'=>$_eventUrl));
//                $this->redirect($_eventUrl);
            }
        }else{
            if($this->isAjax()){
                $this->ok(null, $_eventUrl, '保存失败！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存失败！','_eventUrl'=>$_eventUrl));
//                $this->redirect($_eventUrl);
            }
            
        }
    }

}
