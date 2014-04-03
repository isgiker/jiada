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
     * @param array $parameter
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

}
