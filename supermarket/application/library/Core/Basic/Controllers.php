<?php

class Core_Basic_Controllers extends Yaf_Controller_Abstract {

    public $_layout = false;
    protected $_layoutVars = array();

    /**
     * 加载Layout模板
     */
    public function render($action, array $tplVars = NULL) {
        if ($this->_layout == true) {
            $this->_layoutVars['_ActionContent'] = parent::render($action, $tplVars);
            return parent::render('../layout/layout', $this->_layoutVars);
        } else {
            return parent::render($action, $tplVars);
        }
    }

    /**
     * 监测是否异步请求
     */
    public function isAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && "XMLHttpRequest" === $_SERVER['HTTP_X_REQUESTED_WITH']) {

            return true;
        } else {

            return false;
        }
    }

    /**
     * 监测是否post
     */
    public function isPost() {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            return true;
        } else {

            return false;
        }
    }

    /**
     * 监测是否get
     */
    public function isGet() {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {

            return true;
        } else {

            return false;
        }
    }
    
    public function err($code = "", $msg = "") {
        $strResult = json_encode(array(
            'result' => 'err',
            'code' => $code,
            'msg' => $msg
        ));
        $strCb = $this->getRequest()->getQuery('cb');
        if (!empty($strCb)) {
            $strResult = $strCb . '(' . $strResult . ');';
        }
        header('Content-type: application/x-javascript;charset=UTF-8');
        echo $strResult;
        exit;
    }

}
