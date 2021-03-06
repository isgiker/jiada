<?php

/**
 * @name AreaController
 * @author Vic
 * @desc 地区控制器
 */
class AreaController extends Core_Controller_Admin {
    
    protected $model;
    private $areaType = array('Province'=>'省','City'=>'市','District'=>'区县','Community'=>'小区','Hotspot'=>'商圈');
    public function init() {
        parent::init();
        $this->model = new Default_AreaModel();        
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
        $this->getView()->assign('areaType', $this->areaType);
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
        $this->getView()->assign('areaType', $this->areaType);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
    }
    
    public function editAction(){
        $this->_layout = true;
        $areaid = $this->getParam('areaId',0);        
        $areaInfo = $this->model->getAreaInfo($areaid);
        if(empty($areaInfo)){
            $this->redirect("/$this->_ModuleName/Area/index");
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
        $this->getView()->assign('areaType', $this->areaType);
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
            isset($data['error_msg']) && $data['error_msg']? $error_msg = $data['error_msg'] : $error_msg = '保存失败！';
            //返回来源地址;
            $this->err(null, null, $error_msg);
        }
    }
    
    /**
     * ajax onchang事件联动获取市、区
     */
    public function ajaxAreaAction() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $areaid = $this->getParam('areaId');
        if(!$areaid){
            return false;
        }
        $areaList = $this->model->getAreas($areaid);
//        $areaStr = '';
//        foreach($areaList as $area){
//            $areaStr .= '<option value="'.$area['areaId'].'">'.$area['areaName'].'</option>';
//        }
//        return $areaStr;
        $data = json_encode($areaList);
        echo $data;
        exit;
    }
    
    /**
     * 获取地区的子分类,支持ajax http和函数形式调用;
     * 根据parentPath参数判断该分类是第几级分类。
     * @param int $parentId 分类的父级节点id
     */
    public function nodeAreaAction($parentId = '0') {
//        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        //根据分类id获取所有子类
        if ($this->isAjax()) {
            $areaId = $this->getParam('areaId');
            if ($areaId) {
                $parentId = $areaId;
            }else{
                $parentId = '';
                return FALSE;
            }
        }
        //获取数据        
        $nodeGcate = $this->model->getNodeArea($parentId);
        if ($this->isAjax()) {
            $data = json_encode($nodeGcate);
            echo $data;
            exit;
        } else {
            return $nodeGcate;
        }
    }

    

}
