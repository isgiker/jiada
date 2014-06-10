<?php

/**
 * @name AdmoduleController
 * @author Vic
 * @desc 广告模块控制器
 */
class AdmoduleController extends Core_Controller_Admin {
    
    protected $model;

    public function init() {
        parent::init();
        $this->model = new Default_AdmoduleModel();
    }
    
    /**
     * 广告模块列表
     */
    public function indexAction(){
        $this->_layout = true;
        $post = $this->getPost();
        if ($post && $post['jsubmit']) {
            switch ($post['jsubmit']) {
                case 'search':

                    $data = $this->model->getAdmoduleList($post);
                    $total = (int) $this->model->getAdmoduleTotal($post);

                    break;
            }
        } else {
            $data = $this->model->getAdmoduleList();            
            $total = (int) $this->model->getAdmoduleTotal();
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
            

        }
        
        
        
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
    }
    
    public function editAction(){
        $this->_layout = true;
        $admId = $this->getParam('admId',0);
        $item = $this->model->getAdmoduleInfo($admId);
        if(empty($item)){
            $this->redirect("/{$this->_ModuleName}/{$this->_ControllerName}/index");
        }
        $rules = $this->model->getRules();
        if($this->isPost()){
            $post = $this->getPost();
            $post['admId']=$admId;

            $v = new validation(); //数据校验
            $v->parentCls=$this;
            $v->validate($rules, $post);
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
                if ($this->isAjax()) {
                    $this->err('', $v->error_message); //输出异步错误信息
                }
            }else{
                $this->saveAction($post, 'edit');
            }
            
            $item=$post;
        }
        
      
        
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('item', $item);
    }
    
    public function ajax_adminfoAction() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $admId = $this->getParam('admId',0);
        $item = $this->model->getAdmoduleInfo($admId);
        $data = json_encode($item);
        echo $data;
        exit;
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
        if($saveR){
            //保存成功跳转到列表页            
            if($this->isAjax()){
                $this->ok(null, '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index', '保存成功！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存成功！','_eventUrl'=>'/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index'));
//                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
            }
        }else{
            if($this->isAjax()){
                $this->ok(null, '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index', '保存失败！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存失败！','_eventUrl'=>'/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index'));
//                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
            }
            
        }
    }
    

}
