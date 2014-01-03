<?php

/**
 * @name GoodscateController
 * @author Vic
 * @desc 商品品牌
 */
class GoodsbrandController extends Core_Basic_Controllers {
    
    protected $model;

    public function init() {
        $this->getView()->assign('_view', $this->getView());
        $this->model = new Admin_GoodsbrandModel();
    }
    
    /**
     * 区域列表是无限极分类结构，不能进行模糊搜索和查看未公布状态；原因和parentId有关。
     */
    public function indexAction(){
        $this->_layout = true;
        $cateId = $this->getParam('cateId');
        $post = $this->getPost();
        if($post && $post['jsubmit']){
            switch ($post['jsubmit']) {
                case 'search':
                    
                    $data = $this->model->getGbrandList($cateId,$post);        
                    $total = (int) $this->model->getGbrandTotal($cateId,$post);       

                    break;
            }
        }else{
            $data = $this->model->getGbrandList($cateId);        
            $total = (int) $this->model->getGbrandTotal($cateId);
        }
        foreach($data as $k => $v){
            $data[$k]['childCateType'] = $this->getCateName($data[$k]['childCateType']);
            $data[$k]['cateName'] = $this->getCateName($data[$k]['cateId']);
        }
        
        //显示分页
        $pagination = $this->showPagination($total);
        
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
        $this->getView()->assign('post', $post);
        $this->getView()->assign('cateId', $cateId);
    }

    
    public function addAction() {
        $this->_layout = true;
        $cateId = $this->getParam('cateId',0);        
        $gcateInfo = $this->model->getGcateInfo($cateId);
        //商品分类下的子分类，也即商品类型；
        $childCateType = $this->model->childCateType($cateId);
        if(empty($gcateInfo)){
            $this->redirect('/admin/goodscate/index');
        }
        
        $rules = $this->model->getRules();
        $post = $this->getPost();
        if($this->isPost()){
            //数组的验证需要转换成字符串
            if($post['childCateType'] && is_array($post['childCateType'])){
                $post['childCateType'] = implode('|', $post['childCateType']);
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
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
    }
    
    public function editAction(){
        $this->_layout = true;
        $brandId = $this->getParam('brandId',0);
        //获取品牌信息
        $gbrandInfo = $this->model->getGbrandInfo($brandId);
        if(empty($gbrandInfo)){
            $this->redirect('/admin/goodsbrand/index');
        }
        $gbrandInfo['childCateType'] = explode(',', $gbrandInfo['childCateType']);
        //获取品牌的分类信息
        $gcateInfo = $this->model->getGcateInfo($gbrandInfo['cateId']);

        //商品分类下的子分类，也即商品类型；
        $childCateType = $this->model->childCateType($gbrandInfo['cateId']);
        
        $rules = $this->model->getRules();        
        if($this->isPost()){
            $post = $this->getPost();
            //数组的验证需要转换成字符串
            if(isset($post['childCateType']) && is_array($post['childCateType'])){
                $post['childCateType'] = implode('|', $post['childCateType']);
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
        if($saveR){
            //保存成功跳转到列表页
            $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index');
        }else{
        //返回来源地址;
            $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action.'/cateId/'.$data['cateId']);
        }
    }
    
    /**
     * 获取分类节点路径名称
     */
    public function getCateName($cateIds){
        if(!trim($cateIds)){
            return '';
        }
        $model = new Admin_GoodscateModel();
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

    

}
