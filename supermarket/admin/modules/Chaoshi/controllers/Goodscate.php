<?php

/**
 * @name GoodscateController
 * @author Vic Shiwei
 * @desc 商品分类控制器
 */
class GoodscateController extends Core_Controller_Admin {
    
    protected $model;

    public function init() {
        parent::init();
        $this->model = new Chaoshi_GoodscateModel();        
    }
    
    /**
     * 区域列表是无限极分类结构，不能进行模糊搜索和查看未公布状态；原因和parentId有关。
     */
    public function indexAction(){
        $this->_layout = true;
        $request = $this->_getRequest();
        //获取商品一级分类
        $nodeCate1 = $this->nodeGcateAction(0);
        $this->getView()->assign('nodeCate1', $nodeCate1);
        
        //设置并显示默认分类
        $defaultParentId = $nodeCate1[0]['id'];

        if($request && isset($request['jsubmit']) && $request['jsubmit']){
            switch ($request['jsubmit']) {
                case 'search':
                    $defaultParentId=$request['cateId'];
                    $returnData = $this->model->getGcateList($request, $defaultParentId);
                    $data=$returnData['data'];
                    $total = $returnData['total'];

                    break;
            }
        }else{
            $returnData = $this->model->getGcateList(null,$defaultParentId);        
            $data=$returnData['data'];
            $total = $returnData['total'];
        }
        

        //显示分页
        $pagination = $this->showPagination($total);
        $this->getView()->assign('pagination', $pagination);
        

        $this->getView()->assign('defaultParentId', $defaultParentId);
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);        
        $this->getView()->assign('post', $request);
    }

    
    public function addAction() {
        $this->_layout = true;
        $rules = $this->model->getRules();
        $cateId = $this->getParam('cateId',0);
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
                $this->save($post, 'add');
            }
            
            $treeGcate = $this->model->getTreeGcate($post['parentId']);
        }else{
            $treeGcate = $this->model->getTreeGcate($cateId);
        }
        
        
        $this->getView()->assign('treeGcate', $treeGcate);
        $this->getView()->assign("rules", json_decode($rules)->validation);
    }
    
    public function editAction(){
        $this->_layout = true;
        $cateId = $this->getParam('cateId',0);        
        $gcateInfo = $this->model->getGcateInfo($cateId);
        if(empty($gcateInfo)){
            $this->redirect("/$this->_ModuleName/Goodscate/index");
        }
        $rules = $this->model->getRules();        
        if($this->isPost()){
            $post = $this->getPost();
            $post['cateId']=$cateId;
            $v = new validation(); //数据校验
            $v->validate($rules, $post);
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
                if ($this->isAjax()) {
                    $this->err('', $v->error_message); //输出异步错误信息
                }
            }else{
                $this->save($post, 'edit');
            }
            
            $gcateInfo=$post;
        }
        
        $treeGcate = $this->model->getTreeGcate($gcateInfo['parentId']);
        $this->getView()->assign('treeGcate', $treeGcate);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('gcateInfo', $gcateInfo);
    }
    
    /**
     * 删除商品分类
     * 检查该分类下是否有子分类，如果有提示必须先删除子分类，若无则检查该分类下是否有品牌，
     * 如果有则先清除品牌数据，若无则检查该分类是否有商品属性，如果有则先清空该分类的商品属性
     * 如果该分类下什么都没有，则直接删除。
     */
    public function delAction(){
        $cateId = $this->getParam('cateId',0);
        if(empty($cateId)){
            $this->redirect("/$this->_ModuleName/Goodscate/index");
        }
        $ref=$_SERVER['HTTP_REFERER'];
        //检查该分类下是否有子分类
        $children = $this->model->checkCateChildren($cateId);
        if($children){
            $this->jsLocation('请先删除该分类下的子类！',$ref);
        }
        //检查该分类下是否有品牌
        $brand = $this->model->checkCateBrand($cateId);
        if($brand){
            $this->jsLocation('请先删除该分类下的品牌！',$ref);
        }
        //检查该分类下是否有商品属性
        $attribute = $this->model->checkCateAttribute($cateId);
        if($attribute){
            $this->jsLocation('请先删除该分类下的商品属性！',$ref);
        }
        //检查该分类下是否有商品
        $goods = $this->model->checkCateGoods($cateId);
        if($goods){
            $this->jsLocation('请先删除该分类下的商品！',$ref);
        }
        
        //删除分类,返回来源地址
        if($this->model->del($cateId)){
            $this->jsLocation('删除成功！',$ref);
        }else{
            $this->jsLocation('删除失败！',$ref);
        }
    }
    
    public function save($data,$action){
        if(!$data || !$action){            
            $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
        }
        $saveR = $this->model->$action($data);
        $refurl = $this->getQuery('refurl',0);
        if($refurl){
            $_eventUrl = str_replace('{*}', '&', $refurl);
        }else{
            $_eventUrl = "/$this->_ModuleName/$this->_ControllerName/index";
        }
        
        if($saveR){
            //保存成功跳转到列表页
            $this->ok(null, $_eventUrl, '保存成功！');
        }else{
            $this->ok(null, $_eventUrl, '保存失败！');
        }
    }
    
    
    /**
     * 获取分类的子分类,支持ajax http和函数形式调用;
     * 根据parentPath参数判断该分类是第几级分类，目的是要找出哪级分类是商品类型，在目前第3级分类是商品类型。
     * @param int $parentId 分类的父级节点id
     */
    public function nodeGcateAction($parentId = '0') {
//        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        //根据分类id获取所有子类
        if ($this->isAjax()) {
            $cateId = $this->getParam('cateId');
            if ($cateId) {
                $parentId = $cateId;
            }else{
                $parentId = '';
                return FALSE;
            }
        }
        //获取数据        
        $nodeGcate = $this->model->getNodeGcate($parentId);
        if ($this->isAjax()) {
            $data = json_encode($nodeGcate);
            echo $data;
            exit;
        } else {
            return $nodeGcate;
        }
    }

    

}
