<?php

/**
 * @name IndexController
 * @author Vic
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Core_Controller_Admin {

    private $rules = '{"validation":[{
			 		"value":"username",
			  		"label":"用户名",
			  		"rules":[	  					
						{
		  					"name":"clearxss"				
	 					},						
	 					{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	 					{
		  					"name":"regex",
		  					"value":"/^[A-Za-z0-9_\\\\-]{6,20}$/",
			  				"message":"%s%应为6到20位的数字、字符和下划线"
	  					}
		  			]	
		  		},
		  		{
			 		"value":"password",
			  		"label":"密码",
			  		"rules":[
						{
	  						"name":"clearxss"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	 					{
	 						"name":"rangelength",
	 						"value":"[6,20]",
	 						"message":"%s%长度为6到20位"
	 					}
		  			]	
				}]}';

    public function init() {
        $this->getView()->assign('_view', $this->getView());
    }

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/sample/index/index/index/name/root 的时候, 你就会发现不同
     */
    public function indexAction($name = "Stranger") {
        //1. fetch query
        $get = $this->getRequest()->getQuery("get", "default value");

        //2. fetch model
        $model = new Demo_IndexModel();

        //3. assign
        $this->getView()->assign("content", $model->selectSample());
        $this->getView()->assign("name", $name);
        $this->_layout = true;
        $this->_layoutVars['meta_title'] = 'Yaf-J Framework Demo!';
        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
    }

    /**
     *表单验证实例
     */
    public function formAction() {
        $this->_layout = true;
        $this->_layoutVars['meta_title'] = 'Yaf-J Framework Demo!';
        $this->getView()->assign("rules", json_decode($this->rules)->validation);
        if ($this->isPost()) {
            $v = new validation(); //数据校验
            $v->validate($this->rules, $_POST);
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息

                if ($this->isAjax()) {
                    $this->err('', $v->error_message); //输出异步错误信息
                }
            }
        }


    }
    
    public function index2Action($name = "Stranger") {
        //1. fetch query
        $get = $this->getRequest()->getQuery("get", "default value");

        //2. fetch model
        $model = new SampleModel();

        //3. assign
        $this->getView()->assign("content", $model->helloSample());
        $this->getView()->assign("name", $name);
        $this->getView()->assign("data", $model->dbSample());

        $this->_layout->meta_title = 'Yaf-J Framework Hello World!';


        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
    }
    
    public function mongoAction(){
        $model = new SampleModel();
        $model->mongoSample();
    }

    public function uploadAction() {
        $imagesConfig = Yaf_Registry::get("imagesConfig");
        $imagesServerGroups = $imagesConfig->common->setting->images->serverGroup;
        $fi = new File_Image();
        $servGroup = $fi->getImageServerGroup($imagesServerGroups);
        $path = $fi->getImagePath('800X600', 'png', $servGroup);
        

        $config = array('hostname' => '192.168.1.123', 'username' => 'imagesftp', 'password' => 'tj365imagesftp', 'port' => '21');
        $ftp = new File_Ftp();
        $ftp->connect($config);
        
        $ftp->createFolder($path['filePath']);
        $ftp->upload('D:/var/www/php/_tchg/static/img/admin/busniess_img.png', $path['filePath'] . '/' . $path['fileName'], $mode = 'auto', $permissions = 777);
        
        return false;
    }
    
    public function imgurlAction(){
        $imagesConfig = Yaf_Registry::get("imagesConfig");
        $fi = new File_Image();
        $imgParameter = array('imgSize'=>'2','sizeDesc'=>'medium','imgUrl'=>'2013-04-25/upload_72667_1366833257.jpg|/images/uploads/2013-04-25/thumb/general_2013042503541713668332571511.jpg|/images/uploads/2013-04-25/thumb/thumb_2013042503541713668332571511.jpg');
        $result = $fi->generateImgUrl($imgParameter,$imagesConfig);
        print_r($result);
        exit;
    }
    
    public function testAction(){
        $fenweiConfig = new Yaf_Config_Ini(CONFIG_PATH . DS . 'texttable.ini','canyin_shop_fenwei');
        $fenweiTable = $fenweiConfig->toArray();
        print_r($fenweiTable);exit;
    }

    

}
