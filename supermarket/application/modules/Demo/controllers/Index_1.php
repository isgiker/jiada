<?php
/**
 * @name IndexController
 * @author Vic
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Yaf_Controller_Abstract {

    private $_layout;

    public function init() {
        /* use layout */
        $this->_layout = Yaf_Registry::get("layout");
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
        $this->_layout->meta_title = 'Yaf-J Framework演示模块';
        $this->_layout->loadLayout();
        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
    }

}
