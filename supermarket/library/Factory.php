<?php
/**
 * @description Factory Pattern
 * @author Vic Shiwei
 * @copyright The copyright of this framework belongs to the author. Please keep reproduced.
 * @version 1.0
 */
class Factory {

    /**
     * Instantiated database;
     */
    public static function getDBO($dbNode = 'development') {
        $config = new Yaf_Config_Ini(CONFIG_PATH . DS . "databases.ini", $dbNode);
        $dbOption = array(
            'driver' => $config->db->driver,
            'host' => $config->db->host,
            'port' => $config->db->port,
            'user' => $config->db->user,
            'pass' => $config->db->pass,
            'dbname' => $config->db->dbname,
            'dbprefix' => $config->db->dbprefix,
            'charset' => $config->db->charset,
            'debug' => $config->db->debug,
            'persist' => $config->db->persist,
            'persist_key' => $config->db->persist_key
        );

        $db = Db_Adapter::getInstance($dbOption);
        if ($error = $db->ErrorMsg()) {
            die("$error");
        }

        return $db;
    }
    
    public static function getRedisDBO($dbNode = 'development_redis') {
        $config = new Yaf_Config_Ini(CONFIG_PATH . DS . "databases.ini", $dbNode);
        $redis = new Redis(); 
        if(!$config->db->host){
            $host='127.0.0.1';
        }else{
            $host=$config->db->host;
        }
        
        if(!$config->db->port){
            $port=6379;
        }else{
            $port=$config->db->port;
        }
        
        if(!$config->db->timeout){
            $timeout=45;
        }else{
            $timeout=$config->db->timeout;
        }
        
        
        $redis->connect($host,$port,$timeout);
        $redis->auth($config->db->dbpass);
        return $redis;
    }

    public static function getZendDBO($dbNode = 'development') {
//        $adapter = new Zend\Db\Adapter\Adapter(array('driver' => 'pdo', 'dsn' => 'mysql:dbname=mysql;hostname=localhost', 'username' => 'root', 'password' => 'root', 'driver_options' => array(
//                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''),
//        ));
////        $myDbTable = new MyDbTable('user', $adapter);
//        $sql = 'select * from user';
//        $data = $adapter->fetchRow($sql);
//        print_r($data);
    }
    
}
