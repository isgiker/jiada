<?php

/**
 * @abstract:模型基类;
 * @author Shiwei Vic
 */
class BasicModel{
    /* 分页的开始位置 */

    public $limitstart = 0;

    /* 设置每页显示多少条记录 */
    public $limit = 20;

    /* 数据库链接实例 */
    protected $_db = null;
    protected $_SETTINGCONFIG;
    protected $_Request;
    private $_errorMsg=null;

    function __construct() {
        $this->_Request = new Yaf_Request_Http();
        $this->_SETTINGCONFIG = Yaf_Registry::get('_SETTINGCONFIG');
        if($this->_SETTINGCONFIG->setting->pagelimit){
            $this->limit = $this->_SETTINGCONFIG->setting->pagelimit;
        }
    }

    /* 自定义每页显示条数 */

    public function setLimit($nums) {
        $this->limit = $nums;
    }

    /* 获取的当前显示条数 */

    public function getLimit() {
        return $this->limit;
    }

    /* 计算分页每次开始位置 */

    public function setLimitStart() {
        $pageNum = $this->_Request->getQuery('p'); //获取当前页码；
        if (!$pageNum) {
            $this->limitstart = 0;
        } else {
            $this->limitstart = ($pageNum * $this->getLimit()) - $this->getLimit(); //起始位置;
        }
    }

    /* 获取当前分页开始位置 */

    public function getLimitStart() {
        $this->setLimitStart();
        return $this->limitstart;
    }
    
    /**
     * 设置Model类中的错误消息
     */
    public function setErrorMsg($errorMsg){
        $this->_errorMsg = $errorMsg;      
    }
    
    /**
     * 获取Model类中的错误消息
     */
    public function getErrorMsg(){
        return $this->_errorMsg;      
    }

}
