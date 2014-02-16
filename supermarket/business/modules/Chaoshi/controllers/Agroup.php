<?php

/**
 * @name AgroupController
 * @author Vic Shiwei
 * @desc 用户组，每个商家都可以自定义用户组，暂不支持每个店铺自定义用户组。
 */
class AgroupController extends Core_Controller_Business {
    
    protected $model;

    public function init() {
        parent::init();
        $this->model = new Chaoshi_AgroupModel();
    }
    
    /**
     * 区域列表是无限极分类结构，不能进行模糊搜索和查看未公布状态；原因和parentId有关。
     */
    public function indexAction(){
        $this->_layout = true;
        $post = $this->getPost();
        $post['businessId'] = $this->currentBusinessId;
        if($this->isPost() && $post && $post['jsubmit']){
            switch ($post['jsubmit']) {
                case 'search':
                    
                    $data = $this->model->getAgroupList($post);        
                    $total = (int) $this->model->getAgroupTotal($post);       

                    break;
            }
        }else{
            $data = $this->model->getAgroupList($post);
            $total = (int) $this->model->getAgroupTotal($post);
        }

        //显示分页
        $pagination = $this->showPagination($total);
        
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
        $this->getView()->assign('post', $post);
    }

    /**
     * 多个店铺共用用户组，用户组目前只关联商家id。只有商家账号可以自定义用户组，日后如果有需求再考虑每个店铺自定义用户组。
     */
    public function addAction() {
        $this->_layout = true;
        $rules = $this->model->getRules();
        $post = $this->getPost();
        if($this->isPost()){
            $post['businessId']=$this->currentBusinessId;
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
            $treeAgroup = $this->model->getTreeAgroup($post['parentId']);
        }else{
            $treeAgroup = $this->model->getTreeAgroup();
        }
        
        //获取权限资源文件
        $currentIndustry = strtolower($this->currentIndustry);
        $resourcConfig = new Yaf_Config_Ini(CONFIG_PATH . DS . 'resourc'.DS.'business_'.$currentIndustry.'.ini');
        $this->getView()->assign('resourcConfig', $resourcConfig);
        $this->getView()->assign('treeAgroup', $treeAgroup);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
    }
    
    public function editAction(){
        $this->_layout = true;
        $agroupId = $this->getParam('agroupId',0);
        $agroupInfo = $this->model->getAgroupInfo($agroupId);
        if(empty($agroupInfo)){
            $this->redirect("/$this->_ModuleName/Agroup/index");
        }
        $rules = $this->model->getRules();        
        if($this->isPost()){
            $post = $this->getPost();
            $post['businessId']=$this->currentBusinessId;
            $post['agroupId']=$agroupId;
            
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
            
            $agroupInfo=$post;
        }
        
        $treeAgroup = $this->model->getTreeAgroup($agroupInfo['parentId']);
        
        //获取权限资源文件
        $currentIndustry = strtolower($this->currentIndustry);
        $resourcConfig = new Yaf_Config_Ini(CONFIG_PATH . DS . 'resourc'.DS.'business_'.$currentIndustry.'.ini');
        $this->getView()->assign('resourcConfig', $resourcConfig);
        $this->getView()->assign('treeAgroup', $treeAgroup);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('agroupInfo', $agroupInfo);        
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
