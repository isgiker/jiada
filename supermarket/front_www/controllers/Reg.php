<?php

/**
 * @name IndexController
 * @desc 注册控制器
 * @author Vic Shiwei
 */
class RegController extends Core_Controller_Www {
    protected $model;
    public function init() {
        parent::init();
        $this->model = new RegModel();
        $this->areaModel = new AreaModel();
    }
    
    public function indexAction() {
        $this->_layout = false;
        $rules = $this->model->getRules();
        $post = $this->getPost();
        if($this->isPost()){
            $v = new validation(); //数据校验
            $v->parentCls=$this;
            $v->validate($rules, $post);
            
            //检验用户名和邮箱是否有人注册
            $email_exists=$this->model->checkEmail($post['email']);
            if($email_exists){
                if(!isset($v->error_message['email']) || !$v->error_message['email']){
                    $v->error_message['email']='该邮箱已有人注册！';
                }
            }
            $uname_exists=$this->model->checkUserName($post['nickname']);
            if($uname_exists){
                if(!isset($v->error_message['nickname']) || !$v->error_message['nickname']){
                    $v->error_message['nickname']='该昵称已有人注册！';
                }
            }
            
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
                $this->getView()->assign("post", $post);
                if ($this->isAjax()) {
                    $this->err(null, $v->error_message); //输出异步错误信息
                }
            }else{
                $this->saveAction($post, 'reg');
            }
            
            if(isset($post['provinceId']) && $post['provinceId']){
                $city = $this->areaModel->getNodeArea($post['provinceId']);
                $this->getView()->assign("city", $city);
            }
            if(isset($post['cityId']) && $post['cityId']){
                $district = $this->areaModel->getNodeArea($post['cityId']);            
                $this->getView()->assign("district", $district);
            }
            if(isset($post['districtId']) && $post['districtId']){
                $community = $this->areaModel->getNodeArea($post['districtId']);            
                $this->getView()->assign("community", $community);
            }
        }
        
        //获取省份
        $province = $this->areaModel->getNodeArea(0);
        
        
        $this->getView()->assign("province", $province);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
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
        
        $_event_success_Url = "http://chaoshi.jiada.local";
        $_event_fail_Url = "/$this->_ModuleName/$this->_ControllerName/reg";
        if($saveR){
            //如果是注册成功，则发送激活邮件
            if($action=='reg'){
                
            }
            
            //保存成功跳转到列表页            
            if($this->isAjax()){
                $this->ok(null, $_event_success_Url, '保存成功！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存成功！','_eventUrl'=>$_event_success_Url));
            }
        }else{
            if($this->isAjax()){
                $this->ok(null, $_event_fail_Url, '保存失败！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存失败！','_eventUrl'=>$_event_fail_Url));
            }
            
        }
    }


}
