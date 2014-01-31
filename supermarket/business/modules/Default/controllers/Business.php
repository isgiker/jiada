<?php

/**
 * @name StorehouseController
 * @author Vic
 * @desc 商家控制器
 */
class BusinessController extends Core_Controller_Admin {
    
    protected $model;

    public function init() {
        parent::init();
        $this->model = new Default_BusinessModel();
        $this->areaModel = new Default_AreaModel();
    }
    
    /**
     * 
     */
    public function indexAction(){
        $this->_layout = true;
        $post = $this->getPost();
        if ($post && $post['jsubmit']) {
            switch ($post['jsubmit']) {
                case 'search':

                    $data = $this->model->getBusinessList($post);
                    $total = (int) $this->model->getBusinessTotal($post);

                    break;
            }
        } else {
            $data = $this->model->getBusinessList();            
            $total = (int) $this->model->getBusinessTotal();
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
            
            $city = $this->areaModel->getAreas($post['provinceId']);
            $district = $this->areaModel->getAreas($post['cityId']);
            $this->getView()->assign("city", $city);
            $this->getView()->assign("district", $district);
        }
        
        //获取省份
        $province = $this->areaModel->getAreas(0);
        
        
        $this->getView()->assign("province", $province);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
    }
    
    public function editAction(){
        $this->_layout = true;
        $businessId = $this->getParam('businessId',0);
        $this->businessInfo = $businessInfo = $this->model->getBusinessInfo($businessId);
        if(empty($businessInfo)){
            $this->redirect('/Default/Business/index');
        }
        $rules = $this->model->getRules();
        if($this->isPost()){
            $post = $this->getPost();
            $post['businessId']=$businessId;
            if(!trim($post['password'])){
                unset($post['password']);
            }
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
            
            $businessInfo=$post;
        }
        
        //获取省/市/区县
        $province = $this->areaModel->getAreas(0);
        $city = $this->areaModel->getAreas($businessInfo['provinceId']);
        $district = $this->areaModel->getAreas($businessInfo['cityId']);
        
        $this->getView()->assign("province", $province);
        $this->getView()->assign("city", $city);
        $this->getView()->assign("district", $district);
        
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('businessInfo', $businessInfo);
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
                $this->getView()->assign("_event", array('_eventMsg'=>'保存成功！','_eventUrl'=>'/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index'));
//                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
            }
            
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
     * validation会调用这个函数，验证某个值是否唯一;
     * 检查商家登录用户名是否唯一
     * @param string $fieldValue 需要验证的input name的值
     * @return boolean 根据validation的规则，返回false代表有错误，返回true代表通过
     */
    public function checkFieldAction($fieldValue=null){
        //判断调用方式: true页面请求方式; false为函数内调用
        $flag=false;
        if ($fieldValue===null){
            $flag=true;
            //
            $fieldValue='input content';
        }
        //如果是edit action，用户名和原有用户名值相同不需要check是否唯一；
        if(isset($this->businessInfo)){
            if($this->businessInfo['userName'] == $fieldValue){
                //不作任何处理;
                return true;
            }
        }
        
        $result=$this->model->checkUsername($fieldValue);
        if ($flag) {
                //网页请求方式使用echo函数
                //false:用户名已经存在，true:输入的用户名可用
                if ($result) {
                    echo('false');
                    exit();
                } else {
                    echo('true');
                    exit();
                }
            exit;
        }else{
                //函数内部使用return函数,如果该值已存在返回false;
                if ($result) {
                    return false;
                } else {
                    return true; 
                }
        }
    }
    

}
