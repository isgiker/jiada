<?php

/**
 * @name GoodsController
 * @desc 商品发布
 * @author Vic
 */
class GoodsController extends Core_Basic_Controllers {
    
    protected $model;
    protected $cateModel;

    public function init() {
        $this->getView()->assign('_view', $this->getView());
        $this->model = new Admin_GoodsModel();
        //加载商品分类模型
        $this->cateModel = new Admin_GoodscateModel();
        $this->brandModel = new Admin_GoodsbrandModel();
    }
    
    /**
     * 区域列表是无限极分类结构，不能进行模糊搜索和查看未公布状态；原因和parentId有关。
     */
    public function indexAction(){
        $this->_layout = true;

        $post = $this->getPost();
        if($post && $post['jsubmit']){
            switch ($post['jsubmit']) {
                case 'search':
                    
                    $data = $this->model->getGoodsList($post);        
                    $total = (int) $this->model->getGoodsTotal($post);       

                    break;
            }
        }else{
            $data = $this->model->getGoodsList();        
            $total = (int) $this->model->getGoodsTotal();
        }

        
        //显示分页
        $pagination = $this->showPagination($total);
        
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
        $this->getView()->assign('post', $post);
    }

    /**
     * 发布商品
     */
    public function addAction() {
        $this->_layout = true;       
        $post = $this->getPost();
        $rules = $this->model->getRules();        
        if($this->isPost()){            
            //数据校验
            $v = new validation();
            $v->validate($rules, $post);

            if (!empty($v->error_message)) {
                //输出同步错误信息
                $this->getView()->assign("error", $v->error_message);                
                $this->getView()->assign("post", $post);
                if ($this->isAjax()) {
                    //输出异步错误信息
                    $this->err('', $v->error_message);
                }
            }else{
                //保存数据
                $this->saveAction($post, 'add');
            }            
            
        }
        $cateId=@$post['cateId'];
        $brandId=@$post['brandId'];
        //获取商品分类，根据分类id获取品牌列表
        $treeGcate = $this->cateModel->getTreeGcate($cateId);
        
        if($cateId){
            $brands = $this->brandModel->getCateBrand($cateId);
            $this->getView()->assign('brands', $brands);
            $this->getView()->assign('brandId', $brandId);
        }
        
        //模板变量
        $this->getView()->assign('treeGcate', $treeGcate);        
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
    }
    
    public function editAction(){
        $this->_layout = true;
        $goodsId = $this->getParam('goodsId',0);
        //获取商品信息
        $goodsInfo = $this->model->getGoodsInfo($goodsId);
        if(!$goodsInfo){
            $this->redirect('/Admin/Goods/index');
        }
        
        $post = $this->getPost();
        $rules = $this->model->getRules();        
        if($this->isPost()){            
            //数据校验
            $v = new validation();
            $v->validate($rules, $post);

            if (!empty($v->error_message)) {
                //输出同步错误信息
                $this->getView()->assign("error", $v->error_message);                
                $goodsInfo=$post;
                if ($this->isAjax()) {
                    //输出异步错误信息
                    $this->err('', $v->error_message);
                }
            }else{
                //保存数据
                $this->saveAction($post, 'add');
            }            
            $cateId=@$post['cateId'];
            $brandId=@$post['brandId'];
        }else{
            $cateId=$goodsInfo['cateId'];
            $brandId=$goodsInfo['brandId'];
        }
        
        //获取商品分类，根据分类id获取品牌列表
        $treeGcate = $this->cateModel->getTreeGcate($cateId);
        
        if($cateId){
            $brands = $this->brandModel->getCateBrand($cateId);
            $this->getView()->assign('brands', $brands);
            $this->getView()->assign('brandId', $brandId);
        }
        
        //模板变量
        
        $this->getView()->assign('goodsInfo', $goodsInfo);
        $this->getView()->assign('treeGcate', $treeGcate);
        $this->getView()->assign("rules", json_decode($rules)->validation);
    }
    
    /**
     * 上传包装图片
     * @param type $goodsId 产品Id必要参数
     */
    public function packPicAction(){
        $this->_layout=true;
        $goodsId = $this->getParam('goodsId');
        //获取商品信息
        $goodsInfo = $this->model->getGoodsInfo($goodsId);
        if(!$goodsInfo){
            $this->redirect('/Admin/Goods/index');
        }
        
        //模板变量        
        $this->getView()->assign('goodsInfo', $goodsInfo);
        $this->getView()->assign('goodsId', $goodsId);
    }
    
    /**
     * 商品包装图片至本地
     */
    public function uploadToLocalAction(){
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $fi = new File_Image();
        new File_Thumbnail('D:/var/www/php/me/jiada/static/plugin/jcrop/img/default.jpg', 200, 200, 0, '200X200.jpg');

        exit;
    }
    
    /**
     * 保存切图至Ftp服务器;
     */
    public function uploadToFtpAction(){
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        if($this->isPost()){            
            //数据校验
            $post = $this->getPost();

            $targ_w = $targ_h = 150;
            $jpeg_quality = 90;

            $src = 'demo_files/pool.jpg';
            $img_r = imagecreatefromjpeg($src);
            $dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

            imagecopyresampled($dst_r,$img_r,0,0,$post['x'],$post['y'],
            $targ_w,$targ_h,$post['w'],$post['h']);

            header('Content-type: image/jpeg');
            imagejpeg($dst_r,null,$jpeg_quality);

            exit;          
            
        }
    }


    public function saveAction($data,$action){
        if(!$data || !$action){            
            $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
        }
        //新增时返回商品id;
        $saveR = $this->model->$action($data);
        if($saveR){
            //保存成功跳转到列表页
            if($action == 'add'){
                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/edit/goodsId/'.$saveR.'#tab2');
            }else{
                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index');
            }
            
        }else{
            //返回来源地址;
            $this->jsLocation('保存失败！',$_SERVER['HTTP_REFERER']);
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

    

}
