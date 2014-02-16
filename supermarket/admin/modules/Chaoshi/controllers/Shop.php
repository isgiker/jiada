<?php

/**
 * @name ShopController
 * @author Vic
 * @desc 店铺管理
 */
class ShopController extends Core_Controller_Admin {
    
    protected $model;

    public function init() {
        parent::init();
        $this->model = new Chaoshi_ShopModel();
        $this->areaModel = new Default_AreaModel();
    }
    
    /**
     * 获取所有店铺。
     */
    public function indexAction(){
        $this->_layout = true;
        $post = $this->getPost();
        if ($this->isPost() && isset($post['jsubmit']) && $post['jsubmit']) {
            switch ($post['jsubmit']) {
                case 'search':

                    $data = $this->model->getShopList($post);
                    $total = (int) $this->model->getShopTotal($post);

                    break;
            }
        } else {
            $data = $this->model->getShopList();
            $total = (int) $this->model->getShopTotal();
        }
        
        //处理数据，关联区域表和行业表
        if ($data) {
            foreach ($data as $key => $items) {
                $data[$key]['areaNames'] = $this->areaModel->getAreaNames($items['provinceId'] . ',' . $items['cityId'] . ',' . $items['districtId']);
            }
        }
        
        //显示分页
        $pagination = $this->showPagination($total);
        
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
        $this->getView()->assign('post', $post);
    }

    
    public function editAction(){
        $this->_layout = true;
        $shopId = $this->getParam('shopId',0);        
        $shopInfo = $this->model->getShopInfo($shopId);
        if(empty($shopInfo)){
            $this->redirect("/$this->_ModuleName/$this->_ControllerName/index");
        }
        $rules = $this->model->getRules();
        if($this->isPost()){
            $post = $this->getPost();
            $post['shopId']=$shopId;
            $v = new validation(); //数据校验
            $v->validate($rules, $post);
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
                if ($this->isAjax()) {
                    $this->err('', $v->error_message); //输出异步错误信息
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
        //保存数据 begin
        $saveR = $this->model->$action($data);
        
        $_eventUrl = "/$this->_ModuleName/$this->_ControllerName/index";

        if($saveR){
            //保存成功跳转到列表页            
            if($this->isAjax()){
                $this->ok(null, $_eventUrl, '保存成功！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存成功！','_eventUrl'=>$_eventUrl));
//                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index');
            }
        }else{
            if($this->isAjax()){
                $this->ok(null, $_eventUrl, '保存失败！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存失败！','_eventUrl'=>$_eventUrl));
//                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index');
            }
            
        }
    }
    
}
