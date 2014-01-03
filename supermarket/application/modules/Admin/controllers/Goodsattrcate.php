<?php

/**
 * @name GoodscateController
 * @desc 商品属性分类
 * @author Vic Shi
 */
class GoodsattrcateController extends Core_Basic_Controllers {
    
    protected $model;

    public function init() {
        $this->getView()->assign('_view', $this->getView());
        $this->model = new Admin_GoodsattrcateModel();
    }
    
    /**
     * 商品属性分类列表,商品属性和属性分类都是基于某个商品类型的；
     * @param int $goodsCateId 商品分类id，这是必要参数；
     * @return array 数组
     */
    public function indexAction(){
        $this->_layout = true;
        //得到商品类型id
        $goodsCateId= $this->getParam('cateId');
        if(!$goodsCateId){
            $this->redirect('/admin/goodscate/index');
        }
        //得到商品类型的信息
        $gcateInfo = $this->model->getGcateInfo($goodsCateId);
        if(empty($gcateInfo)){
            $this->redirect('/admin/goodscate/index');
        }
        
        //根据商品类型id获取商品属性分类
        $data = $this->model->getGattrcateList($goodsCateId);
        $total = (int) $this->model->getGattrcateTotal($goodsCateId);
        
        //显示分页
        $pagination = $this->showPagination($total);
        
        //模板变量
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
        $this->getView()->assign('goodsCateId', $goodsCateId);
        $this->getView()->assign('gcateInfo', $gcateInfo);
    }

    /**
     * 添加商品属性分类，次事件是由ajax触发；
     * @param int $goodsCateId 商品分类id，这是必要参数；
     * @return json
     */    
    public function addAction() {
        $this->_layout = true;
        $rules = $this->model->getRules();
        if($this->isPost()){
            $post = $this->getPost();
            //数据校验
            $v = new validation();
            $v->validate($rules, $post);

            if (!empty($v->error_message)) {
                //输出同步错误信息
                $this->getView()->assign("error", $v->error_message);
                if ($this->isAjax()) {
                    //输出异步错误信息
                    $this->err('', $v->error_message);
                }
            }else{
                $this->saveAction($post, 'add');
            }
            
        }
        //获取商品类型id
        $goodsCateId = $this->getParam('cateId',0);
        
        //获取商品类型信息
        $gcateInfo = $this->model->getGcateInfo($goodsCateId);
        if(empty($gcateInfo)){
            $this->redirect('/admin/goodscate/index');
        }
        //模板
        $this->getView()->assign('gcateInfo', $gcateInfo);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('goodsCateId', $goodsCateId);
    }
   
    /**
     * 编辑商品属性分类，次事件是由ajax触发；
     * @param int $attrCateId 商品属性分类id，这是必要参数；
     * @return json
     */
    public function editAction(){
        $this->_layout = true;
        $attrCateId = $this->getParam('attrCateId',0);
        //获取商品属性分类信息
        $attrcateInfo = $this->model->getGattrcateInfo($attrCateId);
        if(empty($attrcateInfo)){
            $this->redirect('/admin/goodsattrcate/index');
        }
        
        //获取商品类型信息
        $gcateInfo = $this->model->getGcateInfo($attrcateInfo['goodsCateId']);
      
        $rules = $this->model->getRules();
        if($this->isPost()){
            $post = $this->getPost();
            //把id加入post数组
            $post['attrCateId'] = $attrCateId;
            
            //数据校验
            $v = new validation(); 
            $v->validate($rules, $post);

            if (!empty($v->error_message)) {
                //输出同步错误信息
                $this->getView()->assign("error", $v->error_message);
                if ($this->isAjax()) {
                    //输出异步错误信息
                    $this->err('', $v->error_message);
                }
            }else{
                //保存数据
                $this->saveAction($post, 'edit');
            }
            
        }

        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('attrcateInfo', $attrcateInfo);
        $this->getView()->assign('gcateInfo', $gcateInfo);
    }
    
    public function delAction(){
        $post = $this->getPost();
        if(!isset($post['checkIds']) || !is_array($post['checkIds']) || !$post['checkIds']){
            $this->err(null, null, '请选择要删除的项目！');
        }
        $cateId = $this->getParam('cateId');
        if(!$cateId){
            $this->err(null, null, '商品类型不能为空！');            
        }
        $post['goodsCateId'] = $cateId;
        $post['success_msg'] = '删除成功！';
        $post['error_msg'] = '删除失败！';
        $this->saveAction($post, 'del');
    }
    
    public function saveAction($data,$action){
        if(!$data || !$action){
            $this->err(null, null, '数据不能为空！');
        }
        $saveR = $this->model->$action($data);
        if ($saveR) {
            isset($data['success_msg']) && $data['success_msg']? $success_msg = $data['success_msg'] : $success_msg = '保存成功！';
            //保存成功跳转到列表页
            $this->ok(null, '/admin/goodsattrcate/index/cateId/'.$data['goodsCateId'], $success_msg);
        } else {
            isset($data['error_msg']) && $data['error_msg']? $error_msg = $data['error_msg'] : $error_msg = '保存失败！';
            //返回来源地址;
            $this->err(null, null, $error_msg);
        }
    }
    
}
