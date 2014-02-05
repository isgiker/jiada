<?php

/**
 * @name StorehouseController
 * @author Vic
 * @desc 地区控制器
 */
class StorehouseController extends Core_Controller_Admin {
    
    protected $model;

    public function init() {
        parent::init();
        $this->model = new Default_StorehouseModel();
    }
    
    /**
     * 区域列表是无限极分类结构，不能进行模糊搜索和查看未公布状态；原因和parentId有关。
     */
    public function indexAction(){
        $this->_layout = true;
        $data = $this->model->getStorehouseList();        
        $total = (int) $this->model->getStorehouseTotal();
        
        //显示分页
        $pagination = $this->showPagination($total);
        
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);

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

        }
        
        //获取省份
        $province = $this->model->getAreas(0);
        
        $this->getView()->assign("province", $province);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
    }
    
    public function editAction(){
        $this->_layout = true;
        $storehouseId = $this->getParam('storehouseId',0);        
        $storehouseInfo = $this->model->getStorehouseInfo($storehouseId);
        if(empty($storehouseInfo)){
            $this->redirect("/$this->_ModuleName/Storehouse/index");
        }
        $rules = $this->model->getRules();
        if($this->isPost()){
            $post = $this->getPost();
            $post['storehouseId']=$storehouseId;
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
            
            $storehouseInfo=$post;
        }
        
        //获取省/市/区县
        $province = $this->model->getAreas(0);
        $city = $this->model->getAreas($storehouseInfo['provinceId']);
        $district = $this->model->getAreas($storehouseInfo['cityId']);
        
        $this->getView()->assign("province", $province);
        $this->getView()->assign("city", $city);
        $this->getView()->assign("district", $district);
        
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('storehouseInfo', $storehouseInfo);
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
            $this->ok(null, '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index', '保存成功！');
        }else{
            $this->ok(null, '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index', '保存失败！');
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
    

}
