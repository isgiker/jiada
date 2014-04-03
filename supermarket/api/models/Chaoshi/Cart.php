<?php

/**
 * @name Chaoshi_CartModel
 * @desc 购物车
 * @author Vic
 */
class Chaoshi_CartModel extends BasicModel{

    public function __construct() {
        parent::__construct();
        $this->ssodb = Factory::getDBO('local_jiada_sso');
        $this->hydb = Factory::getDBO('local_jiada_chaoshi');
    }
    
    /**
     * 将商品放入购物车
     * @param array $parameter
     * @return bool
     */
    public function putinCart($parameter){
        if(!$parameter){
            return false;
        }
        $createTime=time();
        
        if($parameter['userId']){
            //登录
            $sql = "update user_cart set buyNum=buyNum+$parameter[buyNum],createTime='$createTime'  where userId='$parameter[userId]' and shopId='$parameter[shopId]' and goodsPriceId='$parameter[goodsPriceId]'";
        }else{
            //未登录
            $sql = "update user_cart set buyNum=buyNum+$parameter[buyNum],createTime='$createTime'  where userKey='$parameter[userKey]' and shopId='$parameter[shopId]' and goodsPriceId='$parameter[goodsPriceId]'";
        }
        $result = $this->ssodb->query($sql);
        if ($result === false) {
            $error = $this->ssodb->ErrorMsg();
//            die("$error");
            return false;
        }elseif($result === 0){
            //如果更新受影响行数为0,说明购物车内没有此商品，那么可以放入购物车了;
            $sql = "insert user_cart set userId='$parameter[userId]',userKey='$parameter[userKey]',shopId='$parameter[shopId]',goodsPriceId='$parameter[goodsPriceId]',buyNum='$parameter[buyNum]',createTime='$createTime'";
            $result = $this->ssodb->query($sql);
            if ($result == false) {
                $error = $this->ssodb->ErrorMsg();
//            die("$error");
                return false;
            }
        }
        return true;
    }
    
    
    /**
     * 更新购物车内商品，如果商品数量为0则删除该商品
     * @param array $parameter
     * @return bool
     */
    public function updateCart($parameter){
        if(!$parameter){
            return false;
        }
        $createTime=time();
        
        if ($parameter['buyNum'] > 0) {
            if ($parameter['userId']) {
                //登录情况下的sql条件,这里是修改数量，不是累加；
                $sql = "update user_cart set buyNum=$parameter[buyNum],createTime='$createTime'  where userId='$parameter[userId]' and shopId='$parameter[shopId]' and goodsPriceId='$parameter[goodsPriceId]'";
            } else {
                //未登录情况下的sql条件
                $sql = "update user_cart set buyNum=$parameter[buyNum],createTime='$createTime'  where userKey='$parameter[userKey]' and shopId='$parameter[shopId]' and goodsPriceId='$parameter[goodsPriceId]'";
            }
        } else {
            //删除商品
            if ($parameter['userId']) {
                //登录情况下的sql条件
                $sql = "delete from user_cart where userId='$parameter[userId]' and shopId='$parameter[shopId]' and goodsPriceId='$parameter[goodsPriceId]'";
            } else {
                //未登录情况下的sql条件
                $sql = "delete from user_cart where userKey='$parameter[userKey]' and shopId='$parameter[shopId]' and goodsPriceId='$parameter[goodsPriceId]'";
            }

        }
        $result = $this->ssodb->query($sql);
        if (!$result) {
            $error = $this->ssodb->ErrorMsg();
//            die("$error");
            return false;
        }
        return true;
    }
    
    /**
     * 统计购物车内所有商品总数量
     * @param array $parameter
     * @return int 
     */
    public function countCart($parameter) {
        if (!$parameter) {
            return 0;
        }
        if ($parameter['userId']) {
            //登录情况下的sql条件
            $sql = "select sum(buyNum) from user_cart where userId='$parameter[userId]'";
        } else {
            //未登录情况下的sql条件
            $sql = "select sum(buyNum) from user_cart where userKey='$parameter[userKey]'";
        }
        
        $this->ssodb->setQuery($sql);
        $num = $this->ssodb->loadResult();
        return $num;
    }
    
    /**
     * 根据用户id或user-key获取用户购物车内所有数据
     * @param array $parameter
     * @return array 
     */
    public function getCartItems($parameter){
        if (!$parameter) {
            return false;
        }
        if ($parameter['userId']) {
            //登录情况下的sql条件
            $sql = "select * from user_cart where userId='$parameter[userId]' order by createTime desc";
        } else {
            //未登录情况下的sql条件
            $sql = "select * from user_cart where userKey='$parameter[userKey]' order by createTime desc";
        }
        $this->ssodb->setQuery($sql);
        $rows = $this->ssodb->loadAssocList();
        return $rows;
    }
    
    /**
     * 根据商品价格id获取商品
     * @param array $priceIds 商品价格id
     * @return array 
     */
    public function getGoodsItems($priceIds){
        if (!$priceIds) {
            return false;
        }
        
        $str_ids=implode(',', $priceIds);
        $sql = "select a.*,b.cateId,b.goodsName,b.packPic from goods_price a, goods b where a.priceId in ($str_ids) and a.goodsId=b.goodsId";
        $this->hydb->setQuery($sql);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }
    
    /**
     * 根据商品价格id获取商品
     * @param string $shopId_str 多个店铺id逗号分隔
     * @return array 
     */
    public function getShops($shopId_str){
        if (!$shopId_str) {
            return false;
        }
        
        $sql = "select a.shopId,a.shopName,a.provinceId,a.cityId,a.districtId,a.lng,a.lat from shop_basic a  where a.shopId in ($shopId_str)";
        $this->hydb->setQuery($sql);
        $rows = $this->hydb->loadAssocList();
        return $rows;
    }

}
