<?php

/**
 * @name GoodscateController
 * @author Vic
 * @desc 商品品牌
 */
class GoodsattrController extends Core_Basic_Controllers {
    
    protected $model;

    public function init() {
        $this->getView()->assign('_view', $this->getView());
        $this->model = new Admin_GoodsattrModel();
    }
    
    /**
     * 
     */
    public function indexAction(){
        $this->_layout = true;
        $goodsCateId = $this->getParam('cateId');
        if(!$goodsCateId){
            $this->redirect('/Admin/Goodscate/index');
        }
        
        $data = $this->model->getGattrList($goodsCateId);
        $total = (int) $this->model->getGattrTotal($goodsCateId);
        
        //显示分页
        $pagination = $this->showPagination($total);
        
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
        $this->getView()->assign('goodsCateId', $goodsCateId);
    }

    
    public function addAction() {
        $this->_layout = true;
        $rules = $this->model->getRules();
        
        if($this->isPost()){
            $post = $this->getPost();
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
        
        $goodsCateId = $this->getParam('cateId');
        if(!$goodsCateId){
            $this->redirect('/Admin/Goodscate/index');
        }
        $gcateInfo = $this->model->getGcateInfo($goodsCateId);
        if(empty($gcateInfo)){
            $this->redirect('/Admin/Goodscate/index');
        }
        
        //获取商品属性分类
        $goodsAttrCate = $this->model->getGattrCate($goodsCateId);
        
        
        
        $this->getView()->assign('goodsAttrCate', $goodsAttrCate);
        $this->getView()->assign('gcateInfo', $gcateInfo);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('goodsCateId', $goodsCateId);
        
    }
    
    
    
    public function editAction(){
        $this->_layout = true;
        $attrId = $this->getParam('attrId',0);
        
        $rules = $this->model->getRules(); 
        if($this->isPost()){
            $post = $this->getPost();
            //把id加入post数组
            $post['attrId'] = $attrId;
            
            $v = new validation(); //数据校验
            $v->validate($rules, $post);

            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息                
                $gAttrInfo=$post;
                if ($this->isAjax()) {
                    $this->err('', $v->error_message); //输出异步错误信息
                }
            }else{
                $this->saveAction($post, 'edit');
            }            
            
        }
        
        
        //获取商品属性信息
        $gAttrInfo = $this->model->getGattrInfo($attrId);
        if(empty($gAttrInfo)){
            $this->redirect('/Admin/Goodsattr/index');
        }
        
        //获取分类信息
        $gcateInfo = $this->model->getGcateInfo($gAttrInfo['goodsCateId']);
        if(empty($gcateInfo)){
            $this->redirect('/Admin/Goodsattr/index');
        }
        
         //获取商品属性分类
        $goodsAttrCate = $this->model->getGattrCate($gAttrInfo['goodsCateId']);        

        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('gAttrInfo', $gAttrInfo);
        $this->getView()->assign('gcateInfo', $gcateInfo);
        $this->getView()->assign('goodsAttrCate', $goodsAttrCate);
    }
    
    public function saveAction($data,$action){
        if(!$data || !$action){            
            $this->err(null, null, '数据不能为空！');
        }
        $saveR = $this->model->$action($data);
        if ($saveR) {
            //保存成功跳转到列表页
            $this->ok(null, '/admin/goodsattr/index/cateId/'.$data['goodsCateId'], '保存成功');
        } else {
            //返回来源地址;
            $this->err(null, null, '保存失败');
        }
    }
    
}
