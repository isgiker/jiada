<?php

/**
 * @name Chaoshi_OrderModel
 * @desc 购物车
 * @author Vic
 */
class Chaoshi_OrderModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->db = Factory::getDBO('local_jiada');
        $this->ssodb = Factory::getDBO('local_jiada_sso');
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    /**
     * 根据用户id获取用户所有收货地址
     * @param bigint $userId
     * @return array 
     */
    public function getUserAddress($userId){
        if (!$userId) {
            return false;
        }
        $sql = "select * from user_address where userId='$userId' order by createTime desc";
        $this->ssodb->setQuery($sql);
        $rows = $this->ssodb->loadAssocList();
        return $rows;
    }
    
    public function getAreas($areaidStr){
        if (!$areaidStr) {
            return false;
        }
        $sql = "select areaId,areaName from area where areaId in($areaidStr)";
        $this->db->setQuery($sql);
        $rows = $this->db->loadAssocList();
        return $rows;
    }
    
    /**
     * 获取用户的默认收货地址
     * @param string $userId
     */
    public function getDefaultAddress($userId) {
        if (!$userId) {
            return false;
        }
        $sql = "select * from user_address where userId='$userId' and `status`=1;";
        $this->ssodb->setQuery($sql);
        $rows = $this->ssodb->loadAssoc();
        return $rows;
    }
    
    /**
     * 用户选择默认的收货地址
     * @param array $parameter
     */
    public function setDefaultAddress($parameter){
        if (!$parameter['userId'] || !$parameter['addressId']) {
            return false;
        }
        $sql = "update user_address set `status`=0 where userId='$parameter[userId]' and `status`=1;";
        $sql .= "update user_address set `status`=1 where addressId='$parameter[addressId]' and userId='$parameter[userId]';";
        return $this->ssodb->query($sql);
    }
    
    /**
     * 用户添加新的收货地址
     * @param array $parameter
     */
    public function addAddress($param) {
        //验证参数
        if (!$param['userId'] || !$param['contact'] || !$param['provinceId'] || !$param['cityId'] || !$param['districtId']
                || !$param['communityId'] || !$param['address'] || !$param['mobile']) {
            return false;
        }
        $createTime=time();
        $sql = "update user_address set `status`=0 where userId='$param[userId]' and `status`=1;";
        $sql .= "insert user_address set `userId`='$param[userId]',`contact`='$param[contact]',`provinceId`='$param[provinceId]',
            `cityId`='$param[cityId]',`districtId`='$param[districtId]',`communityId`='$param[communityId]',`address`='$param[address]',
                `mobile`='$param[mobile]',`tel`='$param[tel]',`email`='$param[email]',status=1,createTime=$createTime;";
        return $this->ssodb->query($sql);
    }
    
    /**
     * 修改用户的收货地址
     * @param array $param
     * @param string userId 用户id必要参数
     * @param int addressId 地址id必要参数
     */
    public function editAddress($param) {
        //验证参数
        if (!$param['userId'] || !$param['addressId'] || !$param['contact'] || !$param['provinceId'] || !$param['cityId'] || !$param['districtId']
                || !$param['communityId'] || !$param['address'] || !$param['mobile']) {
            return false;
        }

        $sql = "update user_address set `status`=0 where userId='$param[userId]' and `status`=1;";
        $sql .="update user_address set `contact`='$param[contact]',`provinceId`='$param[provinceId]',
            `cityId`='$param[cityId]',`districtId`='$param[districtId]',`communityId`='$param[communityId]',`address`='$param[address]',
                `mobile`='$param[mobile]',`tel`='$param[tel]',`email`='$param[email]',status=1 
                    where addressId='$param[addressId]' and userId='$param[userId]'";
        return $this->ssodb->query($sql);
    }
    
    /**
     * 获取用户指定的收货地址信息
     * @param array $param
     * @param string userId 用户id
     * @param string addressId 用户收货id
     * @return array
     */
    public function getAddressInfo($param) {
        if (!$param['userId'] || !$param['addressId']) {
            return false;
        }
        $sql = "select * from user_address where addressId='$param[addressId]' and userId='$param[userId]';";
        $this->ssodb->setQuery($sql);
        $rows = $this->ssodb->loadAssoc();
        return $rows;
    }
    
    public function uuid_short(){
       $sql = "select uuid_short();";
       $this->hydb->setQuery($sql);
       $uuid_short = $this->hydb->loadResult();
       return $uuid_short;
    }
    
    public function submitOrder($param){
        $orderNo=$this->uuid_short();
        if (!$orderNo) {
            return false;
        }
        $param['orderNo']=$orderNo;
        
        //下单时间
        $createTime = time();
        $param['createTime']=$createTime;
               
        //保存订单
        
        $rollback=false;
        
        $saveOrder=$this->saveOrder($param);
        if($saveOrder){
            //保存订单收货人信息
            $saveConsignee=$this->saveOrderConsignee($orderNo,$param['userId'],$param['pConsignee']);
            if($saveConsignee){
                //保存订单商品
                $saveProduct=$this->saveOrderProduct($param);
                if($saveProduct){
                    //保存订单统计数据
                    $saveStatistics=$this->saveOrderStatistics($orderNo,$param['userId'],$param['product']['statistics'],$createTime);
                    if($saveStatistics){
                        //保存订单配送信息
                        $saveOrderDelivery=$this->saveOrderDelivery($param);
                        if(!$saveOrderDelivery){
                            $rollback=true;
                        }
                    }else{
                        $rollback=true;
                    }
                }else{
                    $rollback=true;
                }
            }else{
                $rollback=true;
            }
        }else{
            $rollback=true;
        }
        //保证数据的原子性;
        if($rollback===true){
            $this->delOrder($orderNo,$param['userId']);
            return false;
        }else{
            //订单保存成功后的逻辑处理...
            
            //删除用户购物车内数据
            $this->delUserCart($param['userId']);
        }
        return $orderNo;
    }
    
    /**
     * 如果订单商品涉及多个店铺，那么该订单将被拆分成多个订单分发给各个店铺。
     */
    private function saveOrder($param){
        $orderStatus = 0;
        $shippingStatus = 0;
        $payStatus = -1;
        $activityRemark='';
        $payMode = $param['payAndShip']['payMode'];
        
        $shopStatistics=@$param['product']['shop_statistics'];
        $shopStatisticsCn=count($shopStatistics);
        
        //拆单
        if ($shopStatistics && $shopStatisticsCn > 0) {
            $sql='';
            foreach ($shopStatistics as $shopId => $statistics) {
                if($shopStatisticsCn==1){
                    //订单id可以和订单编号相同
                    $orderId=$param[orderNo];
                }else{
                    $orderId=$this->uuid_short();
                }
                $sql.="insert `order` set "
                        . "orderId='$orderId',"
                        . "orderNo='$param[orderNo]',"
                        . "userId='$param[userId]',"
                        . "shopId='$shopId',"
                        . "productAmount='$statistics[currentPriceTotal]',"
                        . "orderAmount='$statistics[orderPriceTotal]',"
                        . "actLower='$statistics[actLower]',"
                        . "actGiveaway='$statistics[actGiveaway]',"
                        . "deliveryFee='$statistics[deliveryFee]',"
                        . "payAmount='$statistics[payPriceTotal]',"
                        . "orderStatus='$orderStatus',"
                        . "shippingStatus='$shippingStatus',"
                        . "payStatus='$payStatus',"
                        . "payMode='$payMode',"
                        . "createTime='$param[createTime]',"
                        . "activityRemark='$activityRemark';"
                        ;
                
            }
            return $this->hydb->query($sql);
        }
        return false;
    }
    
    public function saveOrderConsignee($orderNo,$userId,$pConsignee){
        $orderRemark='';
        $sql = "insert `order_consignee` set "
                . "orderNo='$orderNo',"
                . "userId='$userId',"
                . "contact='$pConsignee[contact]',"
                . "contactMobile='$pConsignee[mobile]',"
                . "contactTel='$pConsignee[tel]',"
                . "contactEmail='$pConsignee[email]',"
                . "contactProvince='$pConsignee[province]',"
                . "contactCity='$pConsignee[city]',"
                . "contactCounty='$pConsignee[district]',"
                . "contactCommunity='$pConsignee[community]',"
                . "contactAddress='$pConsignee[address]',"
                . "contactZipcode='$pConsignee[zipcode]',"
                . "orderRemark='$orderRemark';"
        ;
        return $this->hydb->query($sql);
    }
    
    public function saveOrderProduct($param){
        $shopProductItems=@$param['product']['goodsItems'];
        $shopProductItemsCn=count($shopProductItems);
        
        if ($shopProductItems && $shopProductItemsCn > 0) {
            $sql='';
            foreach ($shopProductItems as $shopId => $pItems) {
                if($pItems && count($pItems) > 0){
                    foreach ($pItems as $k => $item) {
                        if ($item['priceId'] && $item['shopId'] && $item['goodsId'] && $item['goodsName'] && $item['buyNum']) {
                            $sql.="insert `order_product` set "
                                    . "orderNo='$param[orderNo]',"
                                    . "userId='$param[userId]',"
                                    . "shopId='$shopId',"
                                    . "priceId='$item[priceId]',"
                                    . "productId='$item[goodsId]',"
                                    . "productName='$item[goodsName]',"
                                    . "productNum='$item[buyNum]',"
                                    . "originalPrice='$item[originalPrice]',"
                                    . "currentPrice='$item[currentPrice]',"
                                    . "discount='$item[discount]';"
                            ;
                        }else{
                            return false;
                        }
                    }
                    
                }
            }
            return $this->hydb->query($sql);
        }
        
        return false;
    }
    
    public function saveOrderStatistics($orderNo,$userId,$statistics,$createTime){
        if(isset($statistics['activityRemark'])){
            $activityRemark=$statistics['activityRemark'];
        }else{
            $activityRemark='';
        }
        if(!$orderNo || !$userId || !$statistics){
            return false;
        }
        $sql="insert `order_statistics` set "
                        . "orderNo='$orderNo',"
                        . "userId='$userId',"
                        . "productAmount='$statistics[currentPriceTotal]',"
                        . "orderAmount='$statistics[orderPriceTotal]',"
                        . "actLower='$statistics[actLower]',"
                        . "actGiveaway='$statistics[actGiveaway]',"
                        . "deliveryFee='$statistics[deliveryFee]',"
                        . "payAmount='$statistics[payPriceTotal]',"
                        . "createTime='$createTime',"
                        . "activityRemark='$activityRemark';"
                        ;
        return $this->hydb->query($sql);
    }
    
    public function saveOrderDelivery($param){
        if(!$param['payAndShip']['deliveryTimeOption'] || !$param['orderNo'] || !$param['userId']){
            return false;
        }
        $payAndShip=$param['payAndShip'];
        $deliveryTimeOption = $payAndShip['deliveryTimeOption'];
        $payway='';
        $sql="insert `order_delivery` set "
                        . "orderNo='$param[orderNo]',"
                        . "userId='$param[userId]',"
                        . "dmId='$deliveryTimeOption[dmId]',"
                        . "deliveryMode='$deliveryTimeOption[deliveryMode]',"
                        . "deliveryTime='$deliveryTimeOption[deliveryTime]',"
                        . "timeHourStart='$deliveryTimeOption[timeHourStart]',"
                        . "timeMinuteStart='$deliveryTimeOption[timeMinuteStart]',"
                        . "timeHourEnd='$deliveryTimeOption[timeHourEnd]',"
                        . "timeMinuteEnd='$deliveryTimeOption[timeMinuteEnd]',"
                        . "deliveryFee='$deliveryTimeOption[deliveryFee]',"
                        . "fullMoneyDelivery='$deliveryTimeOption[fullMoneyDelivery]',"
                        . "fullMoneyFree='$deliveryTimeOption[fullMoneyFree]',"
                        . "deliveryDistance='$deliveryTimeOption[deliveryDistance]',"
                        . "isNow='$deliveryTimeOption[isNow]',"
                        . "callToConfirm='$payAndShip[callToConfirm]',"
                        . "payway='$payway';"
                        ;
        return $this->hydb->query($sql);
    }
    
    private function delOrder($orderNo,$userId) {
        if (!$orderNo || !$userId) {
            return false;
        }
        $query = "delete from `order` where userId='$userId' and orderNo = '$orderNo';";
        $query.="delete from `order_product` where orderNo = '$orderNo' and userId='$userId';";
        $query.="delete from `order_consignee` where orderNo = '$orderNo' and userId='$userId';";
        $query.="delete from `order_statistics` where orderNo = '$orderNo' and userId='$userId';";
        $query.="delete from `order_delivery` where orderNo = '$orderNo' and userId='$userId';";
        $result = $this->hydb->query($query);
        return $result;
    }
    
    private function delUserCart($userId) {
        if (!$userId) {
            return false;
        }
        $query = "delete from `user_cart` where userId='$userId';";
        $result = $this->ssodb->query($query);
        return $result;
    }

}
