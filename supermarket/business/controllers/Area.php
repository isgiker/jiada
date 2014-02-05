<?php

/**
 * @name IndexController
 * @desc 地区控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class AreaController extends Core_Controller_Business {

    public function init() {
        parent::init();
        $this->model = new AreaModel();
    }

    /**
     * 获取地区的子分类,支持ajax http和函数形式调用;
     * 根据parentPath参数判断该分类是第几级分类。
     * @param int $parentId 分类的父级节点id
     */
    public function nodeAreaAction($parentId = '0') {
//        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        //根据分类id获取所有子类
        if ($this->isAjax()) {
            $areaId = $this->getParam('areaId');
            if ($areaId) {
                $parentId = $areaId;
            }else{
                $parentId = '';
                return FALSE;
            }
        }
        //获取数据        
        $nodeGcate = $this->model->getNodeArea($parentId);
        if ($this->isAjax()) {
            $data = json_encode($nodeGcate);
            echo $data;
            exit;
        } else {
            return $nodeGcate;
        }
    }

}
