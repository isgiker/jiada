<?php

/**
 * @name IndexController
 * @author Vic Shiwei
 * @desc 店铺（仓库）控制器
 */
class ShopController extends Core_Controller_Business {
    
    protected $model;

    public function init() {
        parent::init();
        $this->model = new Chaoshi_ShopModel();
        $this->areaModel = new AreaModel();
    }
    
    /**
     * 店铺首页
     */
    public function indexAction(){
        $this->_layout = true;
        $data = null;
        $this->getView()->assign('data', $data);
    }
    
    /**
     * 新建店铺/仓库
     */
    public function addAction() {
        $this->_layout = true;
        $rules = $this->model->getRules();
        $post = $this->getPost();
        if($this->isPost()){
            $v = new validation(); //数据校验
            $v->parentCls=$this;
            $v->validate($rules, $post);
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
                $this->getView()->assign("post", $post);
                if ($this->isAjax()) {
                    $this->err(null, $v->error_message); //输出异步错误信息
                }
            }else{
                $this->saveAction($post, 'add');
            }
            if(isset($post['provinceId']) && $post['provinceId']){
                $city = $this->areaModel->getNodeArea($post['provinceId']);
                $this->getView()->assign("city", $city);
            }
            if(isset($post['cityId']) && $post['cityId']){
                $district = $this->areaModel->getNodeArea($post['cityId']);            
                $this->getView()->assign("district", $district);
            }
        }
        
        //获取省份
        $province = $this->areaModel->getNodeArea(0);
        
        
        $this->getView()->assign("province", $province);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
        //page 添加js文件..<!--iframeTools的位置必须在artDialog下面-->        
        $_page=array(
            'static_css_files' => [
            ],
            'static_js_files' => [
                ['path'=>'/plugin/jquery_artDialog/jquery.artDialog.js?skin=blue','attr'=>''],
                ['path'=>'/js/basic/libs/iframeTools.source.js','attr'=>''],
                ['path'=>'http://api.map.baidu.com/api?v=2.0&ak=7463442f78f85ee9bc9e7b3b0ff60e6d','attr'=>''],
                ['path'=>'/plugin/baidumap/js/baidumap.js','attr'=>['charset'=>'utf8']],
            ]
        );
        $this->getView()->assign("_page", $_page);
        
    }
    
    /**
     * 店铺/仓库信息，编辑；
     */
    public function editAction() {
        $this->_layout = true;
        if(empty($this->currentShopId)){
            $this->redirect("/$this->_ModuleName/$this->_ControllerName/index");
        }
        //获取当前店铺信息
        $shopInfo = $this->model->getShopInfo($this->currentShopId);
        if(empty($shopInfo)){
            $this->redirect("/$this->_ModuleName/$this->_ControllerName/index");
        }
        
        $rules = $this->model->getRules();
        
        if($this->isPost()){
            $post = $this->getPost();
            $post['shopId']=$this->currentShopId;
            
            $v = new validation(); //数据校验
            $v->parentCls=$this;
            $v->validate($rules, $post);
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
                if ($this->isAjax()) {
                    $this->err(null, $v->error_message); //输出异步错误信息
                }
            }else{
                $this->saveAction($post, 'edit');
            }
            
            
            $shopInfo=$post;
        }
        
        //获取省份
        $province = $this->areaModel->getNodeArea(0);
        $this->getView()->assign("province", $province);
        
        $city = $this->areaModel->getNodeArea($shopInfo['provinceId']);
        $this->getView()->assign("city", $city);
        
        $district = $this->areaModel->getNodeArea($shopInfo['cityId']);
        $this->getView()->assign("district", $district);
                
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('shopInfo', $shopInfo);
        
        //page 添加js文件..<!--iframeTools的位置必须在artDialog下面-->
        $_page=array(
            'static_css_files' => [
            ],
            'static_js_files' => [
                ['path'=>'/plugin/jquery_artDialog/jquery.artDialog.js?skin=blue','attr'=>''],
                ['path'=>'/js/basic/libs/iframeTools.source.js','attr'=>''],
                ['path'=>'http://api.map.baidu.com/api?v=2.0&ak=7463442f78f85ee9bc9e7b3b0ff60e6d','attr'=>''],
                ['path'=>'/plugin/baidumap/js/baidumap.js','attr'=>['charset'=>'utf8']],
            ]
        );
        $this->getView()->assign("_page", $_page);
        
    }
    
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
        
        $_eventUrl = "/$this->_ModuleName/$this->_ControllerName/index/shopId/$saveR";
        if($action=='edit'){
            $_eventUrl = "/$this->_ModuleName/$this->_ControllerName/$action/shopId/$saveR";
        }
        if($saveR){
            //保存成功跳转到列表页            
            if($this->isAjax()){
                $this->ok(null, $_eventUrl, '保存成功！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存成功！','_eventUrl'=>$_eventUrl));
//                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
            }
        }else{
            if($this->isAjax()){
                $this->ok(null, $_eventUrl, '保存失败！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存失败！','_eventUrl'=>$_eventUrl));
//                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
            }
            
        }
    }
}
