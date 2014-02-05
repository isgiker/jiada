<?php

/**
 * @name AdminController
 * @author Vic Shiwei
 * @desc 管理员
 */
class AdminController extends Core_Controller_Admin {
    
    protected $model;

    public function init() {
        parent::init();
        $this->model = new Default_AdminModel();
        $this->agroupModel = new Default_AgroupModel();
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
                    
                    $data = $this->model->getAdminList($post);        
                    $total = (int) $this->model->getAdminTotal($post);       
                    //获取用户组
                    $treeAgroup = $this->agroupModel->getTreeAgroup($post['agroupId']);
                    break;
            }
        }else{
            $data = $this->model->getAdminList();        
            $total = (int) $this->model->getAdminTotal();
            $treeAgroup = $this->agroupModel->getTreeAgroup();
        }
        

        //显示分页
        $pagination = $this->showPagination($total);
        
        
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
        $this->getView()->assign('post', $post);
        $this->getView()->assign('treeAgroup', $treeAgroup);
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
            $treeAgroup = $this->agroupModel->getTreeAgroup($post['parentId']);
        }else{
            $treeAgroup = $this->agroupModel->getTreeAgroup();
        }

        $this->getView()->assign('treeAgroup', $treeAgroup);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
    }
    
    public function editAction(){
        $this->_layout = true;
        $adminId = $this->getParam('adminId',0);
        $adminInfo = $this->model->getAdminInfo($adminId);
        if(empty($adminInfo)){
            $this->redirect("/$this->_ModuleName/Admin/index");
        }
        $rules = $this->model->getRules();        
        if($this->isPost()){
            $post = $this->getPost();
            $post['adminId']=$adminId;
            if(!trim($post['password'])){
                unset($post['password']);
            }
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
            
            $adminInfo=$post;
        }
        
        $treeAgroup = $this->agroupModel->getTreeAgroup($adminInfo['agroupId']);
        
        $this->getView()->assign('treeAgroup', $treeAgroup);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('adminInfo', $adminInfo);
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
