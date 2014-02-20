<?php

/**
 * @name IndexController
 * @author Vic Shiwei
 * @desc 店铺（仓库）控制器
 */
class ShopController extends Core_Controller_Business {
    
    protected $model;
    protected $imagesConfig;

    public function init() {
        parent::init();
        $this->model = new Chaoshi_ShopModel();
        $this->areaModel = new AreaModel();
        
        //加载配置文件
        $this->imagesConfig = Yaf_Registry::get("_ImagesConfig");
    }
    
    /**
     * 店铺首页
     */
    public function indexAction(){
        $this->_layout = true;
        $data = null;
        $this->getView()->assign('data', $data);
    }
    
    /**
     * 新建店铺/仓库
     */
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
            if(isset($post['provinceId']) && $post['provinceId']){
                $city = $this->areaModel->getNodeArea($post['provinceId']);
                $this->getView()->assign("city", $city);
            }
            if(isset($post['cityId']) && $post['cityId']){
                $district = $this->areaModel->getNodeArea($post['cityId']);            
                $this->getView()->assign("district", $district);
            }
        }
        
        //获取省份
        $province = $this->areaModel->getNodeArea(0);
        
        
        $this->getView()->assign("province", $province);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
        //page 添加js文件..<!--iframeTools的位置必须在artDialog下面-->        
        $_page=array(
            'static_css_files' => [
            ],
            'static_js_files' => [
                ['path'=>'/plugin/jquery_artDialog/jquery.artDialog.js?skin=blue','attr'=>''],
                ['path'=>'/js/basic/libs/iframeTools.source.js','attr'=>''],
                ['path'=>'http://api.map.baidu.com/api?v=2.0&ak=7463442f78f85ee9bc9e7b3b0ff60e6d','attr'=>''],
                ['path'=>'/plugin/baidumap/js/baidumap.js','attr'=>['charset'=>'utf8']],
            ]
        );
        $this->getView()->assign("_page", $_page);
        
    }
    
    /**
     * 店铺/仓库信息，编辑；
     */
    public function editAction() {
        $this->_layout = true;
        if(empty($this->currentShopId)){
            $this->redirect("/$this->_ModuleName/$this->_ControllerName/index");
        }
        //获取当前店铺信息
        $shopInfo = $this->model->getShopInfo($this->currentShopId);
        if(empty($shopInfo)){
            $this->redirect("/$this->_ModuleName/$this->_ControllerName/index");
        }
        
        $rules = $this->model->getRules();
        
        if($this->isPost()){
            $post = $this->getPost();
            $post['shopId']=$this->currentShopId;
            
            $v = new validation(); //数据校验
            $v->parentCls=$this;
            $v->validate($rules, $post);
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
                if ($this->isAjax()) {
                    $this->err(null, $v->error_message); //输出异步错误信息
                }
            }else{
                $this->saveAction($post, 'edit');
            }
            
            
            $shopInfo=$post;
        }
        
        //获取省份
        $province = $this->areaModel->getNodeArea(0);
        $this->getView()->assign("province", $province);
        
        $city = $this->areaModel->getNodeArea($shopInfo['provinceId']);
        $this->getView()->assign("city", $city);
        
        $district = $this->areaModel->getNodeArea($shopInfo['cityId']);
        $this->getView()->assign("district", $district);
                
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('shopInfo', $shopInfo);
        
        //page 添加js文件..<!--iframeTools的位置必须在artDialog下面-->
        $_page=array(
            'static_css_files' => [
            ],
            'static_js_files' => [
                ['path'=>'/plugin/jquery_artDialog/jquery.artDialog.js?skin=blue','attr'=>''],
                ['path'=>'/js/basic/libs/iframeTools.source.js','attr'=>''],
                ['path'=>'http://api.map.baidu.com/api?v=2.0&ak=7463442f78f85ee9bc9e7b3b0ff60e6d','attr'=>''],
                ['path'=>'/plugin/baidumap/js/baidumap.js','attr'=>['charset'=>'utf8']],
            ]
        );
        $this->getView()->assign("_page", $_page);
        
    }
    
    public function saveAction($data,$action){
        if(!$data || !$action){
            if($this->isAjax()){
                $this->err(null, '参数错误');
            }else{
                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
            }
            
        }
        //保存数据 begin;返回shopId;
        $saveR = $this->model->$action($data);
        
        $_eventUrl = "/$this->_ModuleName/$this->_ControllerName/index/shopId/$saveR";
        if($action=='edit'){
            $_eventUrl = "/$this->_ModuleName/$this->_ControllerName/$action/shopId/$saveR";
        }
        if($saveR){
            //保存成功跳转到列表页            
            if($this->isAjax()){
                $this->ok(null, $_eventUrl, '保存成功！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存成功！','_eventUrl'=>$_eventUrl));
//                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
            }
        }else{
            if($this->isAjax()){
                $this->ok(null, $_eventUrl, '保存失败！');
            }else{
                $this->getView()->assign("_event", array('_eventMsg'=>'保存失败！','_eventUrl'=>$_eventUrl));
//                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
            }
            
        }
    }
    
    /**
     * 上传店铺Logo
     * @param type $param
     */
    public function logoAction() {
        $this->_layout = true;
        
        if($this->isPost()){
            $r=$this->uploadToFtpAction();
            
            $this->getView()->assign('uploadMsg', $r['message']);
        }
        //获取当前店铺信息
        $shopInfo = $this->model->getShopInfo($this->currentShopId);
        if(empty($shopInfo)){
            $this->redirect("/$this->_ModuleName/$this->_ControllerName/index");
        }
        $fileImg = new File_Image();
        if($shopInfo['shopLogo']){
            $shopLogoUrl=$fileImg->generateImgUrl(array('imgUrl'=>$shopInfo['shopLogo']), $this->imagesConfig);
        }else{
            $shopLogoUrl='';
        }
        $this->getView()->assign('shopLogoUrl', $shopLogoUrl);

    }
    
    /**
     * 单个文件上传，保存切图至Ftp服务器;
     */
    public function uploadToFtpAction(){
        if($this->isPost()){
            //对提交数据校验(图片链接不要简写如：//....jpg)
            $post = $this->getPost();
            if(!trim($post['img'])){
                return $this->returnResult(false, '提交数据没有检查到上传源文件！');
            }
            
            //读取图片配置文件
            $imagesConfig = $this->imagesConfig;
            
            //获取缩略图尺寸
            $logoSize = $imagesConfig->business->shoplogo->size;
            if ($logoSize) {
                $logoSize = explode(',', $logoSize);
            } else {
                return $this->returnResult(false, '缩略图大小尺寸未定义！');
            }
            
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
        
            //文件扩展名
            $fileSuffix = pathinfo($post['img'], PATHINFO_EXTENSION);
            //获取远程文件类型
            $fileHeaders=get_headers($post['img'], 1);
            $fileType=$fileHeaders['Content-Type'];
            
            //设置文件保存的目标路径,可修改;
            $localDestPath = rtrim($_SERVER['DOCUMENT_ROOT'] . '/temp/' . date("Y") . '/' . date("m"), '/');
            Util::mkdir_r($localDestPath);
            $fileName = date("YmdHis") . rand(100, 999);
            //获取ftp上的文件路径
            $imgParameter=array('imgType'=>$fileSuffix,'imgServer'=>$servGroup);
            $ftpFile = $fi->getImagePath($imgParameter);
            $ftp->createFolder($ftpFile['filePath']);
            
            //开始上传文件
            foreach ($logoSize as $key=>$size) {
                //验证格式是否正确
                $imgSizePattern = '/(\d+)X(\d+)/';
                $isRight = preg_match($imgSizePattern, $size);
                if (!$isRight) {
                    return $this->returnResult(false, '缩略图大小尺寸格式配置错误！正确格式如：60X60！');
                }
                $sizeArr = explode('X', $size);
                
                //组合目标文件
                $localDestFile = $localDestPath . '/' . $fileName. '_' . $size . ".$fileSuffix";
                
                //对提交的切图数据进行处理，根据缩略图尺寸生成相应图片
                $targ_w = $sizeArr[0];
                $targ_h = $sizeArr[1];
                $jpeg_quality = 100;
                $img_r = imagecreatefromjpeg($post['img']);
                $dst_r = ImageCreateTrueColor( $targ_w, $targ_h);

                @imagecopyresampled($dst_r,$img_r,0,0,$post['x'],$post['y'],$targ_w,$targ_h,$post['w'],$post['h']);

    //            header('Content-type: image/jpeg');
                //将生成的图片保存到$localDestFile                
                if ($fileType == 'image/jpeg' || $fileType == 'image/pjpeg') {
                    imagejpeg($dst_r,$localDestFile,$jpeg_quality);
                }
                if ($fileType == 'image/gif') {
                    imagegif($dst_r,$localDestFile,$jpeg_quality);
                }
                if ($fileType == 'image/png') {
                    imagepng($dst_r,$localDestFile,$jpeg_quality);
                }
                
                //开始将文件上传至ftp服务器；                
                $remoteFilePath = $ftpFile['filePath'] . '/' . $size.'_'.$ftpFile['fileName'];
                $r = $ftp->upload($localDestFile, $remoteFilePath, $mode = 'auto', 777);
                if ($r) {
                    //更新店铺logo图片,一个文件有多个缩略图，但是每个源文件只更新一个缩略图路径到数据库
                    if ($key==0) {
                        if (!$this->model->upShopLogo($remoteFilePath, $this->currentShopId)) {
                            return $this->returnResult(false, '上传至ftp成功，数据更新失败！');
                        }
                    }
                    //删除本地图片文件
                    @unlink($localDestFile);
                } else {
                    //上传失败，删除本地相关的源图片和缩略图
                    return $this->returnResult(false, '上传至ftp失败！');
                }
            }
            return $this->returnResult(true, '上传至ftp成功！');
        }
        return $this->returnResult(false, '不是正确的提交方式！');
    }
    
}
