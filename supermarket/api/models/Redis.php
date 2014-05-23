<?php
/**
 * @name RedisModel
 * @desc Redis数据类
 * @author vic(shiwei)
 */
class RedisModel {
    
    protected $redis;
    
    public function __construct() {
        $this->redis=Factory::getRedisDBO();
    }
    
    /**
     * 获取超市所有分类
     * @return array
     */
    public function getAllCategary() {
        $result = $this->redis->get("chaoshi_categary_all");
//        $redis->close();
        return $result;
    }
    
    /**
     * 缓存所有分类数据
     * @return boolean|string
     */
    public function setAllCategary($data) {
        if(!$data){
            return false;
        }
        $result = $this->redis->set("chaoshi_categary_all", $data);
        if ($result) {
            $expire=3600*4;
            $this->redis->expire("chaoshi_categary_all", $expire);
        }
        return $result;
        
//        $redis->close();
    }
}