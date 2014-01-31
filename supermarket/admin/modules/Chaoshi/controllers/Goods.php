<?php

/**
 * @name GoodsController
 * @desc 商品发布
 * @author Vic
 */
class GoodsController extends Core_Controller_Admin {

    protected $model;
    protected $cateModel;

    public function init() {
        parent::init();
        $this->model = new Chaoshi_GoodsModel();
        //加载商品分类模型
        $this->cateModel = new Chaoshi_GoodscateModel();
        $this->brandModel = new Chaoshi_GoodsbrandModel();
    }

    /**
     * 区域列表是无限极分类结构，不能进行模糊搜索和查看未公布状态；原因和parentId有关。
     */
    public function indexAction() {
        $this->_layout = true;

        $post = $this->getPost();
        if ($post && $post['jsubmit']) {
            switch ($post['jsubmit']) {
                case 'search':

                    $data = $this->model->getGoodsList($post);
                    $total = (int) $this->model->getGoodsTotal($post);

                    break;
            }
        } else {
            $data = $this->model->getGoodsList();
            $total = (int) $this->model->getGoodsTotal();
        }


        //显示分页
        $pagination = $this->showPagination($total);
        
        //获取商品分类
        if(isset($post['cateId'])){
            $cateId=$post['cateId'];
            
        }else{
            $cateId='';
        }
        $treeGcate = $this->cateModel->getTreeGcate($cateId);

        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
        $this->getView()->assign('post', $post);
        $this->getView()->assign('treeGcate', $treeGcate);
    }

    /**
     * 发布商品
     */
    public function addAction() {
        $this->_layout = true;
        $post = $this->getPost();
        $rules = $this->model->getRules();
        if ($this->isPost()) {
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
            } else {
                //保存数据
                $this->saveAction($post, 'add');
            }
        }
        isset($post['cateId'])?$cateId = $post['cateId']:$cateId='';
        isset($post['brandId'])?$brandId = $post['brandId']:$brandId='';
        //获取商品分类，根据分类id获取品牌列表
        $treeGcate = $this->cateModel->getTreeGcate($cateId);

        if ($cateId) {
            $brands = $this->brandModel->getCateBrand($cateId);
            $this->getView()->assign('brands', $brands);
            $this->getView()->assign('brandId', $brandId);
        }

        //模板变量
        $this->getView()->assign('treeGcate', $treeGcate);
        $this->getView()->assign("rules", json_decode($rules)->validation);
    }

    public function editAction() {
        $this->_layout = true;
        $goodsId = $this->getParam('goodsId', 0);
        //获取商品信息
        $goodsInfo = $this->model->getGoodsInfo($goodsId);
        if (!$goodsInfo) {
            $this->redirect('/Admin/Goods/index');
        }

        $post = $this->getPost();
        $rules = $this->model->getRules();
        if ($this->isPost()) {
            $post['goodsId'] = $goodsId;

            //数据校验
            $v = new validation();
            $v->validate($rules, $post);

            if (!empty($v->error_message)) {
                //输出同步错误信息
                $this->getView()->assign("error", $v->error_message);
                $goodsInfo = $post;
                if ($this->isAjax()) {
                    //输出异步错误信息
                    $this->err('', $v->error_message);
                }
            } else {
                //保存数据
                $this->saveAction($post, 'edit');
            }
            $cateId = @$post['cateId'];
            $brandId = @$post['brandId'];
        } else {
            $cateId = $goodsInfo['cateId'];
            $brandId = $goodsInfo['brandId'];
        }

        //获取商品分类，根据分类id获取品牌列表
        $treeGcate = $this->cateModel->getTreeGcate($cateId);

        if ($cateId) {
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
     * 只能编辑商品价格，不能修改商品其它信息
     * @param int $goodsId 商品id
     * @param int $storehouseId 仓库id
     */
    public function priceAction() {
        $this->_layout = true;
        $goodsId = $this->getParam('goodsId', 0);
        //获取商品信息
        $goodsInfo = $this->model->getGoodsPriceInfo($goodsId);
        if (!$goodsInfo) {
            $this->redirect('/Admin/Goods/index');
        }

        $post = $this->getPost();
        $rules = $this->model->getPriceRules();
        if ($this->isPost()) {
            $post['goodsId'] = $goodsId;

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
            } else {
                //保存数据
                $this->saveAction($post, 'price');
            }
        }

        //获取仓库        
        $sModel = new Chaoshi_StorehouseModel();
        $storehouse = $sModel->getStorehouseList();

        //模板变量
        $this->getView()->assign('storehouse', $storehouse);
        $this->getView()->assign('goodsInfo', $goodsInfo);
        $this->getView()->assign("rules", json_decode($rules)->validation);
    }
    
    /**
     * 添加商品库存，商品进货流水记录
     * @param int $goodsId 商品id
     * @param int $storehouseId 仓库id
     */
    public function stockAction() {
        $this->_layout = true;
        $goodsId = $this->getParam('goodsId', 0);
        //获取商品信息
        $goodsInfo = $this->model->getGoodsPriceInfo($goodsId);
        if (!$goodsInfo) {
            $this->redirect('/Admin/Goods/index');
        }

        $post = $this->getPost();
        $rules = $this->model->getStockRules();
        if ($this->isPost()) {
            $post['goodsId'] = $goodsId;

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
            } else {
                //保存数据
                $this->saveAction($post, 'stock');
            }
        }

        //获取仓库        
        $sModel = new Chaoshi_StorehouseModel();
        $storehouse = $sModel->getStorehouseList();

        //模板变量
        $this->getView()->assign('storehouse', $storehouse);
        $this->getView()->assign('goodsInfo', $goodsInfo);
        $this->getView()->assign("rules", json_decode($rules)->validation);
    }
    
    /**
     * 显示商品进货记录
     * @param int $goodsId 商品id
     * @param int $storehouseId 仓库id,以后要改成从cookie获取
     */
    public function stocklistAction(){
        $this->_layout = true;
        $goodsId = $this->getParam('goodsId', 0);
        $storehouseId = $this->getParam('storehouseId', 0);
        if (!$goodsId || !$storehouseId) {
            $this->redirect('/Admin/Goods/index');
        }
        $data = $this->model->getStocklist($goodsId, $storehouseId);
        $this->getView()->assign('data', $data);
        $this->getView()->assign('goodsId', $goodsId);
        $this->getView()->assign('storehouseId', $storehouseId);
    }
    
    public function detailAction() {
        $this->_layout = true;
        $goodsId = $this->getParam('goodsId', 0);
        //获取商品信息
        $goodsInfo = $this->model->getGoodsInfo($goodsId);
        if (!$goodsInfo) {
            $this->redirect('/Admin/Goods/index');
        }

        if ($this->isPost()) {
            $post = $this->getPost();            
            $post['goodsId'] = $goodsId;
            $this->saveAction($post, 'detail');
        }

        //模板变量
        $this->getView()->assign('goodsInfo', $goodsInfo);
        $this->getView()->assign('goodsId', $goodsId);
    }
    
    public function attrAction(){
        $this->_layout = true;
        $goodsId = $this->getParam('goodsId', 0);
        if (!$goodsId) {
            $this->redirect('/Admin/Goods/index');
        }
        
        if ($this->isPost()) {
            $post = $this->getPost();        
            $post['goodsId'] = $goodsId;
            $this->saveAction($post, 'attr');
        }
        
        //获取商品信息
        $goodsInfo = $this->model->getGoodsInfo($goodsId);
        if (!$goodsInfo) {
            $this->redirect('/Admin/Goods/index');
        }
        
        //根据商品类型获取父类id
        $cateInfo = $this->cateModel->getGcateInfo($goodsInfo['cateId']);
        if($cateInfo){
            //根据商品分类获取商品属性,form:input
            $gattr = $this->model->getGoodsAttr($cateInfo['parentId']);
            $this->getView()->assign('gattr', $gattr);
        }
        $attrVaules = $this->model->getGoodsAttrVaules($goodsId);
        $this->getView()->assign('attrVaules', $attrVaules);
        $this->getView()->assign('goodsId', $goodsId);
        
    }

    /* 上传包装图片 begin
     * ========================================================================================================== */

    /**
     * 上传包装图片,保证数据的原子性，只要数据没有更新到数据库，所有图片删除重新上传；
     * @param type $goodsId 产品Id必要参数
     */
    public function packPicAction() {
        $this->_layout = true;
        $goodsId = $this->getParam('goodsId');
        if (!$goodsId) {
            $this->redirect('/Admin/Goods/index');
        }
        if ($this->isPost()) {
            //

            $upResult = new File_ImageUpload('files');
            if ($upResult->uploadFile()) {

                //获取上传成功的文件
                $sFile = $upResult->getUploadSuccessFile();

                //生成缩略图
                $r = $this->thumbnail($sFile, $upResult);

                //如果生成缩略图失败，删除本地源文件
                $upResult->delFiles();

                $this->getView()->assign('uploadMsg', $r['message']);
            } else {
                //如果失败upload类自动删除本地源图片;
                $this->getView()->assign('uploadMsg', $upResult->getErrorMsg());
            }
        }
        //获取商品信息
        $goodsInfo = $this->model->getGoodsInfo($goodsId);
        if (!$goodsInfo) {
            $this->redirect('/Admin/Goods/index');
        }

        //模板变量        
        $this->getView()->assign('goodsInfo', $goodsInfo);
        $this->getView()->assign('goodsId', $goodsId);
    }

    /**
     * 商品包装图片至本地
     */
    public function thumbnail($files, $upResult) {
        //读取图片配置文件
        $imagesConfig = Yaf_Registry::get("_ImagesConfig");
        //获取缩略图尺寸
        $goodsSize = $imagesConfig->admin->goods->size;
        if ($goodsSize) {
            $goodsSize = explode(',', $goodsSize);
        } else {
            return $this->returnResult(false, '缩略图大小尺寸未定义！');
        }

        $fileThumbnail = new File_Thumbnail();

        if ($files && is_array($files)) {
            $toFtpFiles = array();
            foreach ($files as $f) {
                $fInfo = pathinfo($f);
                foreach ($goodsSize as $size) {
                    //验证格式是否正确
                    $imgSizePattern = '/(\d+)X(\d+)/';
                    $isRight = preg_match($imgSizePattern, $size);
                    if (!$isRight) {
                        return $this->returnResult(false, '缩略图大小尺寸格式配置错误！正确格式如：60X60！');
                    }
                    $sizeArr = explode('X', $size);
                    //缩略图路径
                    $localThumbFilePath = $fInfo['dirname'] . '/' . $fInfo['filename'] . '_' . $size . '.' . $fInfo['extension'];
                    //记录源文件的缩略图路径，然后统一上传至ftp服务器；
                    $toFtpFiles[$fInfo['filename']][$size] = $localThumbFilePath;

                    //开始生成缩略图，无法判断缩略图是否生成成功，有待改良；
                    $fileThumbnail->exe($f, $sizeArr[0], $sizeArr[1], 0, $localThumbFilePath);
                }
            }
            //将缩略图上传至ftp，不管上传成功失败，删除本地相关的源图片和缩略图
            $r = $this->uploadToFtpAction($toFtpFiles);
            //删除本地源文件和缩略图文件
            $dFs = array_merge($files, $toFtpFiles);
            $upResult->delFiles($dFs);
            return $r;
        } else {
            return $this->returnResult(false, '源文件为空或数据类型错误');
        }
    }

    /**
     * 保存切图至Ftp服务器;
     */
    public function uploadToFtpAction($localFiles) {
        $goodsId = $this->getParam('goodsId');

        if (!$localFiles || !is_array($localFiles)) {
            return $this->returnResult(false, '参数错误,上传至Ftp失败！没有可上传的本地文件。');
        }
        //加载配置文件
        $imagesConfig = Yaf_Registry::get("_ImagesConfig");

        //获取图片服务器组
        $imagesServerGroups = $imagesConfig->common->setting->images->serverGroup;

        $fi = new File_Image();
        //设置此次上传到哪个ftp组;
        $servGroup = $fi->getImageServerGroup($imagesServerGroups);

        //根据ftp组，获取组的服务器配置信息
        $hostname = $imagesConfig->$servGroup->ftp->master->host;
        $username = $imagesConfig->$servGroup->ftp->master->username;
        $password = $imagesConfig->$servGroup->ftp->master->password;
        $port = $imagesConfig->$servGroup->ftp->master->port;

        //连接ftp服务器
        $config = array('hostname' => $hostname, 'username' => $username, 'password' => $password, 'port' => $port);
        $ftp = new File_Ftp();
        if (!$ftp->connect($config)) {
            return $this->returnResult(false, '连接ftp服务器失败！');
        }

        foreach ($localFiles as $key => $lf) {
            $fileName = '';
            foreach ($lf as $size => $lfPath) {

                //文件扩展名
                $fileType = pathinfo($lfPath, PATHINFO_EXTENSION);

                //获取ftp上的文件路径
                $ftpFile = $fi->getImagePath($size, $fileType, $servGroup);
                $ftp->createFolder($ftpFile['filePath']);
                $remoteFilePath = $ftpFile['filePath'] . '/' . $ftpFile['fileName'];
                $r = $ftp->upload($lfPath, $remoteFilePath, $mode = 'auto', $permissions = 777);
                if ($r) {
                    //更新产品包装图片,一个文件有多个缩略图，但是每个源文件只更新一个缩略图路径到数据库
                    if ($fileName != $key) {
                        if (!$this->model->upGoodsPackPic($remoteFilePath, $goodsId)) {
                            return $this->returnResult(false, '上传至ftp成功，数据更新失败！');
                        }
                    }
                } else {
                    //上传失败，删除本地相关的源图片和缩略图
                    return $this->returnResult(false, '上传至ftp失败！');
                }
                $fileName = $key;
            }
        }

        return $this->returnResult(true, '上传成功！');
    }

    /* 上传包装图片 end
     * ========================================================================================================== */

    /**
     * 删除商品包装图片
     * @param int $goodsId 商品id
     * @param string $packPicSign 图片路径md5值
     */
    public function delPackPicAction() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $goodsId = $this->getParam('goodsId');
        $packPicSign = $this->getParam('sign');
        if (!$goodsId || !$packPicSign) {
            $this->jsLocation('参数错误！', '/Admin/Goods/index');
        }

        //获取商品信息
        $goodsInfo = $this->model->getGoodsInfo($goodsId);
        if (!$goodsInfo) {
            $this->jsLocation('该商品不存在！', '/Admin/Goods/index');
        }
        $newPackPic = array();
        if ($goodsInfo['packPic']) {
            $packPic = explode(',', $goodsInfo['packPic']);
            //去掉要删除的图片，组成新的数组；
            foreach ($packPic as $pic) {
                if ($packPicSign != md5($pic)) {
                    $newPackPic[] = $pic;
                }
            }
        }

        if (empty($newPackPic)) {
            //注意这里一定是NUll,concat_ws函数决定的；否则前面会多一个逗号
            $newPackPic = NUll;
        } else {
            $newPackPic = implode(',', $newPackPic);
        }

        if (!$this->model->upGoodsPackPic($newPackPic, $goodsId, 'del')) {
            $this->jsLocation('图片删除失败', '/Admin/Goods/packPic/goodsId/' . $goodsId);
        } else {
            $this->jsLocation('图片删除成功', '/Admin/Goods/packPic/goodsId/' . $goodsId);
        }
    }

    public function saveAction($data, $action) {
        if (!$data || !$action) {
            if($this->isAjax()){
                $this->err(null, '参数错误');
            }else{
                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
            }
        }
        //新增时返回商品id;
        $saveR = $this->model->$action($data);
        if ($saveR) {
            //保存成功跳转到列表页
            if ($action == 'add') {
                $this->ok(null, '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/packpic/goodsId/'.$saveR, '保存成功！');
            } elseif ($action == 'edit') {
                $this->ok(null, '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/edit/goodsId/'.$data['goodsId'], '保存成功！');
            }elseif ($action == 'detail') {
                $this->redirect('/' . $this->getRequest()->getModuleName() . '/' . $this->getRequest()->getControllerName() . '/detail/goodsId/'.$data['goodsId']);
            }elseif ($action == 'attr') {
                $this->ok(null, '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/attr/goodsId/'.$data['goodsId'], '保存成功！');
            } elseif ($action == 'price') {
                $this->redirect('/' . $this->getRequest()->getModuleName() . '/' . $this->getRequest()->getControllerName() . '/price/goodsId/'.$data['goodsId']);
            }elseif ($action == 'stock') {
                $this->redirect('/' . $this->getRequest()->getModuleName() . '/' . $this->getRequest()->getControllerName() . '/stocklist/storehouseId/'.$data['storehouseId'].'/goodsId/'.$data['goodsId']);
            }
            
            
        } else {
            //返回来源地址;
            $this->jsLocation('保存失败！', $_SERVER['HTTP_REFERER']);
        }
    }

}
