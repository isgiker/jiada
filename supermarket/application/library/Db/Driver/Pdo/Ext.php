<?php
/**
 * @abstract PDO
 * @author Vic Shiwei <isgiker@gmail.com>
 * @copyright The copyright of this framework belongs to the author. Please keep reproduced.
 */
class Db_Driver_Pdo_Ext extends Db_Adapter {

    protected $errorInfo = null;  // corresponds to PDO::errorInfo() or PDOStatement::errorInfo()
    protected $message;    // textual error message， use Exception::getMessage() to access it
    protected $code;      // SQLSTATE error code， use Exception::getCode() to access it

    function __construct($options) {
        $host = array_key_exists('host', $options) ? $options['host'] : 'localhost';
        $port = array_key_exists('port', $options) ? $options['port'] : '3306';
        $user = array_key_exists('user', $options) ? $options['user'] : '';
        $pass = array_key_exists('pass', $options) ? $options['pass'] : '';
        $database = array_key_exists('dbname', $options) ? $options['dbname'] : '';

        if ($options['debug'])
            $this->_debug = $options['debug'];

        /* 链接数据库 */
        try {
            $dbh = new PDO('mysql:host=' . $host . '; port=' . $port . '; dbname=' . $database . '', '' . $user . '', '' . $pass . '');
            $this->_resource = $dbh;

            if ($this->_debug) {
                /* 修改默认的错误显示级别 */
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        } catch (PDOException $e) {
            $this->_errorNum = $e->getCode();
            $this->_errorMsg = 'Could not connect to MySQL ' . $e->getMessage();
            die($this->_errorNum . '连接错误：' . $this->_errorMsg);
        }
        // finalize initialization
        parent::__construct($options);
        return $dbh;
    }

    /**
     * Custom settings for UTF/GBK support
     * 设置数据库字符集编码
     * @access	public
     */
    function setSupport() {
        $this->_resource->query('set names ' . $this->charset . ';');
    }

    /* ----------------------------------#以下是查询结果的方法#--------------------------------------- */

    //用于有记录成果返回的操作，特别是select操作
    function query_select() {
        $sql = $this->_sql;
        //返回一个对象;
        $result = $this->_resource->query($sql);
        return $result;
    }

    /* PDOStatement begin */

    //返回的只有单行关联数组
    function loadAssoc() {
        $rs = $this->query_select();
        return $rs->fetch(PDO::FETCH_ASSOC);
    }

    //返回一行对象列表;
    function loadObject() {
        $rs = $this->query_select();
        return $rs->fetch(PDO::FETCH_OBJ);
    }

    /* 是默认的，可省，返回单行关联和索引;
     * 在这里做特殊处理，返回列：此方法返回第一列的第一个值；     *
     * */

    function loadResult() {
        $rs = $this->query_select();
        $result = $rs->fetch(PDO::FETCH_BOTH);
        return $result[0];
    }

    //是默认的，可省，返回关联和索引列表;
    function loadResultBothArray() {
        $rs = $this->query_select();
        return $rs->fetchAll(PDO::FETCH_BOTH);
    }

    /*
     * 默认返回索引数组列表;
     * 返回列：此方法以数组的形式返回第一列的多行值，此数组没有键值。
     *
     * */

    function loadResultArray() {
        $rs = $this->query_select();
        $result = $rs->fetchAll(PDO::FETCH_NUM);
        $i = 0;
        $array = array();
        foreach ($result as $key => $item) {
            $array[] = $item[0];
        }

        return $array;
    }

    //返回关联数组列表
    function loadAssocList() {
        $rs = $this->query_select();
        return $rs->fetchAll(PDO::FETCH_ASSOC);
    }

    //返回对象列表;
    function loadObjectList() {
        $rs = $this->query_select();
        return $rs->fetchAll(PDO::FETCH_OBJ);
    }

    //返回一行索引数组;
    function loadRow() {
        $rs = $this->query_select();
        return $rs->fetch(PDO::FETCH_NUM);
    }

    /* ----------------------------------#结果集统计#--------------------------------------- */

    //获取结果集记录的条数
    function getNumRows() {
        $rs = $this->query_select();
        return $rs->rowcount();
    }

    //获取记录的列数
    function getNumColumn() {
        $rs = $this->query_select();
        return $rs->columncount();
    }

    /* ----------------------------------#更新写入的操作#--------------------------------------- */

    //是针对没有成果调集返回的操作。如insert,update等操作。返回影响行数
    function query($sql = NULL) {
        if (!$sql) {
            $sql = $this->_sql;
        }
        $result = $this->_resource->exec($sql);
        if ($this->errorcode() != '00000') {
            $this->_errorNum = $this->errorcode();
            $this->_errorMsg = $this->errorInfo();
            return false;
        }
        return true;
    }

    function insert($sql = NULL) {
        return $this->query($sql);
    }

    function update($sql = NULL) {
        return $this->query($sql);
    }

    //返回上次插进去操作最后一条ID
    function insertid() {
        return $this->_resource->lastInsertId();
    }

    /**
     * Inserts a row into a table based on an objects properties
     * 根据对象元素插入一行到表里
     * @access	public
     * @param	string	The name of the table
     * @param	object	An object whose properties match table fields
     * @param	string	The name of the primary key. If provided the object property is updated.
     */
    function insertObject($TableName, &$object, $keyName = NULL) {

        $fmtsql = 'INSERT INTO ' . $TableName . ' ( %s ) VALUES ( %s ) ';
        $fields = array();
        foreach (get_object_vars($object) as $k => $v) {
            if (is_array($v) or is_object($v) or $v === NULL) {
                continue;
            }
            if ($k[0] == '_') { // internal field
                continue;
            }
            $fields[] = $k;
            $values[] = $this->checkStingType($v);
        }

        $sql = sprintf($fmtsql, implode(",", $fields), implode(",", $values));
        $this->query($sql);
        $id = $this->insertid();
        if ($keyName && $id) {
            $object->$keyName = $id;
        }
        return true;
    }

    function replaceInsertObject($TableName, &$object, $keyName = NULL) {

        $fmtsql = 'replace into ' . $TableName . ' ( %s ) VALUES ( %s ) ';
        $fields = array();
        foreach ($object as $k => $v) {
            if (is_array($v) or is_object($v) or $v === NULL) {
                continue;
            }
            if ($k[0] == '_') { // internal field
                continue;
            }
            $fields[] = $k;
            $values[] = $this->checkStingType($v);
        }

        $sql = sprintf($fmtsql, implode(",", $fields), implode(",", $values));
        $this->query($sql);
        $id = $this->insertid();
        if ($keyName && $id) {
            $object->$keyName = $id;
        }
        return true;
    }

    /**
     * Description
     *
     * @access public
     * @param [type] $updateNulls
     */
    function updateObject($TableName, &$object, $keyName, $updateNulls = true) {
        $fmtsql = 'UPDATE ' . $TableName . ' SET %s WHERE %s';
        $tmp = array();
        foreach (get_object_vars($object) as $k => $v) {
            if (is_array($v) or is_object($v) or $k[0] == '_') { // internal or NA field
                continue;
            }
            if ($k == $keyName) { // PK not to be updated
                $where = $keyName . '=' . $this->checkStingType($v);
                continue;
            }
            if ($v === null) {
                if ($updateNulls) {
                    $val = 'NULL';
                } else {
                    continue;
                }
            } else {
                $val = $this->checkStingType($v);
            }
            $tmp[] = $k . '=' . $val;
        }
        $sql = sprintf($fmtsql, implode(",", $tmp), $where);
        $this->query($sql);
        return true;
    }

    /**
     * Get a database escaped string
     * @abstract 本函数将 string 中的特殊字符转义,可使用本函数来预防数据库攻击;
     * PDO->quote()方法的作用是为某个SQL中的字符串添加引号,PDO->quote()方法有两个参数，第一个参数是字符串，第二个参数表示参数的类型。
     * @param	string	The string to be escaped
     * @param	boolean	Optional parameter to provide extra escaping
     * @return	string
     * @access	public
     *  
     */
    function getEscaped($text, $extra = false) {
        $result = $this->_resource->quote($text);
        if ($extra) {
            $result = addcslashes($result, '%_');
        }
        return $result;
    }

    //错误处理;
    function errorcode() {
        $error = $this->_resource->errorcode();
        return $error;
    }

    function errorInfo() {
        $error = $this->_resource->errorInfo();
        if ($error[2]) {
            $errorMsg = 'ERROR: ' . $error[1] . ' (' . $error[0] . ') ' . $error[2];
            return $errorMsg;
        }
    }

    /* ------------------------------------------------------事务处理-------------------------------------------------------- */

    function BeginTrans() {
        $this->_resource->beginTransaction();
    }

    function CommitTrans() {
        $this->_resource->commit();
    }

    function RollBackTrans() {
        $this->_resource->rollBack();
    }

    function checkStingType($text) {
        if (is_string($text)) {
            return '\'' . $text . '\'';
        } else {
            return (int) $text;
        }
    }

    /* PDOStatement end */
}