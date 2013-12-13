<?php
/**
 * @desc Mongodb Test库结构文件
 * @author Vic Shiwei
 */
class Mongodb_TestModel {
    public function __construct() {
//        $columns = array(
//                'boolean' => array('name' => 'boolean'),
//                'string' => array('name'  => 'varchar'),
//                'text' => array('name' => 'text'),
//                'integer' => array('name' => 'integer', 'format' => null, 'formatter' => 'intval'),
//                'float' => array('name' => 'float', 'format' => null, 'formatter' => 'floatval'),
//                'datetime' => array('name' => 'datetime', 'format' => null, 'formatter' => 'MongodbDateFormatter'),
//                'timestamp' => array('name' => 'timestamp', 'format' => null, 'formatter' => 'MongodbDateFormatter'),
//                'time' => array('name' => 'time', 'format' => null, 'formatter' => 'MongodbDateFormatter'),
//                'date' => array('name' => 'date', 'format' => null, 'formatter' => 'MongodbDateFormatter'),
//        );
    }
    
    /**
     * key:PRIMARY/UNIQUE/NORMAL
     */
    public function postsCollection() {
        $_tableStructure = array(
                        'sysmsgId' => array('type' => 'varchar', 'default' => null, 'length' => 60, 'key' => 'PRIMARY', 'comment' => '系统消息表ID'),
                        'mTitle' => array('type' => 'varchar', 'default' => null, 'length' => 100, 'key' => null, 'comment' => '消息标题'),
                        'mContent' => array('type' => 'text', 'default' => null, 'length' => null, 'key' => null, 'comment' => '系统消息内容'),
                        'objectType'=>array('type' => 'varchar', 'default' => null, 'length' => 60, 'key' => 'NORMAL', 'comment' => '接收对象类型：用户USERS、商家BUSINESS、代理商AGENTS'),
                        'industryId'=>array('type' => 'int', 'default' => null, 'length' => 11, 'key' => 'NORMAL', 'comment' => '行业：给某个行业的商家或代理商，默认0代表all'),
                        'createTime'=>array('type' => 'int', 'default' => null, 'length' => 11, 'key' => null, 'comment' => '发布时间：时间戳')
                        );
        return $_tableStructure;
    }
    


}
