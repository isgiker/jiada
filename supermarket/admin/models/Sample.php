<?php
/**
 * @name SampleModel
 * @desc sample数据获取类, 可以访问数据库，文件，其它系统等
 * @author root
 */
class SampleModel {
    public function __construct() {
//        $this->db = Factory::getDBO();
//        $this->_mongodb = Factory::getDBO('mongodb');
    }   
    
    public function helloSample() {

        return 'Hello World!';
    }
    
    public function dbSample() {
//        $query = "select * from area ";
//
//        $this->db->setQuery( $query );
//        $result = $this->db->loadobjectlist();

//        return $result;
    }
    
    public function mongoSample(){
        $postsCollection = Documents_Mongodb_Test::getPostsCollection();
        $fields = array(
                        'sysmsgId' => array('type' => 'varchar', 'default' => null, 'length' => 60, 'key' => 'PRIMARY', 'comment' => '系统消息表ID'),
                        'mTitle' => array('type' => 'varchar', 'default' => null, 'length' => 100, 'key' => null, 'comment' => '消息标题'),
                        'mContent' => array('type' => 'text', 'default' => null, 'length' => null, 'key' => null, 'comment' => '系统消息内容'),
                        'objectType'=>array('type' => 'varchar', 'default' => null, 'length' => 60, 'key' => 'NORMAL', 'comment' => '接收对象类型：用户USERS、商家BUSINESS、代理商AGENTS'),
                        'industryId'=>array('type' => 'int', 'default' => null, 'length' => 11, 'key' => 'NORMAL', 'comment' => '行业：给某个行业的商家或代理商，默认0代表all'),
                        'createTime'=>array('type' => 'int', 'default' => null, 'length' => 11, 'key' => null, 'comment' => '发布时间：时间戳')
                        );
//        $result = $this->_mongodb->get('posts');
        print_r($postsCollection);exit;
        
    }

    public function insertSample($arrInfo) {
        return true;
    }
}
