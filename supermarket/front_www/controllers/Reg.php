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


}
