<?php

/**
 * @name Ad
 * @author Vic
 * @desc 广告控制器
 */
class AdController extends Core_Controller_Admin {
    
    protected $model;
    private $imagesConfig;    
    private $fileImg;

    public function init() {
        parent::init();
        //加载配置文件
        $this->imagesConfig = Yaf_Registry::get("_ImagesConfig");

        $this->fileImg = new File_Image();
        
        $this->model = new Default_AdModel();
    }
    
    /**
     * 广告模块列表
     */
    public function indexAction(){
        $this->_layout = true;
        $post = $this->getPost();
        if ($post && $post['jsubmit']) {
            switch ($post['jsubmit']) {
                case 'search':

                    $data = $this->model->getAdList($post);
                    $total = (int) $this->model->getAdTotal($post);

                    break;
            }
        } else {
            $data = $this->model->getAdList();
            $total = (int) $this->model->getAdTotal();
        }

        
        //显示分页
        $pagination = $this->showPagination($total);
        
        $this->getView()->assign('data', $data);
        $this->getView()->assign('total', $total);
        $this->getView()->assign('pagination', $pagination);
        $this->getView()->assign('post', $post);
        
        //图片
        $this->getView()->assign('imagesConfig', $this->imagesConfig);
        $this->getView()->assign('fileImg_obj', $this->fileImg);

    }

    
    public function addAction() {
        $this->_layout = true;
        
        //接收参数
        $advertiserId = $this->getParam('advertiserId',0);
        $advertiserM = new Default_AdvertiserModel();
        $advertiserInfo=$advertiserM->getAdvertiserInfo($advertiserId);
        if(!$advertiserInfo){
            $this->redirect("/{$this->_ModuleName}/{$this->_ControllerName}/index");
        }
        $admoduleM = new Default_AdmoduleModel();
        $admodules = $admoduleM->getAdmodules();
        
        $rules = $this->model->getRules();
        $post = $this->getPost();
        $post['advertiserId']=$advertiserId;
        
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
                //上传图片至ftp
                $size='0X0';
                $r = $this->uploadToFtp($post['adContent'], $size);
                if($r['status']===false){
                    if ($this->isAjax()) {
                        $this->err(null, array('adContent' => $r['message'])); //输出异步错误信息
                    } else {
                        $this->getView()->assign("error", array('adContent' => $r['message'])); //输出同步错误信息
                    }
                }else{
                    $post['adContent']=$r['data']['remoteFilePath'];
                    $post['startTime']=  strtotime($post['startTime']);
                    $post['endTime']=  strtotime($post['endTime']);
                    $this->saveAction($post, 'add');
                }
                
                
                
            }
            

        }
        
        
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign("advertiserName", $advertiserInfo['advertiserName']);
        $this->getView()->assign("admodules", $admodules);
        
        
        //This page add css、js files .
        $_page=array(
            'static_css_files' => [
                ['path'=>'/plugin/kindeditor/themes/default/default.css','attr'=>''],
                ['path'=>'/plugin/datetimepicker/css/jquery.datetimepicker.css','attr'=>'']
            ],
            'static_js_files' => [
//                ['path'=>'/plugin/kindeditor/kindeditor.js','attr'=>['charset'=>'utf8']],
//                ['path'=>'/plugin/kindeditor/lang/zh_CN.js','attr'=>['charset'=>'utf8']]
            ]
        );
        $this->getView()->assign("_page", $_page);
        
    }
    
    public function editAction(){
        $this->_layout = true;
        $adId = $this->getParam('adId',0);
        $item = $this->model->getAdInfo($adId);
        if(empty($item)){
            $this->redirect("/{$this->_ModuleName}/{$this->_ControllerName}/index");
        }
        if(isset($item['adContent']) && $item['adContent']){
            $item['pic']=$this->fileImg->generateImgUrl(array('imgUrl'=>$item['adContent']), $this->imagesConfig);
        }else{
            $item['pic']='';
        }
        
        $admoduleM = new Default_AdmoduleModel();
        $admodules = $admoduleM->getAdmodules();
            
        $rules = $this->model->getRules();
        if($this->isPost()){
            $post = $this->getPost();
            $post['adId']=$adId;

            $v = new validation(); //数据校验
            $v->parentCls=$this;
            $v->validate($rules, $post);
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
                if ($this->isAjax()) {
                    $this->err('', $v->error_message); //输出异步错误信息
                }
            }else{
                //上传图片至ftp
                $size='0X0';
                
                //没有修改图片的情况
                if (sha1($post['adContent']) == $post['adContentSign']) {
                    $post['startTime'] = strtotime($post['startTime']);
                    $post['endTime'] = strtotime($post['endTime']);
                    $this->saveAction($post, 'edit');
                } else {
                    $r = $this->uploadToFtp($post['adContent'], $size);
                    if ($r['status'] === false) {
                        if ($this->isAjax()) {
                            $this->err(null, array('adContent' => $r['message'])); //输出异步错误信息
                        } else {
                            $this->getView()->assign("error", array('adContent' => $r['message'])); //输出同步错误信息
                        }
                    } else {
                        $post['adContent'] = $r['data']['remoteFilePath'];
                        $post['startTime'] = strtotime($post['startTime']);
                        $post['endTime'] = strtotime($post['endTime']);
                        $this->saveAction($post, 'edit');
                    }
                }
                
                
            }
            
            $item=$post;
            
        }
        
      
        $this->getView()->assign("admodules", $admodules);
        $this->getView()->assign("rules", json_decode($rules)->validation);
        $this->getView()->assign('item', $item);
        
        //This page add css、js files .
        $_page=array(
            'static_css_files' => [
                ['path'=>'/plugin/kindeditor/themes/default/default.css','attr'=>''],
                ['path'=>'/plugin/datetimepicker/css/jquery.datetimepicker.css','attr'=>'']
            ],
            'static_js_files' => [
//                ['path'=>'/plugin/kindeditor/kindeditor.js','attr'=>['charset'=>'utf8']],
//                ['path'=>'/plugin/kindeditor/lang/zh_CN.js','attr'=>['charset'=>'utf8']]
            ]
        );
        $this->getView()->assign("_page", $_page);
    }
    
    public function uploadToFtp($filePath, $size){
        if (!$filePath || !$size) {
            return $this->returnResult(false, '参数错误,上传至Ftp失败！没有可上传的本地文件。');
        }
        $fInfo = pathinfo($filePath);
        
        //加载配置文件
        $imagesConfig = $this->imagesConfig;
        //获取图片服务器组
        $imagesServerGroups = $imagesConfig->common->setting->images->serverGroup;
        
        $fi = $this->fileImg;
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
        
        //获取ftp上的文件路径
        $imgParameter=array('imgType'=>$fInfo['extension'],'imgServer'=>$servGroup);
        $ftpFile = $fi->getImagePath($imgParameter);
        
        //建立ftp文件路径
        $ftp->createFolder($ftpFile['filePath']);
        
        //开始上传文件
        $remoteFilePath = $ftpFile['filePath'] . '/' .$size.'_'.$ftpFile['fileName'];
        $r = $ftp->upload($filePath, $remoteFilePath, $mode = 'auto', 777);
        
        if (!$r) {
            //上传失败，删除本地相关的源图片和缩略图
            return $this->returnResult(false, '上传至ftp失败！');
        }
        
        //上传成功，返回图片路径
        return $this->returnResult(true, array('remoteFilePath'=>$remoteFilePath));
        
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
                $this->getView()->assign("_event", array('_eventMsg'=>'保存失败！','_eventUrl'=>'/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/index'));
//                $this->redirect('/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$action);
            }
            
        }
    }
    

}
