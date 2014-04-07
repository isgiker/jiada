<?php

/**
 * @name OrderController
 * @author Vic Shiwei
 * @desc 订单API
 */
class OrderController extends Core_Controller_Api {

    public $_config;
    protected $model;

    public function init() {
        parent::init();
        $this->_config = Yaf_Registry::get('_CONFIG');
        Yaf_Loader::import('phprpc/server/phprpc_server.php');
        Yaf_Loader::import('phprpc/server/dhparams.php');
        Yaf_Loader::import('phprpc/common/bigint.php');
        Yaf_Loader::import('phprpc/common/compat.php');
        Yaf_Loader::import('phprpc/common/phprpc_date.php');
        Yaf_Loader::import('phprpc/common/xxtea.php');
        $this->model = new Chaoshi_OrderModel();
    }

    public function indexAction() {
        //禁止缓存
//        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->_layout = false;
        $phprpcServer = new PHPRPC_Server();
        $phprpcServer->add(array('getUserAddress', 'setDefaultAddress', 'getDefaultAddress','addAddress','editAddress','getAddressInfo','submitOrder'), $this);

        $phprpcServer->start();
    }

    /**
     * 获取用户所有收货地址
     * @param string $userId 登录情况：根据用户id获取用户购物车内商品数据
     * @param string $userKey 未登录情况：根据用户key获取用户购物车内商品数据
     * @return array|json
     */
    public function getUserAddress($userId) {
        //验证参数
        if (!isset($userId) || !$userId) {
            return $this->errorMessage('请求参数错误！');
        }

        //根据参数获取数据
        $addressItems = $this->model->getUserAddress($userId);
        if ($addressItems) {
            $areaidArr = array();
            foreach ($addressItems as $item) {
                $areaidArr[] = $item['provinceId'];
                $areaidArr[] = $item['cityId'];
                $areaidArr[] = $item['districtId'];
                $areaidArr[] = $item['communityId'];
            }
            if ($areaidArr) {
                $areaidArr = array_unique($areaidArr);
                $areaidStr = implode(',', $areaidArr);
                $areas = $this->model->getAreas($areaidStr);
                $newAreas = array();
                if ($areas) {
                    foreach ($areas as $item) {
                        $newAreas[$item['areaId']] = $item;
                    }
                }
            }
        }

        $data = array('addressItems' => $addressItems, 'areas' => $newAreas,);

        return $this->returnData($data);
    }

    /**
     * 根据用户id获取默认收货地址
     * @param string $userId 用户id
     * @return array|json
     */
    public function getDefaultAddress($userId) {
        //验证参数
        if (!$userId) {
            return $this->errorMessage('请求参数错误！');
        }
        $defaultAddress = $this->model->getDefaultAddress($userId);
        if(!$defaultAddress){
            return $this->errorMessage('还没有添加收货地址！');
        }
        $areaidStr = $defaultAddress['provinceId'] . ',' . $defaultAddress['cityId'] . ',' . $defaultAddress['districtId'] . ',' . $defaultAddress['communityId'];
        $areaidStr = $str{strlen($areaidStr)-1} == ',' ? substr($areaidStr, 0, -1) : $areaidStr;
        $areas = $this->model->getAreas($areaidStr);
        $newAreas = array();
        if ($areas) {
            foreach ($areas as $item) {
                $newAreas[$item['areaId']] = $item;
            }
        }
        $data=array('defaultAddress'=>$defaultAddress,'areas'=>$newAreas);
        return $this->returnData($data);
    }

    /**
     * 设置默认地址
     * @param array $parameter 用户id和地址id
     * @return array|json
     */
    public function setDefaultAddress($parameter) {
        //验证参数
        if (!$parameter['userId'] || !$parameter['addressId']) {
            return $this->errorMessage('请求参数错误！');
        }

        //根据参数获取数据
        $r = $this->model->setDefaultAddress($parameter);
        if ($r) {
            return $this->returnData();
        } else {
            return $this->errorMessage();
        }
    }
    
    /**
     * 添加用户收货地址
     * @param array $parameter
     */
    public function addAddress($parameter) {
        //验证参数
        if (!$parameter['userId'] || !$parameter['contact'] || !$parameter['provinceId'] || !$parameter['cityId'] || !$parameter['districtId']
                || !$parameter['communityId'] || !$parameter['address'] || !$parameter['mobile']) {
            return $this->errorMessage('请求参数错误！');
        }
        
        //保存
        $r = $this->model->addAddress($parameter);
        if ($r) {
            return $this->returnData();
        } else {
            return $this->errorMessage('添加失败！');
        }
        
    }
    
    /**
     * 修改用户的收货地址
     * @param array $parameter
     * @param string userId 用户id必要参数
     * @param int addressId 地址id必要参数
     */
    public function editAddress($parameter){
        //验证参数
        if (!$parameter['userId'] || !$parameter['addressId'] || !$parameter['contact'] || !$parameter['provinceId'] || !$parameter['cityId'] || !$parameter['districtId']
                || !$parameter['communityId'] || !$parameter['address'] || !$parameter['mobile']) {
            return $this->errorMessage('请求参数错误！');
        }
        
        //修改数据
        $r = $this->model->editAddress($parameter);
        if ($r) {
            return $this->returnData();
        } else {
            return $this->errorMessage('数据修改失败！');
        }
    }
    
    public function getAddressInfo($parameter){
        //验证参数
        if (!$parameter['userId'] || !$parameter['addressId']) {
            return $this->errorMessage('请求参数错误！');
        }
        
        //保存
        $data = $this->model->getAddressInfo($parameter);
        return $this->returnData($data);
    }
    
    public function submitOrder($param) {
        //验证参数
        if (!$param['userId'] || !$param['product']['statistics']['goodsCn'] || !$param['pConsignee']['mobile'] || !$param['payAndShip']['deliveryTimeOption']) {
            return $this->errorMessage('提交参数错误！');
        }
        
        //保存
        $saveResult = $this->model->submitOrder($param);
        if(!$saveResult){
            return $this->errorMessage('订单保存失败！');
        }
        
        $payMode = $param['payAndShip']['payMode'];
        $data=array('payMode'=>$payMode,'orderNo'=>$saveResult);
        return $this->returnData($data);
    }

}
