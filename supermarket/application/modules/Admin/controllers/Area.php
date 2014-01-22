<?php

/**
 * @name AreaController
 * @author Vic
 * @desc 地区控制器
 */
class AreaController extends Core_Basic_Controllers {
    
    protected $model;

    public function init() {
        $this->getView()->assign('_view', $this->getView());
        $this->model = new Admin_AreaModel();        
    }
    
    /**
     * 区域列表是无限极分类结构，不能进行模糊搜索和查看未公布状态；原因和parentId有关。
     */
    public function indexAction(){
        $this->_layout = true;
        $post = $this->getPost();
        if($post && $post['jsubmit']){
            switch ($post['jsubmit']) {
                case 'search':
                    
                    $data = $this->model->getAreaList($post);        
                    $total = (int) $this->model->getAreaTotal($post);       

                    break;
            }
        }else{
            $data = $this->model->getAreaList();        
            $total = (int) $this->model->getAreaTotal();
        }
        

        //显示分页
        $pagination = $this->showPagination($total);
        
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
        $this->getView()->assign('post', $post);
    }

    
    public function addAction() {
        $this->_layout = true;
        $rules = $this->model->getRules();
        $post = $this->getPost();
        if($this->isPost()){
            $v = new validation(); //数据校验
            $v->validate($rules, $post);
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
                $this->getView()->assign("post", $post);
                if ($this->isAjax()) {
                    $this->err('', $v->error_message); //输出异步错误信息
                }
            }else{
                $this->saveAction($post, 'add');
            }            
            $treeArea = $this->model->getTreeArea($post['parentId']);
        }else{
            $treeArea = $this->model->getTreeArea();
        }
        
        $this->getView()->assign('treeArea', $treeArea);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
    }
    
    public function editAction(){
        $this->_layout = true;
        $areaid = $this->getParam('areaId',0);        
        $areaInfo = $this->model->getAreaInfo($areaid);
        if(empty($areaInfo)){
            $this->redirect('/admin/area/index');
        }
        $rules = $this->model->getRules();        
        if($this->isPost()){
            $post = $this->getPost();
            $post['areaId']=$areaid;
            
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
            
            $areaInfo=$post;
        }
        
        $treeArea = $this->model->getTreeArea($areaInfo['parentId']);
        $this->getView()->assign('treeArea', $treeArea);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('areaInfo', $areaInfo);
    }
    
    public function saveAction($data,$action){
        if(!$data || !$action){
            $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
        }
        $saveR = $this->model->$action($data);
        if($saveR){
            //保存成功跳转到列表页
            $this->ok(null, '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index', '保存成功！');
        }else{
            $this->ok(null, '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index', '保存失败！');
        }
    }

    

}
