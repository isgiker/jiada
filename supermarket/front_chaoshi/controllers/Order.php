<?php

/**
 * @name OrderController
 * @desc 订单信息确认（该页面必须要登录才能访问）
 */
class OrderController extends Core_Controller_Www {

    private $imagesConfig;
    private $fileImg;
    private $phprpcClient;
    private $uid;
    private $userKey;

    public function init() {
        parent::init();
        Yaf_Loader::import('phprpc/client/phprpc_client.php');
        Yaf_Loader::import('phprpc/common/bigint.php');
        Yaf_Loader::import('phprpc/common/compat.php');
        Yaf_Loader::import('phprpc/common/phprpc_date.php');
        Yaf_Loader::import('phprpc/common/xxtea.php');

        //加载配置文件
        $this->imagesConfig = Yaf_Registry::get("_ImagesConfig");

        $this->fileImg = new File_Image();
        //接口地址
        $this->phprpcClient = new PHPRPC_Client();

        //用户信息
        $this->uid = $_COOKIE['uid'];
        $this->userKey = $_COOKIE['user-key'];
    }

    public function indexAction() {
        $parameter = array(
            'userId' => $this->uid,
            'userKey' => $this->userKey
        );

        //请求接口,获取用户购物车内的商品数据
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Cart/index');
        $recountResult = $this->phprpcClient->recount($parameter);
        $recountResult = json_decode($recountResult, true);
        if($recountResult['result']=='err'){
            $this->jsLocation(null, '/Cart/index');
        }
        //对商品数据进行签名
        $order_product_sign = strrev(sha1(serialize($recountResult['data'])));
        $order_product_sign = md5($order_product_sign);
        $this->setCookies('order_product_sign', $order_product_sign);

        

        //获取默认收货地址
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Order/index');
        $addressData = $this->phprpcClient->getDefaultAddress($this->uid);
        $addressData = json_decode($addressData, true);
        if($addressData['result']=='err'){
            $needSetConsignee=true;
        }else{
            $needSetConsignee=FALSE;
            $defaultAddress=$addressData['data']['defaultAddress'];
            $areas=$addressData['data']['areas'];
            
            //注意顺序要保持一致
            if(isset($defaultAddress['contact'])){$pConsignee['contact']=$defaultAddress['contact'];}else{$pConsignee['contact']='';}
            if(isset($defaultAddress['mobile'])){$pConsignee['mobile']=$defaultAddress['mobile'];}else{$pConsignee['mobile']='';}
            if(isset($defaultAddress['tel'])){$pConsignee['tel']=$defaultAddress['tel'];}else{$pConsignee['tel']='';}
            if(isset($defaultAddress['email'])){$pConsignee['email']=$defaultAddress['email'];}else{$pConsignee['email']='';}
            if(isset($areas[$defaultAddress['provinceId']]['areaName'])){$pConsignee['province']=$areas[$defaultAddress['provinceId']]['areaName'];}else{$pConsignee['province']='';}
            if(isset($areas[$defaultAddress['cityId']]['areaName'])){$pConsignee['city']= $areas[$defaultAddress['cityId']]['areaName'];}else{$pConsignee['city']='';}
            if(isset($areas[$defaultAddress['districtId']]['areaName'])){$pConsignee['district']= $areas[$defaultAddress['provinceId']]['areaName'];}else{$pConsignee['district']='';}
            if(isset($areas[$defaultAddress['communityId']]['areaName'])){$pConsignee['community']= $areas[$defaultAddress['communityId']]['areaName'];}else{$pConsignee['community']='';}
            if(isset($defaultAddress['address'])){$pConsignee['address']=$defaultAddress['address'];}else{$pConsignee['address']='';}
            if(isset($defaultAddress['zipcode'])){$pConsignee['zipcode']=$defaultAddress['zipcode'];}else{$pConsignee['zipcode']='';}
            
            //对收货人地址进行签名
            $order_consignee_sign = strrev(sha1(serialize($pConsignee)));
            $order_consignee_sign = md5($order_consignee_sign);
            $this->setCookies('order_consignee_sign', $order_consignee_sign);
        }
        
        
        //支付和配送方式时间等
        $hour = date('H', time());
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime("+1 day"));

        if (isset($_COOKIE['payMode']) && isset($_COOKIE['deliveryTimeOption']) && isset($_COOKIE['callToConfirm']) && isset($_COOKIE['order_payship_sing']) && $_COOKIE['order_payship_sing']) {
            $payModeMsg = array(1 => '货到付款', 2 => '在线支付');

            $deliveryTimeMsg = array('NOW' => '即时', 'DELIVERY_FREE_MORNING' => '早上05:30－07:30', 'DELIVERY_FREE_NIGHT' => '晚上19:00－21:00');
            if ($_COOKIE['callToConfirm'] == 1) {
                $callToConfirmMsg = '送货前电话确认';
            } else {
                $callToConfirmMsg = '';
            }

            $this->getView()->assign('payMode', $_COOKIE['payMode']);
            $this->getView()->assign('deliveryTimeOption', $_COOKIE['deliveryTimeOption']);
            $this->getView()->assign('callToConfirm', $_COOKIE['callToConfirm']);
            $this->getView()->assign('order_payship_sing', $_COOKIE['order_payship_sing']);

            $this->getView()->assign('payModeMsg', @$payModeMsg[$_COOKIE['payMode']]);
            $this->getView()->assign('deliveryTimeMsg', @$deliveryTimeMsg[$_COOKIE['deliveryTimeOption']]);
            $this->getView()->assign('callToConfirmMsg', $callToConfirmMsg);
            $needSetPayAndShip = false;
        } else {
            $needSetPayAndShip = true;
        }
        $this->getView()->assign('needSetConsignee', $needSetConsignee);
        $this->getView()->assign('needSetPayAndShip', $needSetPayAndShip);
        $this->getView()->assign('pConsignee', $pConsignee);
        $this->getView()->assign('hour', $hour);
        $this->getView()->assign('today', $today);
        $this->getView()->assign('tomorrow', $tomorrow);
        $this->getView()->assign('data', $recountResult['data']);
    }

    /**
     * 根据用户id获取用户收货地址（必须要登录后才能请求该接口）
     */
    public function userAddressAction() {
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Order/index');
        $result = $this->phprpcClient->getUserAddress($this->uid);
        $result = json_decode($result, true);
        $data = $result['data'];
        $this->getView()->assign('data', $data);
    }

    /**
     * 支付和配送设置
     */
    public function payShipAction() {
//        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        if ($this->isAjax() && $this->isPost()) {
            //把用户选择的支付方式和配送时间记录在cookie
            $post = $this->getPost();
            $time = time() + (3600 * 24 * 30);
            $domain = $this->_config->domain->nowww;
            $r1 = $this->setCookies('payMode', $post['payMode'], $time, '/', $domain);
            $r2 = $this->setCookies('deliveryTimeOption', $post['deliveryTimeOption'], $time, '/', $domain);
            $r3 = $this->setCookies('callToConfirm', $post['callToConfirm'], $time, '/', $domain);
            //签名
            $order_payship_sing = $post['payMode'] . $post['deliveryTimeOption'] . $post['callToConfirm'];
            $order_payship_sing = strrev(sha1($order_payship_sing));
            $order_payship_sing = md5($order_payship_sing);
            $r4 = $this->setCookies('order_payship_sing', $order_payship_sing, $time, '/', $domain);
            if ($r1 && $r2 && $r3 && $r4) {
                echo $this->returnData();
            } else {
                echo $this->errorMessage('保存失败');
            }
            exit;
        }
        if (isset($_COOKIE['payMode']) && $_COOKIE['payMode']) {
            $data['payMode'] = $_COOKIE['payMode'];
        } else {
            $data['payMode'] = 1;
        }
        if (isset($_COOKIE['deliveryTimeOption']) && $_COOKIE['deliveryTimeOption']) {
            $data['deliveryTimeOption'] = $_COOKIE['deliveryTimeOption'];
        } else {
            $data['deliveryTimeOption'] = 'NOW';
        }
        if (isset($_COOKIE['callToConfirm']) && $_COOKIE['callToConfirm']) {
            $data['callToConfirm'] = $_COOKIE['callToConfirm'];
        } else {
            $data['callToConfirm'] = -1;
        }
        $hour = date('H', time());
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime("+1 day"));
        $this->getView()->assign('hour', $hour);
        $this->getView()->assign('today', $today);
        $this->getView()->assign('tomorrow', $tomorrow);
        $this->getView()->assign('data', $data);
    }

    /**
     * 选择收货地址（必须要登录后才能请求该接口）
     */
    public function selectedAddressAction() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $addressId = $this->getPost('addressId');
        $parameter = array(
            'userId' => $this->uid,
            'addressId' => $addressId
        );
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Order/index');
        $result = $this->phprpcClient->setDefaultAddress($parameter);
        exit($result);
    }

    /**
     * 保存收货地址
     */
    public function addAddressAction() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $post = $this->getPost();
        $post['userId'] = $this->uid;
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Order/index');
        $result = $this->phprpcClient->addAddress($post);
        exit($result);
    }

    public function editAddressAction() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $post = $this->getPost();
        $post['userId'] = $this->uid;
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Order/index');
        $result = $this->phprpcClient->editAddress($post);
        exit($result);
    }

    /**
     * 根据用户id获取指定收货地址的信息内容
     */
    public function addressInfoAction() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $addressId = $this->getPost('addressId');
        $parameter = array(
            'userId' => $this->uid,
            'addressId' => $addressId
        );
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Order/index');
        $result = $this->phprpcClient->getAddressInfo($parameter);
        exit($result);
    }
    
    public function submitOrderAction(){
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $parameter = array(
            'userId' => $this->uid,
            'userKey' => $this->userKey
        );

        //请求接口,获取用户购物车内的商品数据============================================================
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Cart/index');
        $recountResult = $this->phprpcClient->recount($parameter);
        $recountResult = json_decode($recountResult, true);

        if($recountResult['result']=='err'){
            exit($this->errorMessage('空的订单，请先去选购商品然后提交订单。'));
        }
        //商品结算统计结果
//        $pStatistics=$recountResult['data']['statistics'];
        $parameter['product']=$recountResult['data'];
        
        //对商品数据进行签名
        $order_product_sign = strrev(sha1(serialize($recountResult['data'])));
        $order_product_sign = md5($order_product_sign);
        
        //验证商品清单内容是否一致
        $cookie_order_product_sign=@$_COOKIE['order_product_sign'];
        if(!$cookie_order_product_sign || $cookie_order_product_sign!=$order_product_sign){
            exit($this->errorMessage('商品内容有发生变动，请重新确认后提交。'));
        }
        
        //获取默认收货地址============================================================
        $this->phprpcClient->useService('http://api.jiada.local/Chaoshi/Order/index');
        $addressData = $this->phprpcClient->getDefaultAddress($this->uid);
        $addressData = json_decode($addressData, true);
        if($addressData['result']=='err'){
            exit($this->errorMessage('您还没有添加和指定收货地址。'));
        }else{
            $defaultAddress=$addressData['data']['defaultAddress'];
            $areas=$addressData['data']['areas'];
                        
            if(isset($defaultAddress['contact'])){$pConsignee['contact']=$defaultAddress['contact'];}else{$pConsignee['contact']='';}
            if(isset($defaultAddress['mobile'])){$pConsignee['mobile']=$defaultAddress['mobile'];}else{$pConsignee['mobile']='';}
            if(isset($defaultAddress['tel'])){$pConsignee['tel']=$defaultAddress['tel'];}else{$pConsignee['tel']='';}
            if(isset($defaultAddress['email'])){$pConsignee['email']=$defaultAddress['email'];}else{$pConsignee['email']='';}
            if(isset($areas[$defaultAddress['provinceId']]['areaName'])){$pConsignee['province']=$areas[$defaultAddress['provinceId']]['areaName'];}else{$pConsignee['province']='';}
            if(isset($areas[$defaultAddress['cityId']]['areaName'])){$pConsignee['city']= $areas[$defaultAddress['cityId']]['areaName'];}else{$pConsignee['city']='';}
            if(isset($areas[$defaultAddress['districtId']]['areaName'])){$pConsignee['district']= $areas[$defaultAddress['provinceId']]['areaName'];}else{$pConsignee['district']='';}
            if(isset($areas[$defaultAddress['communityId']]['areaName'])){$pConsignee['community']= $areas[$defaultAddress['communityId']]['areaName'];}else{$pConsignee['community']='';}
            if(isset($defaultAddress['address'])){$pConsignee['address']=$defaultAddress['address'];}else{$pConsignee['address']='';}
            if(isset($defaultAddress['zipcode'])){$pConsignee['zipcode']=$defaultAddress['zipcode'];}else{$pConsignee['zipcode']='';}
            
            //对收货人地址进行签名
            $order_consignee_sign = strrev(sha1(serialize($pConsignee)));
            $order_consignee_sign = md5($order_consignee_sign);

            //验证商品清单内容是否一致
            $cookie_order_consignee_sign=@$_COOKIE['order_consignee_sign'];
            if(!$cookie_order_consignee_sign || $cookie_order_consignee_sign!=$order_consignee_sign){
                exit($this->errorMessage('您的收货地址发生了变动，请重新确认后提交。'));
            }
            
            $parameter['pConsignee']=$pConsignee;
        }
        
        /*配送及支付方式=================================================================*/
        $hour = date('H', time());
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime("+1 day"));

        if (isset($_COOKIE['payMode']) && isset($_COOKIE['deliveryTimeOption']) && isset($_COOKIE['callToConfirm']) && isset($_COOKIE['order_payship_sing']) && $_COOKIE['order_payship_sing']) {
            $order_payship_sing = $_COOKIE['payMode'] . $_COOKIE['deliveryTimeOption'] . $_COOKIE['callToConfirm'];
            $order_payship_sing = strrev(sha1($order_payship_sing));
            $order_payship_sing = md5($order_payship_sing);
            if($_COOKIE['order_payship_sing']!=$order_payship_sing){
                exit($this->errorMessage('您的支付或配送方式发生了变动，请重新确认后提交。'));
            }
            $parameter['payAndShip']['payMode']=$_COOKIE['payMode'];
            $parameter['payAndShip']['deliveryTimeOption']=$_COOKIE['deliveryTimeOption'];
            $parameter['payAndShip']['callToConfirm']=$_COOKIE['callToConfirm'];
            
            if ($_COOKIE['deliveryTimeOption'] == 'DELIVERY_FREE_MORNING') {
                if ($hour > '05') {
                    $day = $tomorrow;
                } else {
                    $day = $today;
                }
            } elseif ($_COOKIE['deliveryTimeOption'] == 'DELIVERY_FREE_NIGHT') {
                if ($hour > '19') {
                    $day = $tomorrow;
                } else {
                    $day = $today;
                }
            }else{
                $day = $today;
            }
            
            $parameter['payAndShip']['deliveryTime']=$day;
        }else{
            exit($this->errorMessage('请设置您的支付及配送方式。'));
        }
//        print_r($parameter);
//        exit;

        //写入数据库
        $saveResult = $this->phprpcClient->submitOrder($parameter);
        exit($saveResult);
    }

}
