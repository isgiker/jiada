<?php

/**
 * @name GoodscateController
 * @author Vic Shiwei
 * @desc 商品品牌
 */
class GoodsbrandController extends Core_Controller_Admin {
    
    protected $model;

    public function init() {
        parent::init();
        $this->model = new Chaoshi_GoodsbrandModel();
    }
    
    /**
     * 根据商品类型id和商品类型所属分类查看品牌。
     */
    public function indexAction(){
        $this->_layout = true;
        //商品类型id
        $cateTypeId = $this->getParam('cateTypeId',0);
        //商品类型所属分类id
        $cateParentId = $this->getParam('cateParentId',0);
        
        
        $request = $this->_getRequest();
        if($request && isset($request['jsubmit']) && $request['jsubmit']){
            switch ($request['jsubmit']) {
                case 'search':
                    
                    $data = $this->model->getGbrandList($cateParentId,$request);
                    $total = (int) $this->model->getGbrandTotal($cateParentId,$request);       

                    break;
            }
        }else{
            $data = $this->model->getGbrandList($cateParentId, array('gcateChildren'=>$cateTypeId));
            $total = (int) $this->model->getGbrandTotal($cateParentId, array('gcateChildren'=>$cateTypeId));
        }
        foreach($data as $k => $v){
            $data[$k]['childCateType'] = $this->getCateName($data[$k]['childCateType']);
            $data[$k]['cateName'] = $this->getCateName($data[$k]['cateId']);
        }
        
        //获取当前商品类型和所属分类的全部子类
        $gcateModel=new Chaoshi_GoodscateModel();
        $gcateChildren = $gcateModel->getGcateChildren($cateParentId, true);
        
        //显示分页
        $pagination = $this->showPagination($total);
        
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
        $this->getView()->assign('request', $request);
        $this->getView()->assign('cateParentId', $cateParentId);
        $this->getView()->assign('cateTypeId', $cateTypeId);
        $this->getView()->assign('gcateChildren', $gcateChildren);
    }

    
    public function addAction() {
        $this->_layout = true;
        //商品类型id
        $cateTypeId = $this->getParam('cateTypeId',0);
        //商品类型所属分类id
        $cateParentId = $this->getParam('cateParentId',0);
        
        $gcateInfo = $this->model->getGcateInfo($cateParentId);
        if(empty($gcateInfo)){
            $this->redirect("/$this->_ModuleName/Goodscate/index");
        } 
        
        //获取该商品类型所属分类下的所有商品类型数据；
        $childCateType = $this->model->childCateType($cateParentId);
        
        $rules = $this->model->getRules();
        $post = $this->getPost();
        if($this->isPost()){
            //防篡改商品分类id
            $post['cateId']=$cateParentId;
            
            //数组的验证需要转换成字符串
            if(isset($post['childCateType']) && is_array($post['childCateType'])){
                $post['childCateType'] = implode('|', $post['childCateType']);
            }else{
                $post['childCateType'] = '';
            }
            
            $v = new validation(); //数据校验
            $v->validate($rules, $post);
            //把字符串还原数组
            $post['childCateType'] = explode('|', $post['childCateType']);
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
        
        $this->getView()->assign('childCateType', $childCateType);
        $this->getView()->assign('gcateInfo', $gcateInfo);
        $this->getView()->assign('cateTypeId', $cateTypeId);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
    }
    
    public function editAction(){
        $this->_layout = true;
        $brandId = $this->getParam('brandId',0);
        //获取品牌信息
        $gbrandInfo = $this->model->getGbrandInfo($brandId);
        if(empty($gbrandInfo)){
            $this->redirect("/$this->_ModuleName/Goodsbrand/index");
        }
        $gbrandInfo['childCateType'] = explode(',', $gbrandInfo['childCateType']);
        //获取品牌的分类信息
        $gcateInfo = $this->model->getGcateInfo($gbrandInfo['cateId']);

        //商品分类下的子分类，也即商品类型；
        $childCateType = $this->model->childCateType($gbrandInfo['cateId']);
        
        $rules = $this->model->getRules();        
        if($this->isPost()){
            $post = $this->getPost();
            //防篡改品牌id
            $post['brandId']=$brandId;
            
            //防篡改商品分类id
            $post['cateId']=$gbrandInfo['cateId'];
            
            //数组的验证需要转换成字符串
            if(isset($post['childCateType']) && is_array($post['childCateType'])){
                $post['childCateType'] = implode('|', $post['childCateType']);
            }else{
                $post['childCateType'] = '';
            }
            
            $v = new validation(); //数据校验
            $v->validate($rules, $post);
            //把字符串还原数组
            if(isset($post['childCateType']) && $post['childCateType']){
                $post['childCateType'] = explode('|', $post['childCateType']);
            }
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
//                $gbrandInfo = $post;
                if ($this->isAjax()) {
                    $this->err('', $v->error_message); //输出异步错误信息
                }
                
            }else{
                $this->saveAction($post, 'edit');
            }            
            
        }

        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('gbrandInfo', $gbrandInfo);
        $this->getView()->assign('gcateInfo', $gcateInfo);
        $this->getView()->assign('childCateType', $childCateType);
    }
    
    public function saveAction($data,$action){
        if(!$data || !$action){            
            $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
        }
        $saveR = $this->model->$action($data);
        $_eventUrl = "/$this->_ModuleName/$this->_ControllerName/index/cateParentId/$data[cateId]";
        if($saveR){
            //保存成功跳转到列表页
            $this->ok(null, $_eventUrl, '保存成功！');
        }else{
            //返回来源地址;
            $this->ok(null, $_eventUrl, '保存失败！');
        }
    }
    
    /**
     * 获取分类节点路径名称
     */
    public function getCateName($cateIds){
        if(!trim($cateIds)){
            return '';
        }
        $model = new Chaoshi_GoodscateModel();
        $cateName = $model->getCatePathName($cateIds);
        $newArr = array();
        if($cateName && is_array($cateName)){
            foreach ($cateName as $key => $value) {
                $newArr[] = $value['cateName'];
            }
            $result = implode(',', $newArr);
        }else{
            $result = '';
        }

        return $result;
    }
    
    /**
     * ajax接口；获取分类下的所有品牌
     */
    public function getCateBrandAction(){
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $cateId = $this->getParam('cateId');
        $brands = $this->model->getCateBrand($cateId);
        $data = json_encode($brands);
        echo $data;
        exit;
    }
    
    /**
     * 根据商品类型获取所有品牌
     * @param int $cateId 分类id
     * @return boolean
     */
    public function gcatebrandAction($cateId) {
        //根据商品分类（商品类型）获取所有品牌
        if (!$cateId) {
            if(!$cateId = $this->getParam('cateId')){
                return false;
            }
        }
        
        //获取数据
        $cateBrand = $this->model->getCateBrand($cateId);
        if ($this->isAjax()) {
            $data = json_encode($cateBrand);
            echo $data;
            exit;
        } else {
            return $cateBrand;
        }
        
    }

    

}
